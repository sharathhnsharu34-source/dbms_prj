<?php
include("../db/connect.php");
echo "<h1 style='text-align:center;'>Admin Dashboard</h1>";
/* Get all buses that have bookings */

$bus_query = "
SELECT DISTINCT buses.bus_id, buses.bus_name, buses.source, buses.destination
FROM buses
JOIN bookings ON buses.bus_id = bookings.bus_id
";

$bus_result = $conn->query($bus_query);

while($bus = $bus_result->fetch_assoc()){

$bus_id = $bus['bus_id'];

echo "<h2>".$bus['bus_name']." (".$bus['source']." → ".$bus['destination'].")</h2>";

/* Table for that bus */

echo "<table border='1'>
<tr>
<th>Booking ID</th>
<th>Passenger</th>
<th>Seat</th>
</tr>";

$booking_query = "
SELECT * FROM bookings
WHERE bus_id = $bus_id
";

$booking_result = $conn->query($booking_query);

while($row = $booking_result->fetch_assoc()){

echo "<tr>";
echo "<td>".$row['booking_id']."</td>";
echo "<td>".$row['passenger_name']."</td>";
echo "<td>".$row['seat_number']."</td>";
echo "</tr>";

}

echo "</table><br><br>";

}
?>

while($row=$result->fetch_assoc()){
    
echo "<h1 style='text-align:center;'>Admin Dashboard</h1>";
echo "<tr>";
echo "<td>".$row['booking_id']."</td>";
echo "<td>".$row['bus_name']."</td>";
echo "<td>".$row['source']." → ".$row['destination']."</td>";
echo "<td>".$row['passenger_name']."</td>";
echo "<td>".$row['seat_number']."</td>";
echo "</tr>";

}

echo "</table>";
?>