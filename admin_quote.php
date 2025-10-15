<?php
 include_once 'db.php';


// Handle Add
if (isset($_POST['add'])) {
    $quote = $_POST['quote'];
    $author = $_POST['author'];
    $conn->query("INSERT INTO quotes (quote, author) VALUES ('$quote', '$author')");
}

// Handle Edit
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $quote = $_POST['quote'];
    $author = $_POST['author'];
    $conn->query("UPDATE quotes SET quote='$quote', author='$author' WHERE id=$id");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM quotes WHERE id=$id");
}

// Fetch all quotes
$result = $conn->query("SELECT * FROM quotes");
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
            <td>
              <textarea name="quote" class="form-control"><?php echo htmlspecialchars($row['quote']); ?></textarea>
            </td>
            <td>
              <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($row['author']); ?>">
            </td>
            <td>
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <button type="submit" name="edit" class="btn btn-success btn-sm">Save</button>
              <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this quote?')">Delete</a>
            </td>
          </form>
        </tr>
      <?php } ?>
    </tbody>
  </table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>