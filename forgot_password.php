<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "batdata";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST['username'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    if (!$user || !$new_pass) {
        $message = "All fields are required.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->close();
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $hashed, $user);
            if ($stmt->execute()) {
                $message = "Password reset successfully!";
            } else {
                $message = "Failed to reset password.";
            }
            $stmt->close();
        } else {
            $message = "Username not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h2 class="mb-4">Forgot Password</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
            <div class="mt-3 text-center">
                <a href="login.php" class="btn btn-link">Login</a> |
                <a href="register.php" class="btn btn-link">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Close the database connection
$conn->close();

/*
End of forgot_password.php
End of file
This file is part of the forgot password functionality.
It allows users to reset their password by providing their username and a new password.
The script checks if the username exists, and if so, updates the password in the database.
If the username does not exist, it informs the user.
The script also handles form submission and displays appropriate messages based on the outcome.
Make sure to include this file in your project where needed.
*/
?>
