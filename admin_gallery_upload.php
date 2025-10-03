<?php
include 'auth.php';
include 'db.php';

$message = '';
$edit_mode = false;
$edit_id = null;
$edit_title = '';
$edit_description = '';
$edit_image_path = '';

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $img_res = $conn->query("SELECT image_path FROM gallery WHERE id=$delete_id");
    if ($img_res && $img_row = $img_res->fetch_assoc()) {
        if (!empty($img_row['image_path']) && file_exists($img_row['image_path'])) {
            unlink($img_row['image_path']);
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
    $stmt = $conn->prepare("SELECT title, description, image_path FROM gallery WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_title, $edit_description, $edit_image_path);
    $stmt->fetch();
    $stmt->close();
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
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
                $image_path = $img_dest;
                // Delete old image if updating
                if (isset($_POST['edit_id']) && !empty($_POST['old_image_path']) && file_exists($_POST['old_image_path'])) {
                    unlink($_POST['old_image_path']);
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
            $stmt = $conn->prepare("UPDATE gallery SET title=?, description=?, image_path=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $description, $image_path, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE gallery SET title=?, description=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $description, $edit_id);
        }
        $stmt->execute();
        $stmt->close();
        $message = "Image updated successfully!";
        $edit_mode = false;
    } elseif (!$edit_mode && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $image_path) {
        // Insert
        $stmt = $conn->prepare("INSERT INTO gallery (image_path, title, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $image_path, $title, $description);
        $stmt->execute();
        $stmt->close();
        $message = "Image uploaded successfully!";
    } elseif (!$edit_mode && empty($image_path)) {
        $message = "Please select an image to upload.";
    }
}

// Fetch all images for listing
$images = [];
$result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
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
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="2"><?= htmlspecialchars($edit_mode ? $edit_description : '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Upload Image' ?></button>
                        <?php if ($edit_mode): ?>
                            <a href="admin_gallery_upload.php" class="btn btn-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                        <a href="gallery.php" class="btn btn-outline-success ms-2">View Gallery</a>
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
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($images): ?>
                                    <?php foreach ($images as $img): ?>
                                        <tr>
                                            <td>
                                                <?php if ($img['image_path']): ?>
                                                    <img src="<?= htmlspecialchars($img['image_path']) ?>" class="gallery-img" alt="Gallery Image">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($img['title']) ?></td>
                                            <td><?= htmlspecialchars($img['description']) ?></td>
                                            <td>
                                                <a href="admin_gallery_upload.php?edit=<?= $img['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="admin_gallery_upload.php?delete=<?= $img['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this image?')">Delete</a>
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
        </div>
    </div>
</body>
</html>