<?php
include 'auth.php'; // Ensure user is authenticated
include 'db.php';

$message = '';
$edit_mode = false;
$edit_id = null;
$edit_caption = '';
$edit_image = '';

// CREATE or UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caption = trim($_POST['caption'] ?? '');
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['image']['name']);
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($img_ext, $allowed)) {
            $img_new_name = uniqid('slide_', true) . '.' . $img_ext;
            $img_dest = "uploads/" . $img_new_name;
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            if (move_uploaded_file($img_tmp, $img_dest)) {
                $image_path = $img_dest;
            }
        }
    }

    if (isset($_POST['save_slide'])) {
        if ($caption && $image_path) {
            $stmt = $conn->prepare("INSERT INTO carousel (image_path, caption) VALUES (?, ?)");
            $stmt->bind_param("ss", $image_path, $caption);
            $stmt->execute();
            $stmt->close();
            $message = "Slide added successfully!";
        } else {
            $message = "Caption and image are required.";
        }
    } elseif (isset($_POST['update_slide']) && isset($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        if ($caption) {
            if ($image_path) {
                $stmt = $conn->prepare("UPDATE carousel SET image_path=?, caption=? WHERE id=?");
                $stmt->bind_param("ssi", $image_path, $caption, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE carousel SET caption=? WHERE id=?");
                $stmt->bind_param("si", $caption, $edit_id);
            }
            $stmt->execute();
            $stmt->close();
            $message = "Slide updated successfully!";
        } else {
            $message = "Caption is required.";
        }
    }
}

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Optionally delete the image file from server
    $img_res = $conn->query("SELECT image_path FROM carousel WHERE id=$delete_id");
    if ($img_res && $img_row = $img_res->fetch_assoc()) {
        if (!empty($img_row['image_path']) && file_exists($img_row['image_path'])) {
            unlink($img_row['image_path']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM carousel WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Slide deleted.";
}

// EDIT (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT caption, image_path FROM carousel WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_caption, $edit_image);
    $stmt->fetch();
    $stmt->close();
}

// READ (fetch all)
$slides = [];
$result = $conn->query("SELECT id, image_path, caption FROM carousel ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $slides[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Slides</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">  
</head>
<body class="container py-5">
  <h2 class="mb-4"><?= $edit_mode ? 'Edit Slide' : 'Add Image for Slide' ?></h2>
  <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data" class="mb-5">
    <?php if ($edit_mode): ?>
      <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
    <?php endif; ?>
    <div class="mb-3">
      <label for="image" class="form-label">Image <?= $edit_mode ? '(leave blank to keep current)' : '' ?></label>
      <input type="file" name="image" id="image" class="form-control" <?= $edit_mode ? '' : 'required' ?>>
      <?php if ($edit_mode && $edit_image): ?>
        <div class="mt-2">
          <img src="<?= htmlspecialchars($edit_image) ?>" alt="Slide Image" style="max-width:120px;">
        </div>
      <?php endif; ?>
    </div>
    <div class="mb-3">
      <label for="caption" class="form-label">Caption</label>
      <input type="text" name="caption" id="caption" class="form-control" placeholder="Caption" required value="<?= htmlspecialchars($edit_caption) ?>">
    </div>
    <?php if ($edit_mode): ?>
      <button type="submit" name="update_slide" class="btn btn-success">Update Slide</button>
      <a href="addimgfor_slide.php" class="btn btn-secondary">Cancel</a>
    <?php else: ?>
      <button type="submit" name="save_slide" class="btn btn-primary">Add Slide</button>
    <?php endif; ?>
  </form>

  <h4 class="mb-3">All Slides</h4>
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-primary">
        <tr>
          <th>Image</th>
          <th>Caption</th>
          <th style="width: 140px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($slides): ?>
          <?php foreach ($slides as $slide): ?>
            <tr>
              <td>
                <?php if (!empty($slide['image_path'])): ?>
                  <img src="<?= htmlspecialchars($slide['image_path']) ?>" alt="Slide Image" style="max-width:80px;">
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($slide['caption']) ?></td>
              <td>
                <a href="addimgfor_slide.php?edit=<?= $slide['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="addimgfor_slide.php?delete=<?= $slide['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this slide?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="text-center">No slides found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
