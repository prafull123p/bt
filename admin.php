<?php
define('FALLBACK_IMG', 'fallback.png');
include 'db.php'; // Include your database connection file ?>
<?php
include 'auth.php'; // Include your authentication/authorization file

// Check if the user is an admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  header("Location: admin_login.php");
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
  // Gallery UI settings (global well intensity)
  $gallery_settings_file = __DIR__ . '/tmp/gallery_settings.json';
  $gallery_intensity = 8;
  if (file_exists($gallery_settings_file)) {
    $raw = @file_get_contents($gallery_settings_file);
    $json = $raw ? json_decode($raw, true) : null;
    if (!empty($json['intensity'])) $gallery_intensity = intval($json['intensity']);
  }
  $gallery_reduced_motion = false;
  if (!empty($json) && !empty($json['reduced_motion'])) $gallery_reduced_motion = true;

  // Handle save gallery settings
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gallery_settings'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      echo "<div class='alert alert-danger'>Invalid CSRF token.</div>";
    } else {
  $ival = isset($_POST['intensity']) ? (int) $_POST['intensity'] : $gallery_intensity;
  $rm = isset($_POST['reduced_motion']) ? 1 : 0;
  $payload = ['intensity' => $ival, 'reduced_motion' => $rm];
      if (!is_dir(__DIR__ . '/tmp')) @mkdir(__DIR__ . '/tmp', 0777, true);
      $ok = @file_put_contents($gallery_settings_file, json_encode($payload, JSON_UNESCAPED_UNICODE));
      if ($ok === false) {
        echo "<div class='alert alert-warning'>Could not save settings. Check file permissions on tmp/.</div>";
      } else {
        // redirect to avoid resubmit
        header('Location: admin.php'); exit;
      }
    }
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


  <!-- Admin Quick Links Cards Section (auto-generated) -->
  <div class="container my-4">
    <div class="row g-4">
      <?php
      // Provide metadata for known admin pages. Fallback will humanize filenames.
      $meta = [
        'admin_staff.php' => ['title' => 'Manage Staff', 'desc' => 'Add, edit, or remove staff members.', 'icon' => 'bi-people-fill', 'btn' => null],
        'admin_notification.php' => ['title' => 'Manage Notifications', 'desc' => 'Add, edit, or remove notifications.', 'icon' => 'bi-bell-fill', 'btn' => null],
        'admin_events.php' => ['title' => 'Manage Events', 'desc' => 'Create and update event details.', 'icon' => 'bi-calendar-event-fill', 'btn' => null],
        'admin_footer_settings.php' => ['title' => 'Footer Settings', 'desc' => 'Edit address, contact and map for the footer.', 'icon' => 'bi-geo-alt-fill', 'btn' => null],
        'admin_aboutus.php' => ['title' => 'About Us', 'desc' => 'Manage About Us content and images.', 'icon' => 'bi-info-circle-fill', 'btn' => ['label' => 'View All', 'link' => 'admin_aboutus.php?view=all']],
        'admin_users.php' => ['title' => 'Manage Users', 'desc' => 'Add, edit, assign roles and features to users.', 'icon' => 'bi-person-lines-fill', 'btn' => ['label' => 'View All Users', 'link' => 'admin_users.php']],
        'admin_gallery_upload.php' => ['title' => 'Image Gallery', 'desc' => 'Upload and manage gallery images.', 'icon' => 'bi-images', 'btn' => ['label' => 'View Gallery', 'link' => 'gallery.php']],
        'admin_quote.php' => ['title' => 'Manage Quotes', 'desc' => 'Add, edit, and manage quotes.', 'icon' => 'bi-person-check-fill', 'btn' => ['label' => 'View Quotes', 'link' => 'admin_quote.php']],
        'admin_blog.php' => ['title' => 'Blog Management', 'desc' => 'Create, edit, and delete blog posts.', 'icon' => 'bi-journal-text', 'btn' => ['label' => 'View Blog', 'link' => 'admin_blog.php']],
        'admin_admissions.php' => ['title' => 'Manage Admissions', 'desc' => 'Add, edit, and manage admitted students.', 'icon' => 'bi-person-check-fill', 'btn' => ['label' => 'View Admissions', 'link' => 'admin_admissions.php']],
        'admin_fees.php' => ['title' => 'Fee Management', 'desc' => 'Generate, store, and calculate student fees.', 'icon' => 'bi-cash-coin', 'btn' => ['label' => 'Manage Fees', 'link' => 'admin_fees.php']],
        'admin_carousel.php' => ['title' => 'Carousel Management', 'desc' => 'Manage homepage carousel slides.', 'icon' => 'bi-sliders', 'btn' => ['label' => 'Manage Carousel', 'link' => 'admin_carousel.php']],
  'admin_campus_life.php' => ['title' => 'Campus Life', 'desc' => 'Manage campus life cards and features.', 'icon' => 'bi-building', 'btn' => ['label' => 'Manage Campus Life', 'link' => 'admin_campus_life.php']],
      ];

  // Static Gallery Settings card (open modal)
  echo "<div class=\"col-md-4\">";
  echo "<div class=\"card shadow-sm h-100 text-center\">";
  echo "<div class=\"card-body\">";
  echo "<i class=\"bi bi-sliders display-4 text-primary mb-3\"></i>";
  echo "<h5 class=\"card-title\">Gallery Settings</h5>";
  echo "<p class=\"card-text text-muted\">Adjust background well intensity and behavior.</p>";
  echo "<button class=\"btn btn-outline-primary btn-sm mt-2\" data-bs-toggle=\"modal\" data-bs-target=\"#gallerySettingsModal\">Edit Settings</button>";
  echo "</div></div></div>";

  // Find all admin_*.php files in project root
  foreach (glob(__DIR__ . '/admin_*.php') as $file) {
        $fname = basename($file);
        // Skip helper/navigation/login pages
        if (in_array($fname, ['admin_nav.php', 'admin_login.php'])) continue;
        $m = isset($meta[$fname]) ? $meta[$fname] : null;
        $title = $m ? $m['title'] : ucwords(str_replace(['admin_', '.php', '_'], ['', '', ' '], $fname));
        $desc = $m ? $m['desc'] : 'Manage ' . strtolower(str_replace(['admin_', '.php', '_'], ['', '', ' '], $fname));
        $icon = $m ? $m['icon'] : 'bi-gear-fill';
        $btn = $m && isset($m['btn']) ? $m['btn'] : null;
        echo "<div class=\"col-md-4\">";
        echo "<a href=\"$fname\" class=\"text-decoration-none\">";
        echo "<div class=\"card shadow-sm h-100 text-center\">";
        echo "<div class=\"card-body\">";
        echo "<i class=\"bi $icon display-4 text-primary mb-3\"></i>";
        echo "<h5 class=\"card-title\">" . htmlspecialchars($title) . "</h5>";
        echo "<p class=\"card-text text-muted\">" . htmlspecialchars($desc) . "</p>";
        if ($btn) {
          echo "<a href=\"" . htmlspecialchars($btn['link']) . "\" class=\"btn btn-outline-primary btn-sm mt-2\">" . htmlspecialchars($btn['label']) . "</a>";
        }
        echo "</div></div></a></div>";
      }
      ?>
    </div>
  </div>

    <!-- Gallery Settings Modal -->
    <div class="modal fade" id="gallerySettingsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="modal-header">
              <h5 class="modal-title">Gallery Settings</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="intensityRange" class="form-label">Background Well Intensity: <span id="intensityValue"><?php echo htmlspecialchars($gallery_intensity); ?></span></label>
                <input type="range" class="form-range" min="0" max="24" step="1" id="intensityRange" name="intensity" value="<?php echo htmlspecialchars($gallery_intensity); ?>">
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="reduced-motion-opt" name="reduced_motion" <?php if (!empty($gallery_reduced_motion)) echo 'checked'; ?>>
                <label class="form-check-label" for="reduced-motion-opt">Prefer reduced motion for gallery</label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="save_gallery_settings" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <script>
      (function(){
        const range = document.getElementById('intensityRange');
        const out = document.getElementById('intensityValue');
        if (range && out) {
          range.addEventListener('input', ()=> out.textContent = range.value);
        }
      })();
    </script>
</body>

</html>
