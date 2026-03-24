<?php
// delete_expired.php
// Run this file manually or via a cron job
// Example Cron job (runs daily at midnight): 
// 0 0 * * * /usr/bin/php /Applications/XAMPP/xamppfiles/htdocs/bus-booking-sys/delete_expired.php

$conn = new mysqli("localhost", "root", "", "bus_booking_db");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Auto-remove expired bookings (where journey_date is < today)
$cleanup_sql = "DELETE bookings FROM bookings 
                JOIN buses ON bookings.bus_id = buses.bus_id 
                WHERE buses.journey_date < CURDATE()";

if ($conn->query($cleanup_sql) === TRUE) {
    echo "Expired bookings cleaned up successfully at " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Error cleaning up bookings: " . $conn->error . "\n";
}

$conn->close();
?>
