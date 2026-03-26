<?php
session_start();
header('Content-Type: application/json');
include("../db/connect.php");

$bus_id = isset($_POST['bus_id']) ? intval($_POST['bus_id']) : 0;
$seat_number = isset($_POST['seat_number']) ? $conn->real_escape_string($_POST['seat_number']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if($bus_id == 0 || empty($seat_number) || empty($action)) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$sess_id = session_id();

// Universal cleanup of expired holds across instances dynamically
$conn->query("DELETE FROM seats WHERE status = 'Reserved' AND updated_at < NOW() - INTERVAL 5 MINUTE");

if($action == 'hold') {
    // Determine if literally sold completely via Bookings first
    $b_sql = "SELECT booking_id FROM bookings WHERE bus_id='$bus_id' AND FIND_IN_SET('$seat_number', seat_number) AND booking_status != 'Cancelled' AND payment_status != 'Failed'";
    $b_res = $conn->query($b_sql);
    if($b_res && $b_res->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Seat already booked securely.']);
        exit;
    }

    // Check intermediate `seats` holding table
    $chk_sql = "SELECT id, session_id, status FROM seats WHERE bus_id='$bus_id' AND seat_number='$seat_number'";
    $chk_res = $conn->query($chk_sql);
    
    if($chk_res && $chk_res->num_rows > 0) {
        $row = $chk_res->fetch_assoc();
        if($row['status'] == 'Booked') {
            echo json_encode(['success' => false, 'error' => 'Seat genuinely confirmed already.']);
        } else if($row['status'] == 'Reserved') {
            if($row['session_id'] === $sess_id) {
                // Refresh explicitly
                $conn->query("UPDATE seats SET updated_at = NOW() WHERE id='".$row['id']."'");
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Seat is currently being reserved by another user.']);
            }
        }
    } else {
        // Not currently allocated, successfully reserve
        $ins_sql = "INSERT INTO seats (bus_id, seat_number, status, session_id) VALUES ('$bus_id', '$seat_number', 'Reserved', '$sess_id')";
        if($conn->query($ins_sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database constraint failure.']);
        }
    }
} elseif($action == 'release') {
    // Only permit destruction of holds belonging purely to active agent securely
    $del_sql = "DELETE FROM seats WHERE bus_id='$bus_id' AND seat_number='$seat_number' AND session_id='$sess_id' AND status='Reserved'";
    $conn->query($del_sql);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Unknown payload mapping']);
}
?>
