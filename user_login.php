<?php
session_start();
include 'db.php';

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password, role, features FROM users WHERE username=?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($id, $db_username, $db_password, $role, $features_json);
            if ($stmt->fetch() && password_verify($password, $db_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $db_username;
                $_SESSION['role'] = $role;
                $_SESSION['features'] = $features_json ? json_decode($features_json, true) : [];
                header("Location: user_dashboard.php");
                exit;
            } else {
                $message = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            $message = "Database query error.";
        }
    } else {
        $message = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .login-card { max-width: 400px; margin: 60px auto; }
    </style>
</head>
<body>
    <div class="login-card card shadow-sm">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">User Login</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>