<?php

session_start();

// If not logged in, redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2 class="mb-4">Admin Dashboard</h2>
    <p>Welcome, Admin!</p>
    <!-- <a href="addimgfor_slide.php" class="btn btn-primary mb-3">Manage Slides</a>
    <a href="admin_notification.php" class="btn btn-secondary mb-3">Manage Notifications</a>
    <a href="manage_event.php" class="btn btn-success mb-3">Manage Event</a>
    <a href="admin_blog.php" class="btn btn-info mb-3">Manage Blog</a>
    <a href="logout.php" class="btn btn-danger mb-3">Logout</a> -->

   
</body>
</html>