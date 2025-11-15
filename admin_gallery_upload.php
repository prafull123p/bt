<?php
include 'auth.php';
include 'db.php';
include_once __DIR__ . '/includes/csrf.php';

$message = '';
$edit_mode = false;
$edit_id = null;
$edit_title = '';
$edit_description = '';
$edit_image_path = '';
$edit_color = '';
$edit_order = 9999;
$edit_featured = 0;
$edit_effect_strength = 0;

// Handle Delete via POST (CSRF-protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verify_csrf($_POST['_csrf'] ?? '')) { die('Invalid CSRF token'); }
    $delete_id = intval($_POST['delete_id']);
    $img_res = $conn->query("SELECT image_path FROM gallery WHERE id=$delete_id");
    if ($img_res && $img_row = $img_res->fetch_assoc()) {
        if (!empty($img_row['image_path']) && file_exists($img_row['image_path'])) {
            @unlink($img_row['image_path']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Image deleted.";
}

// Handle Edit (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    // ensure new columns exist
    $cols = ['color_tag'=>'VARCHAR(50)','display_order'=>'INT DEFAULT 9999','featured'=>'TINYINT(1) DEFAULT 0','effect_strength'=>'INT DEFAULT 0'];
    foreach ($cols as $c=>$type) {
        $res = $conn->query("SHOW COLUMNS FROM gallery LIKE '" . $conn->real_escape_string($c) . "'");
        if (!$res || $res->num_rows === 0) {
            $conn->query("ALTER TABLE gallery ADD COLUMN $c $type");
        }
    }
    // ensure responsive size columns exist
    $more = ['image_small'=>'VARCHAR(255)','image_medium'=>'VARCHAR(255)','image_large'=>'VARCHAR(255)','webp_path'=>'VARCHAR(255)','avif_path'=>'VARCHAR(255)'];
    foreach ($more as $c=>$type) {
        $res = $conn->query("SHOW COLUMNS FROM gallery LIKE '" . $conn->real_escape_string($c) . "'");
        if (!$res || $res->num_rows === 0) {
            $conn->query("ALTER TABLE gallery ADD COLUMN $c $type");
        }
    }

    $stmt = $conn->prepare("SELECT title, description, image_path, color_tag, display_order, featured, effect_strength FROM gallery WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_title, $edit_description, $edit_image_path, $edit_color, $edit_order, $edit_featured, $edit_effect_strength);
    $stmt->fetch();
    $stmt->close();
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $color_tag = trim($_POST['color_tag'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 9999);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $effect_strength = intval($_POST['effect_strength'] ?? 0);
    $image_path = '';

    // Only handle image upload if a new image is provided or not in edit mode
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['image']['name']);
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($img_ext, $allowed)) {
            $img_new_name = uniqid('gallery_', true) . '.' . $img_ext;
            $img_dest = "uploads/gallery/" . $img_new_name;
            if (!is_dir('uploads/gallery')) {
                mkdir('uploads/gallery', 0777, true);
            }
            if (move_uploaded_file($img_tmp, $img_dest)) {
                // Generate multiple responsive sizes and WebP/AVIF (best-effort)
                $sizes = [ 'small'=>480, 'medium'=>768, 'large'=>1200, 'xl'=>1600 ];
                $generated = ['small'=>'','medium'=>'','large'=>'','xl'=>'','webp'=>'','avif'=>''];
                try {
                    list($width, $height) = getimagesize($img_dest);
                    $imgExt = $img_ext;
                    // create source image resource
                    $srcImg = null;
                    if (in_array($imgExt, ['jpg','jpeg'])) $srcImg = @imagecreatefromjpeg($img_dest);
                    elseif ($imgExt === 'png') $srcImg = @imagecreatefrompng($img_dest);
                    elseif ($imgExt === 'gif') $srcImg = @imagecreatefromgif($img_dest);

                    foreach ($sizes as $key=>$max) {
                        $newW = $width; $newH = $height;
                        if ($width > $max || $height > $max) {
                            $ratio = $width / $height;
                            if ($width >= $height) { $newW = $max; $newH = intval($max / $ratio); }
                            else { $newH = $max; $newW = intval($max * $ratio); }
                        }
                        $dst = imagecreatetruecolor($newW, $newH);
                        if ($imgExt === 'png' || $imgExt === 'gif') {
                            imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
                            imagealphablending($dst, false);
                            imagesavealpha($dst, true);
                        }
                        imagecopyresampled($dst, $srcImg, 0,0,0,0, $newW, $newH, $width, $height);
                        $outName = preg_replace('/\.[^.]+$/', "_{$key}.{$img_ext}", $img_dest);
                        if (in_array($imgExt, ['jpg','jpeg'])) imagejpeg($dst, $outName, 88);
                        elseif ($imgExt === 'png') imagepng($dst, $outName, 6);
                        elseif ($imgExt === 'gif') imagegif($dst, $outName);
                        imagedestroy($dst);
                        $generated[$key] = $outName;
                        // attempt WebP conversion for each size
                        if (function_exists('imagewebp') && $img_ext !== 'gif') {
                            $srcForWebp = null;
                            if ($img_ext === 'png') $srcForWebp = @imagecreatefrompng($outName);
                            elseif (in_array($img_ext, ['jpg','jpeg'])) $srcForWebp = @imagecreatefromjpeg($outName);
                            if ($srcForWebp) {
                                $webpName = preg_replace('/\.[^.]+$/', '.webp', $outName);
                                if (@imagewebp($srcForWebp, $webpName, 84)) {
                                    $generated['webp'] = $webpName;
                                }
                                imagedestroy($srcForWebp);
                            }
                        }
                    }
                    // try AVIF using imagick if available
                    if (class_exists('Imagick')) {
                        try {
                            $im = new Imagick();
                            $im->readImage($img_dest);
                            $im->setImageFormat('avif');
                            $avifName = preg_replace('/\.[^.]+$/', '.avif', $img_dest);
                            $im->writeImage($avifName);
                            $generated['avif'] = $avifName;
                            $im->clear(); $im->destroy();
                        } catch (Exception $e) { /* ignore */ }
                    }
                } catch (\Throwable $ex) { /* best-effort */ }

                // Decide primary image_path: prefer webp xl if exists, else xl, else original
                if (!empty($generated['webp'])) $image_path = $generated['webp'];
                elseif (!empty($generated['xl'])) $image_path = $generated['xl'];
                else $image_path = $img_dest;
                // Delete old image if updating
                if (isset($_POST['edit_id']) && !empty($_POST['old_image_path']) && file_exists($_POST['old_image_path'])) {
                    @unlink($_POST['old_image_path']);
                }
            } else {
                $message = "Failed to upload image.";
            }
        } else {
            $message = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
        }
    } elseif ($edit_mode) {
        // Keep old image if not uploading a new one
        $image_path = $_POST['old_image_path'] ?? '';
    }

    if ($edit_mode && isset($_POST['edit_id'])) {
        // Update
        $edit_id = intval($_POST['edit_id']);
        if ($image_path) {
            $stmt = $conn->prepare("UPDATE gallery SET title=?, description=?, image_path=?, image_small=?, image_medium=?, image_large=?, webp_path=?, avif_path=?, color_tag=?, display_order=?, featured=?, effect_strength=? WHERE id=?");
            $stmt->bind_param("sssssssssiiii", $title, $description, $image_path, $generated['small'], $generated['medium'], $generated['large'], $generated['webp'], $generated['avif'], $color_tag, $display_order, $featured, $effect_strength, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE gallery SET title=?, description=?, color_tag=?, display_order=?, featured=?, effect_strength=? WHERE id=?");
            $stmt->bind_param("sssiiii", $title, $description, $color_tag, $display_order, $featured, $effect_strength, $edit_id);
        }
        $stmt->execute();
        $stmt->close();
        $message = "Image updated successfully!";
        $edit_mode = false;
    } elseif (!$edit_mode && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $image_path) {
    // Insert (include responsive size columns if available)
    $stmt = $conn->prepare("INSERT INTO gallery (image_path, image_small, image_medium, image_large, webp_path, avif_path, title, description, color_tag, display_order, featured, effect_strength) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssiii", $image_path, $generated['small'], $generated['medium'], $generated['large'], $generated['webp'], $generated['avif'], $title, $description, $color_tag, $display_order, $featured, $effect_strength);
        $stmt->execute();
        $stmt->close();
        $message = "Image uploaded successfully!";
    } elseif (!$edit_mode && empty($image_path)) {
        $message = "Please select an image to upload.";
    }
}

// Fetch all images for listing
$images = [];
$result = $conn->query("SELECT * FROM gallery ORDER BY COALESCE(display_order,9999) ASC, id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Gallery Image</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gallery-img { width: 100%; height: 120px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $edit_mode ? 'Edit Gallery Image' : 'Upload Image to Gallery' ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                            <input type="hidden" name="old_image_path" value="<?= htmlspecialchars($edit_image_path) ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="image" class="form-label">Select Image <?= $edit_mode ? '(leave blank to keep current)' : '' ?></label>
                            <input type="file" name="image" id="image" class="form-control" <?= $edit_mode ? '' : 'required' ?> accept="image/*">
                            <?php if ($edit_mode && $edit_image_path): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($edit_image_path) ?>" alt="Current Image" style="max-width:120px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title (optional)</label>
                            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($edit_mode ? $edit_title : '') ?>">
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4 mb-3">
                                <label for="color_tag" class="form-label">Color Tag</label>
                                <input type="text" name="color_tag" id="color_tag" class="form-control" value="<?= htmlspecialchars($edit_mode ? $edit_color : '') ?>" placeholder="#ff4080 or pink">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" name="display_order" id="display_order" class="form-control" value="<?= htmlspecialchars($edit_mode ? $edit_order : 9999) ?>">
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= ($edit_mode && $edit_featured) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="featured">Featured</label>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="effect_strength" class="form-label">Effect</label>
                                <input type="number" name="effect_strength" id="effect_strength" class="form-control" value="<?= htmlspecialchars($edit_mode ? $edit_effect_strength : 0) ?>" min="0" max="40">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="2"><?= htmlspecialchars($edit_mode ? $edit_description : '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Upload Image' ?></button>
                        <?php if ($edit_mode): ?>
                            <a href="admin_gallery_upload.php" class="btn btn-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                        <a href="gallery.php" class="btn btn-outline-success ms-2">View Gallery</a>
                        <a href="gallery3d.php" class="btn btn-outline-info ms-2">View 3D Gallery</a>
                        <a href="#settings" class="btn btn-outline-light ms-2">Settings</a>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm mt-4" id="settings">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Gallery Settings</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Load existing settings
                    $settings_file = __DIR__ . '/tmp/gallery_settings.json';
                    $settings = ['intensity'=>8];
                    if (file_exists($settings_file)) {
                        $sraw = @file_get_contents($settings_file);
                        $sjson = $sraw ? json_decode($sraw, true) : null;
                        if ($sjson && isset($sjson['intensity'])) $settings['intensity'] = intval($sjson['intensity']);
                    }

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gallery_settings'])) {
                        if (!verify_csrf($_POST['_csrf'] ?? '')) { die('Invalid CSRF token'); }
                        $int = intval($_POST['intensity'] ?? 8);
                        $settings = ['intensity'=>$int];
                        if (!is_dir(__DIR__.'/tmp')) @mkdir(__DIR__.'/tmp',0777,true);
                        file_put_contents($settings_file, json_encode($settings));
                        echo '<div class="alert alert-success">Settings saved.</div>';
                    }
                    ?>
                    <form method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label for="intensity" class="form-label">3D Intensity</label>
                                <input type="number" name="intensity" id="intensity" class="form-control" value="<?= htmlspecialchars($settings['intensity']) ?>" min="0" max="40">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" name="save_gallery_settings" class="btn btn-primary">Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">All Gallery Images</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px">⇅</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($images): ?>
                                    <?php foreach ($images as $img): ?>
                                        <tr draggable="true" data-id="<?= intval($img['id']) ?>">
                                            <td class="align-middle text-center" style="cursor:grab;">☰</td>
                                            <td>
                                                <?php if ($img['image_path']): ?>
                                                    <img src="<?= htmlspecialchars($img['image_path']) ?>" class="gallery-img" alt="Gallery Image">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($img['title']) ?></td>
                                            <td><?= htmlspecialchars($img['description']) ?></td>
                                            <td>
                                                <a href="admin_gallery_upload.php?edit=<?= $img['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <form method="POST" style="display:inline-block;margin:0 0 0 .5rem;">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="delete_id" value="<?= $img['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this image?')">Delete</button>
                                                </form>
                                                <a href="<?= htmlspecialchars($img['image_path']) ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No images found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Toast for notifications -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
                <div id="galleryToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body" id="galleryToastBody">Saved</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
            <script>
                // Drag-and-drop ordering for gallery table
                (function(){
                    const tbody = document.querySelector('table.table tbody');
                    if (!tbody) return;
                    let dragEl = null;
                    tbody.querySelectorAll('tr[draggable="true"]').forEach(row => {
                        row.addEventListener('dragstart', (e) => { dragEl = row; row.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; });
                        row.addEventListener('dragend', (e) => { row.classList.remove('dragging'); dragEl = null; });
                        row.addEventListener('dragover', (e) => { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; const target = e.currentTarget; if (target !== dragEl) {
                            const rect = target.getBoundingClientRect(); const after = (e.clientY - rect.top) > (rect.height/2);
                            if (after) target.parentNode.insertBefore(dragEl, target.nextSibling); else target.parentNode.insertBefore(dragEl, target);
                        }});
                    });

                    // Save order button (auto-save after drop)
                    function saveOrder(){
                        const ids = Array.from(tbody.querySelectorAll('tr[draggable="true"]')).map(r => r.dataset.id);
                        if (!ids.length) return;
                        const token = '<?= csrf_token() ?>';
                            fetch('api/gallery_order.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ _csrf: token, order: ids })
                            }).then(r=>r.json()).then(j=>{ if (j.ok) {
                                // show toast
                                showToast('Order saved');
                            } else {
                                showToast('Save failed', true);
                                console.error(j);
                            }}).catch(err=>{ showToast('Save failed', true); console.error(err); });
                    }

                    // Observe drops by monitoring dragend and then saving order
                    tbody.addEventListener('drop', () => { setTimeout(saveOrder, 60); });
                })();
                // Toast helper
                function showToast(msg, isError) {
                    const toastEl = document.getElementById('galleryToast');
                    const body = document.getElementById('galleryToastBody');
                    body.textContent = msg;
                    if (isError) toastEl.classList.add('text-bg-danger'); else toastEl.classList.remove('text-bg-danger');
                    const bs = bootstrap.Toast.getOrCreateInstance(toastEl);
                    bs.show();
                }
            </script>
        </div>
    </div>
</body>
</html>