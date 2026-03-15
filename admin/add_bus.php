<?php
include("../db/connect.php");

if(isset($_POST['addbus'])){

$busname=$_POST['busname'];
$source=$_POST['source'];
$destination=$_POST['destination'];
$departure=$_POST['departure'];
$arrival=$_POST['arrival'];
$seats=$_POST['seats'];
$price=$_POST['price'];

$sql="INSERT INTO buses(bus_name,source,destination,departure_time,arrival_time,total_seats,price)
VALUES('$busname','$source','$destination','$departure','$arrival','$seats','$price')";

if($conn->query($sql)){
echo "Bus added successfully";
}else{
echo "Error adding bus";
}
}
?>

<h2>Add Bus</h2>

<form method="POST">

Bus Name  
<input type="text" name="busname" required>

Source  
<input type="text" name="source" required>

Destination  
<input type="text" name="destination" required>

Departure Time  
<input type="time" name="departure" required>

Arrival Time  
<input type="time" name="arrival" required>

Total Seats  
<input type="number" name="seats" required>

Price  
<input type="number" name="price" required>

<button name="addbus">Add Bus</button>

</form>