<?php
session_start();
include 'db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $db_username, $db_password, $role);
        if ($stmt->fetch() && password_verify($password, $db_password) && ($role === 'admin' || $role === 'superadmin')) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['role'] = $role;
            header("Location: admin.php");
            exit;
        } else {
            $message = "Invalid admin credentials.";
        }
        $stmt->close();
    } else {
        $message = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body{background:#f8f9fa} .login-card{max-width:420px;margin:60px auto}</style>
</head>
<body>
    <div class="login-card card shadow-sm">
        <div class="card-header bg-dark text-white text-center"><h4 class="mb-0">Admin Login</h4></div>
        <div class="card-body">
            <?php if ($message): ?><div class="alert alert-danger"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required autofocus></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <button class="btn btn-dark w-100">Login</button>
            </form>
            <div class="mt-3 text-center"><a href="user_login.php">User Login</a></div>
        </div>
    </div>
</body>
</html>
