<?php
// API strictly serving live availability data synchronously
session_start();
header('Content-Type: application/json');
include("../db/connect.php");

$bus_ids = isset($_POST['bus_ids']) ? json_decode($_POST['bus_ids'], true) : [];

if(empty($bus_ids)) {
    echo json_encode(['error' => 'No IDs supplied']);
    exit;
}

$results = [];

foreach($bus_ids as $b_id) {
    if(!is_numeric($b_id)) continue;
    
    // Exact booked count explicitly from `bookings` avoiding overlaps
    $seat_sql = "SELECT seat_number FROM bookings WHERE bus_id='$b_id' AND booking_status != 'Cancelled' AND payment_status != 'Failed'";
    $seat_res = $conn->query($seat_sql);
    $booked_count = 0;
    if($seat_res && $seat_res->num_rows > 0) {
        while($srow = $seat_res->fetch_assoc()) {
            $count_arr = explode(',', $srow['seat_number']);
            foreach($count_arr as $s) {
                if(trim($s) !== '') $booked_count++;
            }
        }
    }
    
    // Dynamic reserved holds natively checked within 5 minute lifespan constraint
    $hold_sql = "SELECT COUNT(*) as held FROM seats WHERE bus_id='$b_id' AND status='Reserved' AND updated_at >= NOW() - INTERVAL 5 MINUTE";
    $hold_res = $conn->query($hold_sql);
    $held_count = ($hold_res && $hold_res->num_rows > 0) ? $hold_res->fetch_assoc()['held'] : 0;
    
    $total_capacity = 32; 
    $seats_left = max(0, $total_capacity - $booked_count - $held_count);
    
    $results[$b_id] = $seats_left;
}

echo json_encode(['success' => true, 'data' => $results]);
?>
