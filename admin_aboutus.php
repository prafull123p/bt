<?php
include 'auth.php';
include 'db.php';

$message = '';
$edit_mode = false;
$edit_id = null;
$edit_title = '';
$edit_content = '';
$edit_image = '';

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $img_res = $conn->query("SELECT image FROM about_us WHERE id=$delete_id");
    if ($img_res && $img_row = $img_res->fetch_assoc()) {
        if (!empty($img_row['image']) && file_exists($img_row['image'])) {
            unlink($img_row['image']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM about_us WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "About Us entry deleted.";
}

// Handle Edit (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT title, content, image FROM about_us WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_title, $edit_content, $edit_image);
    $stmt->fetch();
    $stmt->close();
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['image']['name']);
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($img_ext, $allowed)) {
            $img_new_name = uniqid('about_', true) . '.' . $img_ext;
            $img_dest = "uploads/about/" . $img_new_name;
            if (!is_dir('uploads/about')) {
                mkdir('uploads/about', 0777, true);
            }
            if (move_uploaded_file($img_tmp, $img_dest)) {
                $image_path = $img_dest;
            }
        }
    }

    if ($title && $content) {
        if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
            // Update
            $edit_id = intval($_POST['edit_id']);
            if ($image_path) {
                $stmt = $conn->prepare("UPDATE about_us SET title=?, content=?, image=? WHERE id=?");
                $stmt->bind_param("sssi", $title, $content, $image_path, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE about_us SET title=?, content=? WHERE id=?");
                $stmt->bind_param("ssi", $title, $content, $edit_id);
            }
            $stmt->execute();
            $stmt->close();
            $message = "About Us entry updated successfully!";
        } else {
            // Insert
            if ($image_path) {
                $stmt = $conn->prepare("INSERT INTO about_us (title, content, image) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $title, $content, $image_path);
                $stmt->execute();
                $stmt->close();
                $message = "About Us entry added successfully!";
            } else {
                $message = "Image is required for new entry.";
            }
        }
    } else {
        $message = "Title and content are required.";
    }
}

// Fetch all about us entries for listing
$about_list = [];
$result = $conn->query("SELECT * FROM about_us ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $about_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $edit_mode ? 'Edit About Us Entry' : 'Add About Us Entry' ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_title : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea name="content" id="content" class="form-control" rows="4" required><?= htmlspecialchars($edit_mode ? $edit_content : '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image <?= $edit_mode ? '(leave blank to keep current)' : '' ?></label>
                            <input type="file" name="image" id="image" class="form-control" <?= $edit_mode ? '' : 'required' ?> accept="image/*">
                            <?php if ($edit_mode && $edit_image): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($edit_image) ?>" alt="About Image" style="max-width:120px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Add' ?> About Us</button>
                        <?php if ($edit_mode): ?>
                            <a href="admin_aboutus.php" class="btn btn-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">All About Us Entries</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Content</th>
                                    <th>Image</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($about_list): ?>
                                    <?php foreach ($about_list as $entry): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($entry['title']) ?></td>
                                            <td><?= htmlspecialchars(mb_strimwidth($entry['content'], 0, 60, '...')) ?></td>
                                            <td>
                                                <?php if ($entry['image']): ?>
                                                    <img src="<?= htmlspecialchars($entry['image']) ?>" alt="About Image" style="max-width:60px;">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="admin_aboutus.php?edit=<?= $entry['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="admin_aboutus.php?delete=<?= $entry['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No About Us entries found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    // View feature: show all About Us entries in a modal or section if ?view=all
    if (isset($_GET['view']) && $_GET['view'] === 'all') {
        ?>
        <div class="modal show" tabindex="-1" style="display:block; background:rgba(0,0,0,0.5);" id="aboutViewModal">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">All About Us Entries</h5>
                <a href="admin_aboutus.php" class="btn-close"></a>
              </div>
              <div class="modal-body">
                <div class="row">
                  <?php if ($about_list): ?>
                    <?php foreach ($about_list as $entry): ?>
                      <div class="col-md-6 mb-4">
                        <div class="card h-100">
                          <?php if ($entry['image']): ?>
                            <img src="<?= htmlspecialchars($entry['image']) ?>" class="card-img-top" alt="About Image" style="max-height:180px;object-fit:cover;">
                          <?php endif; ?>
                          <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($entry['title']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($entry['content'])) ?></p>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <div class="col-12">
                      <div class="alert alert-secondary text-center mb-0">No About Us entries found.</div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="modal-footer">
                <a href="admin_aboutus.php" class="btn btn-secondary">Close</a>
              </div>
            </div>
          </div>
        </div>
        <style>
          body { overflow: hidden; }
        </style>
        <script>
          // Close modal on ESC
          document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") window.location = "admin_aboutus.php";
          });
        </script>
        <?php
    }
    ?>
</body>
</html>