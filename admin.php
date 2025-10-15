<?php
define('FALLBACK_IMG', 'fallback.png');
include 'db.php'; // Include your database connection file ?>
<?php
include 'auth.php'; // Include your authentication/authorization file

// Check if the user is an admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  header("Location: login.php");
  exit;
}
?>
<?php include 'admin_nav.php'; // Include your navigation bar ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="container py-5">
  <?php
  // Ensure CSRF token is set
  if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  // Initialize variables
  $search = '';
  $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
  $limit = 10; // Number of records per page
  $offset = ($page - 1) * $limit;
  // Handle search
  if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search = $conn->real_escape_string($search);
    $query = "SELECT * FROM carousel WHERE caption LIKE '%$search%' ORDER BY id DESC LIMIT $limit OFFSET $offset";
  } else {
    $query = "SELECT * FROM carousel ORDER BY id DESC LIMIT $limit OFFSET $offset";
  }
  $paginatedResult = $conn->query($query);
  // Handle bulk delete
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete']) && isset($_POST['slide_ids'])) {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
      $ids = implode(',', array_map('intval', $_POST['slide_ids']));
      $conn->query("DELETE FROM carousel WHERE id IN ($ids)");
      header("Location: admin.php?search=" . urlencode($search));
      exit;
    } else {
      echo "<div class='alert alert-danger'>Invalid CSRF token.</div>";
    }
  }
  // Handle single slide actions
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slide_action']) && isset($_POST['slide_id'])) {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
      $slideId = (int) $_POST['slide_id'];
      if ($_POST['slide_action'] === 'accept') {
        $conn->query("UPDATE carousel SET status='accepted' WHERE id=$slideId");
      } elseif ($_POST['slide_action'] === 'delete') {
        $conn->query("DELETE FROM carousel WHERE id=$slideId");
      }
      header("Location: admin.php?search=" . urlencode($search));
      exit;
    }
  }
  // Count total records for pagination
  $escapedSearch = $conn->real_escape_string($search);
  $totalQuery = "SELECT COUNT(*) as total FROM carousel" . (empty($search) ? "" : " WHERE caption LIKE '%$escapedSearch%'");
  $totalResult = $conn->query($totalQuery);
  $totalRow = $totalResult->fetch_assoc();
  $totalRecords = $totalRow['total'];
  $totalPages = ceil($totalRecords / $limit);
  $totalResult = $conn->query($totalQuery);
  $totalRow = $totalResult->fetch_assoc();
  $totalRecords = $totalRow['total'];
  $totalPages = ceil($totalRecords / $limit);
  ?>
  <!-- Include navigation bar -->
  <?php include 'admin_nav.php'; ?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


  <!-- Admin Quick Links Cards Section -->
  <div class="container my-4">
    <div class="row g-4">
      <div class="col-md-4">
        <a href="admin_staff.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-people-fill display-4 text-primary mb-3"></i>
              <h5 class="card-title">Manage Staff</h5>
              <p class="card-text text-muted">Add, edit, or remove staff members.</p>
            </div>
          </div>
        </a>
        
      </div>
      <div class="col-md-4">
        <a href="admin_events.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-calendar-event-fill display-4 text-success mb-3"></i>
              <h5 class="card-title">Manage Events</h5>
              <p class="card-text text-muted">Create and update event details.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="admin_footer_settings.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-geo-alt-fill display-4 text-danger mb-3"></i>
              <h5 class="card-title">Footer Settings</h5>
              <p class="card-text text-muted">Edit address, contact, email, and map for the footer.</p>
            </div>
          </div>
        </a>
      </div>
  
      <div class="col-md-4">
        <a href="admin_aboutus.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-info-circle-fill display-4 text-warning mb-3"></i>
              <h5 class="card-title">About Us</h5>
              <p class="card-text text-muted">Add, edit, or remove About Us content and images.</p>
              <a href="admin_aboutus.php?view=all" class="btn btn-outline-primary btn-sm mt-2">View All</a>
            </div>
          </div>
        </a>
      </div>
      
      <div class="col-md-4">
        <a href="admin_users.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-person-lines-fill display-4 text-info mb-3"></i>
              <h5 class="card-title">Manage Users</h5>
              <p class="card-text text-muted">Add, edit, assign roles and features to users.</p>
              <a href="admin_users.php" class="btn btn-outline-info btn-sm mt-2">View All Users</a>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="admin_gallery_upload.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-images display-4 text-success mb-3"></i>
              <h5 class="card-title">Image Gallery</h5>
              <p class="card-text text-muted">Upload and manage gallery images.</p>
              <a href="gallery.php" class="btn btn-outline-success btn-sm mt-2">View Gallery</a>
            </div>
          </div>
        </a>
      </div>
        <div class="col-md-4">
        <a href="admin_quote.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-person-check-fill display-4 text-warning mb-3"></i>
              <h5 class="card-title">Manage quote</h5>
              <p class="card-text text-muted">Add, edit, and manage quote.</p>
              <a href="admin_quote.php" class="btn btn-outline-warning btn-sm mt-2">View quote</a>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="admin_blog.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-journal-text display-4 text-primary mb-3"></i>
              <h5 class="card-title">Blog Management</h5>
              <p class="card-text text-muted">Create, edit, and delete blog posts.</p>
              <a href="admin_blog.php" class="btn btn-outline-primary btn-sm mt-2">View Blog</a>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="admin_admissions.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-person-check-fill display-4 text-warning mb-3"></i>
              <h5 class="card-title">Manage Admissions</h5>
              <p class="card-text text-muted">Add, edit, and manage admitted students.</p>
              <a href="admin_admissions.php" class="btn btn-outline-warning btn-sm mt-2">View Admissions</a>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="admin_fees.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-cash-coin display-4 text-success mb-3"></i>
              <h5 class="card-title">Fee Management</h5>
              <p class="card-text text-muted">Generate, store, and calculate student fees.</p>
              <a href="admin_fees.php" class="btn btn-outline-success btn-sm mt-2">Manage Fees</a>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="admin_carousel.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-sliders display-4 text-secondary mb-3"></i>
              <h5 class="card-title">Carousel Management</h5>
              <p class="card-text text-muted">Add, edit, and manage homepage carousel slides.</p>
              <a href="admin_carousel.php" class="btn btn-outline-secondary btn-sm mt-2">Manage Carousel</a>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="admin_campus_life.php" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
              <i class="bi bi-building display-4 text-warning mb-3"></i>
              <h5 class="card-title">Campus Life</h5>
              <p class="card-text text-muted">Add, edit, and manage campus life cards and features.</p>
              <a href="admin_campus_life.php" class="btn btn-outline-warning btn-sm mt-2">Manage Campus Life</a>
            </div>
          </div>
        </a>
      </div>

      <!-- Add more cards as needed -->
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>

</html>
