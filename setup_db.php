<?php
$conn = new mysqli("localhost", "root", "", "bus_booking_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(15),
  password VARCHAR(255)
)";
if ($conn->query($sql_users) === TRUE) {
    echo "Table users created successfully\n";
} else {
    echo "Error creating users table: " . $conn->error . "\n";
}

// Add user_id to bookings table safely
$sql_check = "SHOW COLUMNS FROM bookings LIKE 'user_id'";
$result = $conn->query($sql_check);
if ($result->num_rows == 0) {
    $sql_alter = "ALTER TABLE bookings ADD user_id INT DEFAULT NULL";
    if ($conn->query($sql_alter) === TRUE) {
        echo "Column user_id added to bookings successfully\n";
    } else {
        echo "Error alter bookings table: " . $conn->error . "\n";
    }
} else {
    echo "Column user_id already exists in bookings\n";
}

$conn->close();
?>
