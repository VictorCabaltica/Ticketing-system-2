<?php
// database connection
$conn = new mysqli("localhost", "root", "", "yna_db");


if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>