<?php
$conn = new mysqli("localhost","root","","bus_booking_db");

if($conn->connect_error){
die("Connection Failed: " . $conn->connect_error);
}
?>