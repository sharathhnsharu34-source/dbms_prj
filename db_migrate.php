<?php
$conn = new mysqli("localhost", "root", "", "bus_booking_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql1 = "ALTER TABLE bookings 
         ADD COLUMN primary_phone VARCHAR(15),
         ADD COLUMN primary_email VARCHAR(100),
         ADD COLUMN emergency_contact VARCHAR(15),
         ADD COLUMN special_request TEXT,
         ADD COLUMN seat_preference VARCHAR(50),
         ADD COLUMN coupon_code VARCHAR(20)";
if($conn->query($sql1)) {
    echo "Alter Bookings success. ";
} else {
    echo "Alter Bookings skipped (may already exist). ";
}

$sql2 = "CREATE TABLE IF NOT EXISTS passengers (
          passenger_id INT AUTO_INCREMENT PRIMARY KEY,
          booking_id INT,
          name VARCHAR(100),
          age INT,
          gender VARCHAR(10),
          phone VARCHAR(15),
          email VARCHAR(100),
          id_type VARCHAR(50),
          id_number VARCHAR(100),
          seat_number VARCHAR(10),
          FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
        )";
if($conn->query($sql2)) {
    echo "Create Passengers success.";
} else {
    echo "Error Passengers: " . $conn->error;
}
?>
