<?php
include 'db.php';
$id = $_GET['id'];
$result = $conn->query("SELECT image_path FROM carousel WHERE id=$id");
$row = $result->fetch_assoc();
unlink($row['image_path']); // delete image file
$conn->query("DELETE FROM carousel WHERE id=$id");
header("Location: index.php");
