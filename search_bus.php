<?php
include("db/connect.php");

$source = trim($_POST['source']);
$destination = trim($_POST['destination']);

$sql = "SELECT * FROM buses WHERE source=? AND destination=?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("ss",$source,$destination);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){

echo "<h2>Available Buses</h2>";

while($row = $result->fetch_assoc()){

echo "Bus: ".$row['bus_name']."<br>";
echo "Departure: ".$row['departure_time']."<br>";
echo "Price: ".$row['price']."<br>";

echo "<a href='book.php?bus_id=".$row['bus_id']."'>Book Seat</a>";

echo "<hr>";

}

}else{
echo "No buses available";
}
?>