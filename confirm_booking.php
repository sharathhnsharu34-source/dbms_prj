<?php
session_start();
include("db/connect.php");

$bus_id=$_POST['bus_id'];
$name=$_POST['passenger_name'];
$seat=$_POST['seat_number'];

$user_id = isset($_SESSION['user_id']) ? "'".$_SESSION['user_id']."'" : 'NULL';

$sql="INSERT INTO bookings (bus_id,passenger_name,seat_number,user_id)
VALUES ('$bus_id','$name','$seat',$user_id)";

if($conn->query($sql)==TRUE){

    include("confirmation_ticket.php");

}else{

echo "Error: ".$conn->error;

}

?>