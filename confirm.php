<?php

include("db/connect.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

$bus_id = $_POST['bus_id'];
$name = $_POST['passenger_name'];
$seat = $_POST['seat_number'];

$sql = "INSERT INTO bookings (bus_id, passenger_name, seat_number)
VALUES ('$bus_id','$name','$seat')";
echo "<h2>Booking Successful</h2>";
echo "<br><br>";

echo "<a href='index.php'>Search Another Bus</a>";





}

?>