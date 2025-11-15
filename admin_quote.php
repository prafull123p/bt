<?php
include_once 'db.php';
include_once __DIR__ . '/includes/csrf.php';

// Handle Add (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    die('Invalid CSRF token');
  }
  $quote = trim($_POST['quote'] ?? '');
  $author = trim($_POST['author'] ?? '');
  if ($quote && $author) {
    $stmt = $conn->prepare("INSERT INTO quotes (quote, author) VALUES (?, ?)");
    $stmt->bind_param('ss', $quote, $author);
    $stmt->execute();
    $stmt->close();
  }
}

// Handle Edit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    die('Invalid CSRF token');
  }
  $id = intval($_POST['id'] ?? 0);
  $quote = trim($_POST['quote'] ?? '');
  $author = trim($_POST['author'] ?? '');
  if ($id && $quote && $author) {
    $stmt = $conn->prepare("UPDATE quotes SET quote = ?, author = ? WHERE id = ?");
    $stmt->bind_param('ssi', $quote, $author, $id);
    $stmt->execute();
    $stmt->close();
  }
}

// Handle Delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_quote'])) {
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    die('Invalid CSRF token');
  }
  $id = intval($_POST['delete_id'] ?? 0);
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM quotes WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
  }
}

// Fetch all quotes
$result = $conn->query("SELECT * FROM quotes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quote Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

  <h2>Add New Quote</h2>
  <form method="POST" class="mb-4">
    <?php echo csrf_field(); ?>
    <div class="mb-2">
      <textarea name="quote" class="form-control" placeholder="Quote" required></textarea>
    </div>
    <div class="mb-2">
      <input type="text" name="author" class="form-control" placeholder="Author" required>
    </div>
    <button type="submit" name="add" class="btn btn-primary">Add Quote</button>
  </form>

  <h2>Manage Quotes</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Quote</th>
        <th>Author</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <form method="POST">
            <?php echo csrf_field(); ?>
            <td>
              <textarea name="quote" class="form-control"><?php echo htmlspecialchars($row['quote']); ?></textarea>
            </td>
            <td>
              <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($row['author']); ?>">
            </td>
            <td>
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <button type="submit" name="edit" class="btn btn-success btn-sm">Save</button>
          </form>
              <form method="POST" style="display:inline-block;margin-left:.5rem;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                <button type="submit" name="delete_quote" class="btn btn-danger btn-sm" onclick="return confirm('Delete this quote?')">Delete</button>
              </form>
            </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>