<!-- /**
* login.php
*
* This script handles the admin login functionality for the application.
*
* Features:
* - Establishes a connection to the MySQL database using MySQLi.
* - Sets the character set to utf8mb4 to avoid encoding issues.
* - Processes POST requests for admin login.
* - Uses prepared statements to prevent SQL injection when querying the 'admins' table.
* - Verifies the submitted password against the hashed password stored in the database using password_verify().
* - On successful authentication, sets session variables and redirects to the admin dashboard (admin.php).
* - Displays error messages for invalid login attempts.
* - Utilizes Bootstrap 5 for styling the login form.
* - Ensures proper cleanup by closing the database connection.
*
* Security Considerations:
* - Passwords are never stored or compared in plain text.
* - SQL injection is mitigated through the use of prepared statements.
* - Session management is used to track authenticated admin users.
*
* Usage:
* - Place this file in your web server's document root.
* - Ensure the 'admins' table exists in your 'batdata' database with columns: id (int), username (varchar), password (varchar, hashed).
* - Access the page via your browser and log in with valid admin credentials.
*/ -->
<?php
// login.php
// This script handles the admin login functionality.

session_start();
include 'db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $db_username, $db_password, $role);
        if ($stmt->fetch() && password_verify($password, $db_password)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['role'] = $role;
            header("Location: admin.php");
            exit;
        } else {
            $message = "Invalid username or password.";
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
    <title>Admin/User Login</title>
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
<?php
// This is the end of the login.php script.
// You can add any additional cleanup or redirection logic here if needed.
// For example, redirect to a different page after login
// header("Location: somepage.php");
// exit; // Uncomment if you want to ensure no further output is sent after redirection

// End of the login.php file
// You can add any additional scripts or logic here if needed.
// For example, you might want to include a footer or additional scripts
// include 'footer.php'; // Uncomment if you have a footer file to include
