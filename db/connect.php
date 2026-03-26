<?php
$conn = new mysqli("localhost","root","","bus_booking_db");

if($conn->connect_error){
die("Connection Failed: " . $conn->connect_error);
}

// Note: Expired bookings cleanup should be performed via delete_expired.php or a cron job,
// NOT on every page load, to prevent unexpected booking deletions during checkout.
?>