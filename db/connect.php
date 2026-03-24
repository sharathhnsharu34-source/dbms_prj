<?php
$conn = new mysqli("localhost","root","","bus_booking_db");

if($conn->connect_error){
die("Connection Failed: " . $conn->connect_error);
}

// Auto-remove expired bookings (where journey_date is < today)
$cleanup_sql = "DELETE bookings FROM bookings 
                JOIN buses ON bookings.bus_id = buses.bus_id 
                WHERE buses.journey_date < CURDATE()";
$conn->query($cleanup_sql);
?>