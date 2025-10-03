<?php

// User registration script for different user types (admin, editor, viewer)
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "batdata";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = '';
$success = '';
$user_types = ['admin', 'editor', 'viewer'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $type = $_POST['user_type'] ?? '';
    if (!$user || !$pass || !in_array($type, $user_types)) {
        $error = "All fields are required and user type must be valid.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $stmt->close();
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $user, $hashed, $type);
            if ($stmt->execute()) {
                $success = "User registered successfully!";
            } else {
                $error = "Registration failed.";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h2 class="mb-4">Register New User</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="user_type" class="form-label">User Type</label>
                    <select name="user_type" id="user_type" class="form-control" required>
                        <option value="">Select type</option>
                        <?php foreach ($user_types as $type): ?>
                            <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <div class="mt-3 text-center">
                <a href="login.php" class="btn btn-link">Login</a> |
                <a href="forgot_password.php" class="btn btn-link">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html>