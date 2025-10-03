<?php
include 'auth.php';
include 'db.php';

$message = '';
$edit_mode = false;
$edit_id = null;
$edit_name = '';
$edit_email = '';
$edit_course = '';
$edit_status = 'admitted';

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM admissions WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Admission record deleted.";
}

// Handle Edit (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT name, email, course, status FROM admissions WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_name, $edit_email, $edit_course, $edit_status);
    $stmt->fetch();
    $stmt->close();
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $status = $_POST['status'] ?? 'admitted';

    if ($name && $email && $course) {
        if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
            // Update
            $edit_id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE admissions SET name=?, email=?, course=?, status=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $course, $status, $edit_id);
            $stmt->execute();
            $stmt->close();
            $message = "Admission record updated!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO admissions (name, email, course, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $course, $status);
            $stmt->execute();
            $stmt->close();
            $message = "Admission record added!";
        }
    } else {
        $message = "All fields are required.";
    }
}

// Fetch all admissions for listing
$admissions = [];
$result = $conn->query("SELECT * FROM admissions ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $admissions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admissions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $edit_mode ? 'Edit Admission' : 'Add Admission' ?></h4>
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
                            <label for="name" class="form-label">Student Name</label>
                            <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_name : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_email : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="course" class="form-label">Course</label>
                            <input type="text" name="course" id="course" class="form-control" required value="<?= htmlspecialchars($edit_mode ? $edit_course : '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="admitted" <?= ($edit_mode && $edit_status == 'admitted') ? 'selected' : '' ?>>Admitted</option>
                                <option value="pending" <?= ($edit_mode && $edit_status == 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="rejected" <?= ($edit_mode && $edit_status == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Add' ?> Admission</button>
                        <?php if ($edit_mode): ?>
                            <a href="admin_admissions.php" class="btn btn-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">All Admissions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($admissions): ?>
                                    <?php foreach ($admissions as $adm): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($adm['name']) ?></td>
                                            <td><?= htmlspecialchars($adm['email']) ?></td>
                                            <td><?= htmlspecialchars($adm['course']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $adm['status']=='admitted'?'success':($adm['status']=='pending'?'warning':'danger') ?>">
                                                    <?= ucfirst($adm['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="admin_admissions.php?edit=<?= $adm['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="admin_admissions.php?delete=<?= $adm['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admission?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No admissions found.</td>
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