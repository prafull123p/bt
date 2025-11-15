<?php
include 'auth.php';
include 'db.php';
include_once __DIR__ . '/includes/csrf.php';

$message = '';
$edit_mode = false;
$edit_id = null;
$edit_name = '';
$edit_designation = '';
$edit_photo = '';
$edit_qualification = '';

// CREATE or UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $photo_path = '';

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['photo']['name']);
        $img_tmp = $_FILES['photo']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($img_ext, $allowed)) {
            $img_new_name = uniqid('staff_', true) . '.' . $img_ext;
            $img_dest = "uploads/staff/" . $img_new_name;
            if (!is_dir('uploads/staff')) {
                mkdir('uploads/staff', 0777, true);
            }
            if (move_uploaded_file($img_tmp, $img_dest)) {
                $photo_path = $img_dest;
            }
        }
    }

    if (isset($_POST['save_staff'])) {
        if ($name && $designation && $qualification && $photo_path) {
            $stmt = $conn->prepare("INSERT INTO staff (name, designation, qualification, photo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $designation, $qualification, $photo_path);
            $stmt->execute();
            $stmt->close();
            $message = "Staff member added successfully!";
        } else {
            $message = "All fields are required.";
        }
    } elseif (isset($_POST['update_staff']) && isset($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        if ($name && $designation && $qualification) {
            if ($photo_path) {
                $stmt = $conn->prepare("UPDATE staff SET name=?, designation=?, qualification=?, photo=? WHERE id=?");
                $stmt->bind_param("ssssi", $name, $designation, $qualification, $photo_path, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE staff SET name=?, designation=?, qualification=? WHERE id=?");
                $stmt->bind_param("sssi", $name, $designation, $qualification, $edit_id);
            }
            $stmt->execute();
            $stmt->close();
            $message = "Staff member updated successfully!";
        } else {
            $message = "Name, designation, and qualification are required.";
        }
    }
}

// DELETE (via POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    die('Invalid CSRF token');
  }
  $delete_id = intval($_POST['delete_id']);
  if ($delete_id) {
    // Optionally delete the photo file from server
    $img_res = $conn->query("SELECT photo FROM staff WHERE id=$delete_id");
    if ($img_res && $img_row = $img_res->fetch_assoc()) {
      if (!empty($img_row['photo']) && file_exists($img_row['photo'])) {
        @unlink($img_row['photo']);
      }
    }
    $stmt = $conn->prepare("DELETE FROM staff WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Staff member deleted.";
  }
}

// Set role (make founder/principal)
// Handle role changes via POST (set/unset). Principal is unique: setting Principal will unset others.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
  $change = $_POST['change_role']; // expected 'set' or 'unset'
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    die('Invalid CSRF token');
  }
  $role = trim($_POST['role'] ?? '');
  $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
  if ($id && in_array(strtolower($role), ['founder','principal','staff',''])) {
    // Normalize role names
    $roleNormalized = '';
    if (strtolower($role) === 'founder') $roleNormalized = 'Founder';
    elseif (strtolower($role) === 'principal') $roleNormalized = 'Principal';
    elseif (strtolower($role) === 'staff' || $role === '') $roleNormalized = 'Staff';

    if ($change === 'set') {
      // If setting Principal, unset other Principals first so Principal remains unique
      if ($roleNormalized === 'Principal') {
        $conn->query("UPDATE staff SET designation = 'Staff' WHERE designation = 'Principal'");
      }
      $stmt = $conn->prepare("UPDATE staff SET designation = ? WHERE id = ?");
      $stmt->bind_param("si", $roleNormalized, $id);
      if ($stmt->execute()) {
        $message = "Role updated to $roleNormalized.";
      } else {
        $message = "Failed to update role.";
      }
      $stmt->close();
    } elseif ($change === 'unset') {
      $unsetRole = 'Staff';
      $stmt = $conn->prepare("UPDATE staff SET designation = ? WHERE id = ?");
      $stmt->bind_param("si", $unsetRole, $id);
      if ($stmt->execute()) {
        $message = "Role unset (now Staff).";
      } else {
        $message = "Failed to unset role.";
      }
      $stmt->close();
    }
  } else {
    $message = 'Invalid role change request.';
  }
  // redirect to avoid resubmission
  header('Location: admin_staff.php');
  exit;
}

// EDIT (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT name, designation, qualification, photo FROM staff WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_name, $edit_designation, $edit_qualification, $edit_photo);
    $stmt->fetch();
    $stmt->close();
}

// READ (fetch all)
$staff = [];
$result = $conn->query("SELECT id, name, designation, qualification, photo FROM staff ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Staff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
  <h2 class="mb-4"><?= $edit_mode ? 'Edit Staff Member' : 'Add Staff Member' ?></h2>
  <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data" class="mb-5">
    <?php if ($edit_mode): ?>
      <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
    <?php endif; ?>
    <div class="mb-3">
      <label for="photo" class="form-label">Photo <?= $edit_mode ? '(leave blank to keep current)' : '' ?></label>
      <input type="file" name="photo" id="photo" class="form-control" <?= $edit_mode ? '' : 'required' ?> accept="image/*">
      <?php if ($edit_mode && $edit_photo): ?>
        <div class="mt-2">
          <img src="<?= htmlspecialchars($edit_photo) ?>" alt="Staff Photo" style="max-width:120px;">
        </div>
      <?php endif; ?>
    </div>
    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($edit_name) ?>">
    </div>
    <div class="mb-3">
      <label for="designation" class="form-label">Designation</label>
      <input type="text" name="designation" id="designation" class="form-control" required value="<?= htmlspecialchars($edit_designation) ?>">
    </div>
    <div class="mb-3">
      <label for="qualification" class="form-label">Qualification</label>
      <input type="text" name="qualification" id="qualification" class="form-control" required value="<?= htmlspecialchars($edit_qualification) ?>">
    </div>
    <?php if ($edit_mode): ?>
      <button type="submit" name="update_staff" class="btn btn-success">Update Staff</button>
      <a href="admin_staff.php" class="btn btn-secondary">Cancel</a>
    <?php else: ?>
      <button type="submit" name="save_staff" class="btn btn-primary">Add Staff</button>
    <?php endif; ?>
  </form>

  <h4 class="mb-3">Our Staff</h4>
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-primary">
        <tr>
          <th>Photo</th>
          <th>Name</th>
          <th>Designation</th>
          <th>Qualification</th>
          <th style="width: 180px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($staff): ?>
          <?php foreach ($staff as $member): ?>
            <tr>
              <td>
                <?php if (!empty($member['photo'])): ?>
                  <img src="<?= htmlspecialchars($member['photo']) ?>" alt="Staff Photo" style="max-width:80px;">
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($member['name']) ?></td>
              <td><?= htmlspecialchars($member['designation']) ?></td>
              <td><?= htmlspecialchars($member['qualification']) ?></td>
              <td>
                <a href="admin_staff.php?view=<?= $member['id'] ?>" class="btn btn-sm btn-info">View</a>
                <a href="admin_staff.php?edit=<?= $member['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <form method="POST" style="display:inline-block;margin:0 0 0 .25rem;">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="delete_id" value="<?= $member['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this staff member?')">Delete</button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-role-action="set" data-role="founder" data-id="<?= $member['id'] ?>" data-name="<?= htmlspecialchars($member['name'], ENT_QUOTES) ?>">Make Founder</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-role-action="set" data-role="principal" data-id="<?= $member['id'] ?>" data-name="<?= htmlspecialchars($member['name'], ENT_QUOTES) ?>">Make Principal</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-role-action="unset" data-id="<?= $member['id'] ?>" data-name="<?= htmlspecialchars($member['name'], ENT_QUOTES) ?>">Unset Role</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center">No staff found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
  // View feature: show staff details in a modal or section
  if (isset($_GET['view']) && is_numeric($_GET['view'])) {
      $view_id = intval($_GET['view']);
      $stmt = $conn->prepare("SELECT name, designation, qualification, photo FROM staff WHERE id=?");
      $stmt->bind_param("i", $view_id);
      $stmt->execute();
      $stmt->bind_result($v_name, $v_designation, $v_qualification, $v_photo);
      if ($stmt->fetch()):
  ?>
  <!-- Staff View Modal -->
  <div class="modal show" tabindex="-1" style="display:block; background:rgba(0,0,0,0.5);" id="staffViewModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Staff Details</h5>
          <a href="admin_staff.php" class="btn-close"></a>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <?php if (!empty($v_photo)): ?>
              <img src="<?= htmlspecialchars($v_photo) ?>" alt="Staff Photo" style="max-width:120px;">
            <?php endif; ?>
          </div>
          <p><strong>Name:</strong> <?= htmlspecialchars($v_name) ?></p>
          <p><strong>Designation:</strong> <?= htmlspecialchars($v_designation) ?></p>
          <p><strong>Qualification:</strong> <?= htmlspecialchars($v_qualification) ?></p>
        </div>
        <div class="modal-footer">
          <a href="admin_staff.php" class="btn btn-secondary">Close</a>
        </div>
      </div>
    </div>
  </div>
  <style>
  body { overflow: hidden; }
  </style>
  <script>
    // Close modal on ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === "Escape") window.location = "admin_staff.php";
    });
  </script>
  <?php
      endif;
      $stmt->close();
  }
  ?>
  
  <!-- Role change confirmation modal (single reusable template) -->
  <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" id="roleForm">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="change_role" value="">
          <input type="hidden" name="role" value="">
          <input type="hidden" name="id" value="">
          <div class="modal-header">
            <h5 class="modal-title" id="roleModalLabel">Confirm Role Change</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p id="roleModalBody">Are you sure?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="roleModalConfirm">Confirm</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (for modal) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      const roleModalEl = document.getElementById('roleModal');
      const roleForm = document.getElementById('roleForm');
      const roleModalBody = document.getElementById('roleModalBody');
      const inputChangeRole = roleForm.querySelector('input[name="change_role"]');
      const inputRole = roleForm.querySelector('input[name="role"]');
      const inputId = roleForm.querySelector('input[name="id"]');
      let bsModal = null;
      // Initialize bootstrap modal when needed
      function initModal(){
        if (!bsModal) bsModal = new bootstrap.Modal(roleModalEl);
      }

      document.querySelectorAll('button[data-role-action]').forEach(btn=>{
        btn.addEventListener('click', function(e){
          initModal();
          const action = this.getAttribute('data-role-action');
          const role = this.getAttribute('data-role') || '';
          const id = this.getAttribute('data-id');
          const name = this.getAttribute('data-name') || 'this staff member';
          if (action === 'set') {
            roleModalBody.textContent = `Set ${name} as ${role.charAt(0).toUpperCase()+role.slice(1)}?` + (role==='principal' ? ' This will unset any existing Principal.' : '');
            inputChangeRole.value = 'set';
            inputRole.value = role;
            inputId.value = id;
          } else if (action === 'unset') {
            roleModalBody.textContent = `Unset role for ${name}? They will become 'Staff'.`;
            inputChangeRole.value = 'unset';
            inputRole.value = '';
            inputId.value = id;
          }
          bsModal.show();
        });
      });
    })();
  </script>
</body>
</html>