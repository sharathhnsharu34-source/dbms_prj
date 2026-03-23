<?php
$conn = new mysqli("localhost", "root", "", "bus_booking_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$columns = [
    'primary_phone' => 'VARCHAR(15)',
    'primary_email' => 'VARCHAR(100)',
    'emergency_contact' => 'VARCHAR(15)',
    'special_request' => 'TEXT',
    'seat_preference' => 'VARCHAR(50)',
    'coupon_code' => 'VARCHAR(20)'
];

foreach ($columns as $col => $type) {
    try {
        $conn->query("ALTER TABLE bookings ADD COLUMN $col $type");
        echo "Added $col to bookings.\n";
    } catch (mysqli_sql_exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Column $col already exists.\n";
        } else {
            echo "Error adding $col: " . $e->getMessage() . "\n";
        }
    }
}

$sql2 = "CREATE TABLE IF NOT EXISTS passengers (
          passenger_id INT AUTO_INCREMENT PRIMARY KEY,
          booking_id INT,
          name VARCHAR(100),
          age INT,
          gender VARCHAR(10),
          phone VARCHAR(15),
          email VARCHAR(100),
          id_type VARCHAR(50),
          id_number VARCHAR(100),
          seat_number VARCHAR(10),
          FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
        )";

try {
    if($conn->query($sql2)) {
        echo "Create Passengers success.\n";
    } else {
        echo "Error Passengers: " . $conn->error . "\n";
    }
} catch (mysqli_sql_exception $e) {
    echo "Error Passengers: " . $e->getMessage() . "\n";
}

$conn->close();
?>
