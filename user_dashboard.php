<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
$features = $_SESSION['features'] ?? [];

// Add new features here
$all_features = [
    'manage_staff' => 'Manage Staff',
    'manage_events' => 'Manage Events',
    'manage_blog' => 'Manage Blog',
    'manage_footer' => 'Manage Footer',
    'manage_aboutus' => 'Manage About Us',
    'view_reports' => 'View Reports',
    'manage_fees' => 'Manage Fees',
    'manage_admissions' => 'Manage Admissions'
];

// Add links for new features here
$feature_links = [
    'manage_staff' => 'admin_staff.php',
    'manage_events' => 'admin_events.php',
    'manage_blog' => 'admin_blog.php',
    'manage_footer' => 'admin_footer_settings.php',
    'manage_aboutus' => 'admin_aboutus.php',
    'view_reports' => 'admin_reports.php',
    'manage_fees' => 'admin_fees.php',
    'manage_admissions' => 'admin_admissions.php'
];

// Handle feature assignment (only for admin or users with permission)
$message = '';
if ($role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_features_user'])) {
    $assign_user = trim($_POST['assign_features_user']);
    $assign_features = isset($_POST['assign_features']) ? $_POST['assign_features'] : [];
    // Save to database (assumes users table has a 'features' JSON column)
    include 'db.php';
    $features_json = json_encode($assign_features);
    $stmt = $conn->prepare("UPDATE users SET features=? WHERE username=?");
    $stmt->bind_param("ss", $features_json, $assign_user);
    if ($stmt->execute()) {
        $message = "Features updated for user: " . htmlspecialchars($assign_user);
    } else {
        $message = "Failed to update features.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .dashboard-card { max-width: 700px; margin: 40px auto; }
    </style>
</head>
<body>
    <div class="dashboard-card card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Welcome, <?= htmlspecialchars($username) ?></h4>
        </div>
        <div class="card-body">
            <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($role)) ?></p>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>
            <h5 class="mt-4">Your Assigned Features</h5>
            <?php if ($features && is_array($features)): ?>
                <ul class="list-group mb-4">
                    <?php foreach ($features as $f): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($all_features[$f] ?? $f) ?>
                            <?php if (isset($feature_links[$f])): ?>
                                <a href="<?= htmlspecialchars($feature_links[$f]) ?>" class="btn btn-sm btn-outline-primary ms-2">Go</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-warning">No features assigned to your account.</div>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <hr>
                <h5>Assign Features to User</h5>
                <form method="post" class="mb-3">
                    <div class="mb-2">
                        <label for="assign_features_user" class="form-label">Select User</label>
                        <select name="assign_features_user" id="assign_features_user" class="form-select" required>
                            <option value="">-- Select User --</option>
                            <?php
                            // List all users except admin
                            include 'db.php';
                            $res = $conn->query("SELECT username FROM users WHERE role != 'admin'");
                            while ($row = $res->fetch_assoc()):
                            ?>
                                <option value="<?= htmlspecialchars($row['username']) ?>"><?= htmlspecialchars($row['username']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Assign Features</label>
                        <div class="row">
                            <?php foreach ($all_features as $key => $label): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="assign_features[]" value="<?= $key ?>" id="assign_feature_<?= $key ?>">
                                        <label class="form-check-label" for="assign_feature_<?= $key ?>">
                                            <?= $label ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Features</button>
                </form>
            <?php endif; ?>

            <a href="user_logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>
</html>