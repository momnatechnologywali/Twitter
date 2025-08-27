<?php
// db.php - Database connection file
$servername = "localhost"; // Assuming localhost, change if needed
$username = "uws1gwyttyg2r";
$password = "k1tdlhq4qpsf";
$dbname = "db8lh81k0cyet9";
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
 
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
