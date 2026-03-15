<?php

include("db/connect.php");

$bus_id=$_POST['bus_id'];
$name=$_POST['passenger_name'];
$seat=$_POST['seat_number'];

$sql="INSERT INTO bookings (bus_id,passenger_name,seat_number)
VALUES ('$bus_id','$name','$seat')";

if($conn->query($sql)==TRUE){

echo "<script>

alert('Booking Successful');

window.location='index.php';

</script>";

}else{

echo "Error: ".$conn->error;

}

?>