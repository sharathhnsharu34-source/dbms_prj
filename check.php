<?php
$conn = new mysqli("localhost", "root", "", "bus_booking_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$res = $conn->query("SHOW COLUMNS FROM bookings");
while($row = $res->fetch_assoc()) { print_r($row); }
