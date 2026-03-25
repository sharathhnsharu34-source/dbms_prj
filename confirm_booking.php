<?php
session_start();
include("db/connect.php");

$bus_id = $_POST['bus_id'];
$seat = $_POST['seat_number'];

$primary_email = $_POST['primary_email'] ?? '';
$primary_phone = $_POST['primary_phone'] ?? '';

$user_id = isset($_SESSION['user_id']) ? "'" . $conn->real_escape_string($_SESSION['user_id']) . "'" : 'NULL';

$passenger_names = $_POST['passenger_names'] ?? [];
$passenger_ages = $_POST['passenger_ages'] ?? [];
$passenger_genders = $_POST['passenger_genders'] ?? [];
$passenger_id_types = $_POST['passenger_id_types'] ?? [];
$passenger_id_numbers = $_POST['passenger_id_numbers'] ?? [];

// The main name for the booking will be the first passenger's name
$primary_name = !empty($passenger_names) ? $conn->real_escape_string($passenger_names[0]) : '';

$primary_phone_esc = $conn->real_escape_string($primary_phone);
$primary_email_esc = $conn->real_escape_string($primary_email);
$seat_esc = $conn->real_escape_string($seat);
$bus_id_esc = $conn->real_escape_string($bus_id);

// Insert into bookings table
$sql = "INSERT INTO bookings (bus_id, passenger_name, seat_number, user_id, primary_phone, primary_email)
VALUES ('$bus_id_esc', '$primary_name', '$seat_esc', $user_id, '$primary_phone_esc', '$primary_email_esc')";

if ($conn->query($sql) == TRUE) {
    $booking_id = $conn->insert_id;
    
    // Insert each passenger
    $seats_array = array_filter(array_map('trim', explode(',', $seat)));
    
    // Default passenger name format for ticket (e.g. "John Doe + 2")
    $ticket_passenger_name = $primary_name;
    if (count($passenger_names) > 1) {
        $ticket_passenger_name .= " + " . (count($passenger_names) - 1) . " others";
    }
    
    // To feed into confirmation_ticket.php
    $_POST['passenger_name'] = $ticket_passenger_name;
    
    // Save passengers
    foreach ($seats_array as $index => $seat_num) {
        if (isset($passenger_names[$index])) {
            $p_name = $conn->real_escape_string($passenger_names[$index]);
            $p_age = (int)$passenger_ages[$index];
            $p_gender = $conn->real_escape_string($passenger_genders[$index]);
            $p_id_type = $conn->real_escape_string($passenger_id_types[$index]);
            $p_id_number = $conn->real_escape_string($passenger_id_numbers[$index]);
            $p_seat = $conn->real_escape_string($seat_num);
            
            // Safe insertion into passengers table
            $sql_pass = "INSERT INTO passengers (booking_id, name, age, gender, id_type, id_number, seat_number) 
                         VALUES ($booking_id, '$p_name', $p_age, '$p_gender', '$p_id_type', '$p_id_number', '$p_seat')";
                         
            $conn->query($sql_pass);
        }
    }

    // Redirect to payment.php with auto-submitting form
    echo "<form id='payForm' action='payment.php' method='POST'>";
    foreach($_POST as $key => $val) {
        if(is_array($val)) {
            foreach($val as $v) { echo "<input type='hidden' name='{$key}[]' value='".htmlspecialchars($v, ENT_QUOTES)."'>"; }
        } else {
            echo "<input type='hidden' name='$key' value='".htmlspecialchars($val, ENT_QUOTES)."'>";
        }
    }
    echo "<input type='hidden' name='booking_id' value='$booking_id'>";
    echo "</form>";
    echo "<script>document.getElementById('payForm').submit();</script>";
    exit();

} else {
    echo "Error: " . $conn->error;
}
?>