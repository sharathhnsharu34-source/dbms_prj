<?php
include("db/connect.php");

$bus_id = $_POST['bus_id'];
$name = $_POST['name'];
$seat = $_POST['seat'];

/* Check if seat already booked for that bus */

$check = $conn->prepare("SELECT * FROM bookings WHERE bus_id=? AND seat_number=?");
$check->bind_param("ii",$bus_id,$seat);
$check->execute();
$result = $check->get_result();

if($result->num_rows > 0){

echo "<h2>Seat already booked for this bus!</h2>";
echo "<a href='index.php'>Search another bus</a>";

}else{

$sql = "INSERT INTO bookings(bus_id,passenger_name,seat_number)
VALUES(?,?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi",$bus_id,$name,$seat);
$stmt->execute();

echo "<h2>Booking Successful</h2>";
echo "<a href='index.php'>Search Another Bus</a>";
}
?>