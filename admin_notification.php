<?php

// Use shared DB connection
include 'db.php';

// Start session and restrict access
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle notification creation / update / delete
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create
    if (isset($_POST['create_notification'])) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title && $content) {
            $stmt = $conn->prepare("INSERT INTO notifications (title, content, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $title, $content);
            if ($stmt->execute()) {
                $message = "Notification added successfully!";
            } else {
                $message = "Failed to add notification.";
            }
            $stmt->close();
        } else {
            $message = "Title and content are required.";
        }
    }

    // Update
    if (isset($_POST['edit_notification']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($id && $title && $content) {
            $stmt = $conn->prepare("UPDATE notifications SET title = ?, content = ? WHERE id = ?");
            $stmt->bind_param("ssi", $title, $content, $id);
            if ($stmt->execute()) {
                $message = "Notification updated successfully!";
            } else {
                $message = "Failed to update notification.";
            }
            $stmt->close();
        } else {
            $message = "ID, title and content are required for update.";
        }
    }
}

// include CSRF helper and handle delete via POST
include_once __DIR__ . '/includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verify_csrf($_POST['_csrf'] ?? '')) {
        die('Invalid CSRF token');
    }
    $del_id = (int)$_POST['delete_id'];
    if ($del_id) {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->bind_param('i', $del_id);
        if ($stmt->execute()) {
            $message = "Notification deleted.";
        } else {
            $message = "Failed to delete notification.";
        }
        $stmt->close();
        // redirect to avoid resubmission on refresh
        header('Location: admin_notification.php');
        exit;
    }
}

// Fetch notifications
$notifications = [];
$result = $conn->query("SELECT id, title, content, created_at FROM notifications ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2 class="mb-4">Add New Notification</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" class="mb-5">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="create_notification" value="1">
        <div class="mb-3">
            <label for="title" class="form-label">Notification Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Notification Content</label>
            <textarea name="content" id="content" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Notification</button>
    </form>

    <?php if (isset($_GET['edit_id'])):
        $edit_id = (int)$_GET['edit_id'];
        $edit_item = null;
        foreach ($notifications as $n) if ((int)$n['id'] === $edit_id) { $edit_item = $n; break; }
    ?>
    <h2 class="mb-3">Edit Notification</h2>
    <?php if ($edit_item): ?>
    <form method="POST" class="mb-5">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="edit_notification" value="1">
        <input type="hidden" name="id" value="<?= (int)$edit_item['id'] ?>">
        <div class="mb-3">
            <label for="title_edit" class="form-label">Notification Title</label>
            <input type="text" name="title" id="title_edit" class="form-control" required value="<?= htmlspecialchars($edit_item['title']) ?>">
        </div>
        <div class="mb-3">
            <label for="content_edit" class="form-label">Notification Content</label>
            <textarea name="content" id="content_edit" class="form-control" rows="4" required><?= htmlspecialchars($edit_item['content']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="admin_notification.php" class="btn btn-secondary">Cancel</a>
    </form>
    <?php else: ?>
        <div class="alert alert-warning">Notification not found.</div>
    <?php endif; ?>
    <?php endif; ?>

    <h3 class="mb-3">All Notifications</h3>
    <?php if ($notifications): ?>
        <div class="list-group">
            <?php foreach ($notifications as $note): ?>
                <div class="list-group-item mb-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($note['title']) ?></h5>
                            <small class="text-muted"><?= htmlspecialchars($note['created_at']) ?></small>
                        </div>
                        <div class="btn-group">
                            <a href="admin_notification.php?edit_id=<?= (int)$note['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" style="display:inline-block;margin:0 0 0 .5rem;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_id" value="<?= (int)$note['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this notification?');">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary">No notifications found.</div>
    <?php endif; ?>
</body>
</html>