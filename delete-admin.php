<?php
// Include database connection at the top
include 'db.php'; // Adjust the path if needed

// Logging function
function log_action($action, $details = '')
{
  $entry = date('Y-m-d H:i:s') . " - $action - $details\n";
  file_put_contents(__DIR__ . '/admin_actions.log', $entry, FILE_APPEND);
}

// Output sanitization
function sanitize_output($data)
{
  return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Start session
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in'])
{
    header("Location: login.php");
    exit;
}   
// Check if the user is an admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: login.php");
    exit;
}   

// ...existing code...
//     <label for="password" class="form-label">Password</label>
//     <input type="password" name="password" id="password" class="form-control" required>
//     <div class="mb-3">
//         <label for="user_type" class="form-label">User Type</label>
//         <select name="user_type" id="user_type" class="form-select" required>
//             <option value="admin">Admin</option>
//         <a href="login.php" class="btn btn-link">Login</a>



//         <a href="register_admin.php" class="btn btn-link">Register Admin</a>
//     </div>
// Check if the user is an admin
if (isset($_GET['delete_admin_id'])) {
    $admin_id = intval($_GET['delete_admin_id']);
    // Prevent deleting your own account
    if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $admin_id) {
        header("Location: admin.php?msg=You cannot delete your own admin account.");
        exit;
    }
    // Check if admin exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE id=?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        // Admin does not exist
        header("Location: admin.php?msg=Admin not found.");
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Proceed to delete
    $stmt = $conn->prepare("DELETE FROM admins WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $performed_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';
            log_action('Deleted admin', "Admin ID: $admin_id by $performed_by");
            header("Location: admin.php?msg=Admin deleted successfully");
        } else {
            header("Location: admin.php?msg=Failed to delete admin.");
        }
        $stmt->close();
        exit;
    } else {
        header("Location: admin.php?msg=Failed to prepare delete statement.");
        exit;
    }
}

// Initialize variables
// Handle form submission

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $type = $_POST['user_type'] ?? '';

    // Validate input
    if (empty($user) || empty($pass) || empty($confirm_pass) || empty($type)) {
        $error = "All fields are required.";
    } elseif ($pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } elseif (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
        $error = "Database connection error.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $stmt->close();
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $user, $hashed, $type);
                    if ($stmt->execute()) {
                        log_action('Registered new user', "Username: $user, Type: $type");
                        $success = "User registered successfully!";
                    } else {
                        $error = "Registration failed.";
                    }
                    $stmt->close();
                } else {
                    $error = "Registration failed.";
                }
            }
        } else {
            $error = "Registration failed.";
        }
    }
}

// Close the database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
</html>