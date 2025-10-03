<?php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "batdata";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error)
  die("Connection failed: {$conn->connect_error}");

// Start session and restrict access
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: login.php');
  exit;
}

// Handle notification creation
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_notification'])) {
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

    <h3 class="mb-3">All Notifications</h3>
    <?php if ($notifications): ?>
        <div class="list-group">
            <?php foreach ($notifications as $note): ?>
                <div class="list-group-item mb-3">
                    <h5><?= htmlspecialchars($note['title']) ?></h5>
                    <small class="text-muted"><?= htmlspecialchars($note['created_at']) ?></small>
                    <p><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary">No notifications found.</div>
    <?php endif; ?>
</body>
</html>