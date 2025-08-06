<?php
$host = "localhost";
$username = "root";
$password = "test@123"; // Set your MySQL password
$database = "inventorydb";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
