<?php

include 'auth.php';
include 'db.php';

$message = '';
$edit_mode = false;
$edit_id = null;
$edit_username = '';
$edit_email = '';
$edit_role = '';
$edit_features = [];

// Define available roles and features
$roles = ['admin' => 'Admin', 'editor' => 'Editor', 'staff' => 'Staff', 'viewer' => 'Viewer'];
$all_features = [
    'manage_staff' => 'Manage Staff',
    'manage_events' => 'Manage Events',
    'manage_blog' => 'Manage Blog',
    'manage_footer' => 'Manage Footer',
    'manage_aboutus' => 'Manage About Us',
    'view_reports' => 'View Reports',
    'manage_fees' => 'Manage Fees',           // <-- Add this line
    'manage_admissions' => 'Manage Admissions' // <-- Add this line
];

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "User deleted.";
}

// Handle Edit (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT username, email, role, features FROM users WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_username, $edit_email, $edit_role, $features_json);
    $stmt->fetch();
    $stmt->close();
    $edit_features = $features_json ? json_decode($features_json, true) : [];
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $features = isset($_POST['features']) ? $_POST['features'] : [];
    $features_json = json_encode($features);

    if ($username && $email && $role) {
        if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
            // Update
            $edit_id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, features=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $email, $role, $features_json, $edit_id);
            $stmt->execute();
            $stmt->close();
            $message = "User updated successfully!";
        } else {
            // Insert (default password: user123, you should implement password management)
            $default_password = password_hash('user123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, features) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $default_password, $role, $features_json);
            $stmt->execute();
            $stmt->close();
            $message = "User added successfully! Default password is 'user123'.";
        }
    } else {
        $message = "All fields are required.";
    }
}

// Handle Change Password
if (isset($_POST['change_password_id']) && is_numeric($_POST['change_password_id'])) {
    $change_id = intval($_POST['change_password_id']);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($new_password && $confirm_password && $new_password === $confirm_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $change_id);
        $stmt->execute();
        $stmt->close();
        $message = "Password changed successfully!";
    } else {
        $message = "Passwords do not match or are empty.";
    }
}

// Fetch all users for listing
$users = [];
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['features'] = $row['features'] ? json_decode($row['features'], true) : [];
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $edit_mode ? 'Edit User' : 'Add User' ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_username : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_email : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($edit_mode && $edit_role == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign Features</label>
                            <div class="row">
                                <?php foreach ($all_features as $key => $label): ?>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="<?= $key ?>"
                                                id="feature_<?= $key ?>"
                                                <?= ($edit_mode && in_array($key, $edit_features)) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="feature_<?= $key ?>">
                                                <?= $label ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Add' ?> User</button>
                        <?php if ($edit_mode): ?>
                            <a href="admin_users.php" class="btn btn-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">All Users</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Features</th>
                                    <th style="width: 220px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                                            <td>
                                                <?php if ($user['features']): ?>
                                                    <ul class="mb-0 ps-3">
                                                        <?php foreach ($user['features'] as $f): ?>
                                                            <li><?= htmlspecialchars($all_features[$f] ?? $f) ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="admin_users.php?edit=<?= $user['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                                                <a href="admin_users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Delete this user?')">Delete</a>
                                                <!-- Change Password Button (triggers modal) -->
                                                <button type="button" class="btn btn-sm btn-secondary mb-1" data-bs-toggle="modal" data-bs-target="#changePassModal<?= $user['id'] ?>">
                                                    Change Password
                                                </button>
                                                <!-- Change Password Modal -->
                                                <div class="modal fade" id="changePassModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="changePassModalLabel<?= $user['id'] ?>" aria-hidden="true">
                                                  <div class="modal-dialog">
                                                    <div class="modal-content">
                                                      <form method="POST">
                                                        <div class="modal-header">
                                                          <h5 class="modal-title" id="changePassModalLabel<?= $user['id'] ?>">Change Password for <?= htmlspecialchars($user['username']) ?></h5>
                                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                          <input type="hidden" name="change_password_id" value="<?= $user['id'] ?>">
                                                          <div class="mb-3">
                                                            <label for="new_password<?= $user['id'] ?>" class="form-label">New Password</label>
                                                            <input type="password" name="new_password" id="new_password<?= $user['id'] ?>" class="form-control" required>
                                                          </div>
                                                          <div class="mb-3">
                                                            <label for="confirm_password<?= $user['id'] ?>" class="form-label">Confirm Password</label>
                                                            <input type="password" name="confirm_password" id="confirm_password<?= $user['id'] ?>" class="form-control" required>
                                                          </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                          <button type="submit" class="btn btn-primary">Change Password</button>
                                                        </div>
                                                      </form>
                                                    </div>
                                                  </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No users found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>