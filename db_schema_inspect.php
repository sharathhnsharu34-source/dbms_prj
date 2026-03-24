<?php
$conn = new mysqli("localhost", "root", "", "bus_booking_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("DESCRIBE bookings");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
echo "-----\n";
$result2 = $conn->query("DESCRIBE buses");
while ($row = $result2->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
