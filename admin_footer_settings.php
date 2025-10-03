<?php
include 'auth.php';
include 'db.php';

$message = '';

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM footer_settings WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Footer entry deleted.";
}

// Handle Edit (fetch data)
$edit_mode = false;
$edit_id = null;
$edit_footer = [
    'address' => '',
    'contact' => '',
    'email' => '',
    'map_embed' => ''
];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT address, contact, email, map_embed FROM footer_settings WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_footer['address'], $edit_footer['contact'], $edit_footer['email'], $edit_footer['map_embed']);
    $stmt->fetch();
    $stmt->close();
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $map_embed = trim($_POST['map_embed'] ?? '');

    if ($address && $contact && $email) {
        if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
            // Update
            $edit_id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE footer_settings SET address=?, contact=?, email=?, map_embed=? WHERE id=?");
            $stmt->bind_param("ssssi", $address, $contact, $email, $map_embed, $edit_id);
            $stmt->execute();
            $stmt->close();
            $message = "Footer details updated successfully!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO footer_settings (address, contact, email, map_embed) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $address, $contact, $email, $map_embed);
            $stmt->execute();
            $stmt->close();
            $message = "Footer details added successfully!";
        }
    } else {
        $message = "All fields except map are required.";
    }
}

// Fetch all footer settings for listing
$footer_list = [];
$result = $conn->query("SELECT * FROM footer_settings ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $footer_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Footer Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $edit_mode ? 'Edit Footer Entry' : 'Add Footer Entry' ?></h4>
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
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="2" required><?= htmlspecialchars($edit_mode ? $edit_footer['address'] : '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" name="contact" id="contact" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_footer['contact'] : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_footer['email'] : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="map_embed" class="form-label">Google Map Embed Code (iframe)</label>
                            <textarea name="map_embed" id="map_embed" class="form-control" rows="3"><?= htmlspecialchars($edit_mode ? $edit_footer['map_embed'] : '') ?></textarea>
                            <small class="text-muted">Paste the Google Maps iframe embed code here (optional).</small>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Add' ?> Footer Details</button>
                        <?php if ($edit_mode): ?>
                            <a href="admin_footer_settings.php" class="btn btn-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">All Footer Entries</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                    <th>Map</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($footer_list): ?>
                                    <?php foreach ($footer_list as $entry): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($entry['address']) ?></td>
                                            <td><?= htmlspecialchars($entry['contact']) ?></td>
                                            <td><?= htmlspecialchars($entry['email']) ?></td>
                                            <td>
                                                <?php if ($entry['map_embed']): ?>
                                                    <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="admin_footer_settings.php?edit=<?= $entry['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="admin_footer_settings.php?delete=<?= $entry['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No footer entries found.</td>
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