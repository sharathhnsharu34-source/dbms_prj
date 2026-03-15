<?php
include("db/connect.php");

$source=$_POST['source'];
$destination=$_POST['destination'];

$sql="SELECT * FROM buses WHERE source='$source' AND destination='$destination'";
$result=$conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Available Buses</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="navbar">
<h1>Available Buses</h1>
</div>

<div class="container">

<table>

<tr>
<th>Bus Name</th>
<th>Departure</th>
<th>Arrival</th>
<th>Price</th>
<th>Book</th>
</tr>

<?php

if($result->num_rows>0){

while($row=$result->fetch_assoc()){

echo "<tr>";

echo "<td>".$row['bus_name']."</td>";
echo "<td>".$row['departure_time']."</td>";
echo "<td>".$row['arrival_time']."</td>";
echo "<td>₹".$row['price']."</td>";

echo "<td>
<a class='book-btn' href='book.php?bus_id=".$row['bus_id']."'>Book</a>
</td>";

echo "</tr>";

}

}else{

echo "<tr><td colspan='5'>No buses available</td></tr>";

}

?>

</table>

<br>

<a href="index.php" class="back-btn">Search Again</a>

</div>

</body>
</html>