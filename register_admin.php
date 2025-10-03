<?php
// Admin registration page
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "batdata";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$user || !$pass || !$confirm) {
        $error = "All fields are required.";
    } elseif ($pass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $stmt->close();
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $hashed);
            if ($stmt->execute()) {
                $success = "Admin registered successfully!";
            } else {
                $error = "Registration failed.";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?><
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h2 class="mb-4">Register New Admin</h2>
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
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register Admin</button>
            </form>
            <div class="mt-3 text-center">
                <a href="login.php" class="btn btn-link">Login</a>
            </div>
        </div>
    </div>
    <footer class="text-center mt-5">
        <hr>
        <p>&copy; <?= date("Y") ?> BatData. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
/**
 * register_admin.php
 *
 * This script allows an admin to register a new admin user.
 * 
 * Features:
 * - Checks for existing usernames to prevent duplicates.
 * - Hashes the password securely before storing.
 * - Stores the new admin credentials in the database.
 * - Displays a success message upon successful registration.
 * - Shows an error message if registration fails.
 * 
 * Usage:
 * - Include this file in your project where admin registration is required.
 * - You may add additional functionality, such as redirecting to the admin dashboard after registration.
 *   Example:
 *     // header("Location: admin_dashboard.php");
 *     // exit;
 * - Optionally, include additional scripts or a footer at the end of this file.
 *   Example:
 *     // include 'footer.php';
 *
 * @package AdminRegistration
 */
// End of register_admin.php
// End of file
// This is the end of the register_admin.php script.
// This script allows an admin to register a new admin user.
// It checks for existing usernames, hashes the password, and stores the new admin in the database.
// If registration is successful, it displays a success message; otherwise, it shows an error.      
// Make sure to include this file in your project where needed.
// You can add additional functionality or redirect to another page after registration if needed.
// For example, you might want to redirect to the admin dashboard after successful registration
// header("Location: admin_dashboard.php");
// exit; // Uncomment if you want to ensure no further output is sent after redirection

// End of register_admin.php
// You can add any additional scripts or logic here if needed.
// For example, you might want to include a footer or additional scripts
// include 'footer.php'; // Uncomment if you have a footer file to include
?>

<?php

