<?php
include("../db/connect.php");

$sql="SELECT bookings.booking_id,
buses.bus_name,
buses.source,
buses.destination,
bookings.passenger_name,
bookings.seat_number
FROM bookings
JOIN buses ON bookings.bus_id=buses.bus_id";

$result=$conn->query($sql);

echo "<h2>All Bookings</h2>";

echo "<table border='1'>
<tr>
<th>Booking ID</th>
<th>Bus</th>
<th>Route</th>
<th>Passenger</th>
<th>Seat</th>
</tr>";

while($row=$result->fetch_assoc()){

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