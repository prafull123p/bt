<?php
include 'db.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM carousel WHERE id=$id");
$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $caption = $_POST['caption'];
  if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
    $stmt = $conn->prepare("UPDATE carousel SET image_path=?, caption=? WHERE id=?");
    $stmt->bind_param("ssi", $target, $caption, $id);
  } else {
    $stmt = $conn->prepare("UPDATE carousel SET caption=? WHERE id=?");
    $stmt->bind_param("si", $caption, $id);
  }
  $stmt->execute();
  header("Location: index.php");
}
?>
<form method="POST" enctype="multipart/form-data">
  <img src="<?= $row['image_path'] ?>" width="200"><br>
  <input type="file" name="image">
  <input type="text" name="caption" value="<?= htmlspecialchars($row['caption']) ?>" required>
  <button type="submit">Update</button>
</form>
