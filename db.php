<?php
// filepath: c:\xampp\htdocs\bt\db.php
//$conn = new mysqli("localhost", "u376328101_prafullsir", "Batmul@496001", "u376328101_batdata");
//if ($conn->connect_error) {
 // die("Connection failed: " . $conn->connect_error);
//}
$conn = new mysqli("localhost", "root", "", "batdata");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// $conn = new mysqli("localhost", "u376328101_prafullsir", "Batmul@496001", "u376328101_batdata");
// if ($conn->connect_error) {
//   die("Connection failed: " . $conn->connect_error);
// }
?> 
