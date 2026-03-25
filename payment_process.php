<?php
session_start();
include("db/connect.php");

if(!isset($_POST['booking_id'])) {
    header("Location: index.php");
    exit();
}

$booking_id = intval($_POST['booking_id']);
$amount = isset($_POST['price']) && isset($_POST['seat_number']) ? $_POST['price'] * count(array_filter(explode(',', $_POST['seat_number']))) : 0;

$applied_coupon = isset($_POST['applied_coupon']) ? strtoupper(trim($conn->real_escape_string($_POST['applied_coupon']))) : '';
$discount = 0;
$final_price = $amount;
$coupon_code_db = 'NULL';

if ($applied_coupon !== '') {
    $res = $conn->query("SELECT * FROM coupons WHERE code = '$applied_coupon' AND is_active = 1");
    if ($res->num_rows > 0) {
        $coupon = $res->fetch_assoc();
        // Server side minimum validation
        $valid = true;
        if ($amount < $coupon['min_amount']) $valid = false;
        
        if ($valid) {
            if ($coupon['discount_type'] === 'flat') {
                $discount = floatval($coupon['discount_value']);
            } else {
                $discount = ($amount * floatval($coupon['discount_value'])) / 100;
            }
            if ($coupon['max_discount'] > 0 && $discount > $coupon['max_discount']) {
                $discount = floatval($coupon['max_discount']);
            }
            if ($discount > $amount) $discount = $amount;
            
            $final_price = $amount - $discount;
            $coupon_code_db = "'$applied_coupon'";
        }
    }
}

// Simulate 80% Success, 20% Fail probability
$rand = mt_rand(1, 100);
$success = ($rand <= 80);

if ($success) {
    // Update DB securely
    $conn->query("UPDATE bookings SET payment_status = 'Success', coupon_code = $coupon_code_db, discount_amount = $discount, final_price = $final_price WHERE booking_id = $booking_id");
    
    // Output auto-submit form to confirmation_ticket.php
    echo "<form id='f' action='confirmation_ticket.php' method='POST'>";
    foreach($_POST as $key => $val) {
        if(is_array($val)){
            foreach($val as $v) echo "<input type='hidden' name='{$key}[]' value='".htmlspecialchars($v, ENT_QUOTES)."'>";
        } else {
            echo "<input type='hidden' name='$key' value='".htmlspecialchars($val, ENT_QUOTES)."'>";
        }
    }
    // Inject the real DB booking_id explicitly
    echo "<input type='hidden' name='db_booking_id' value='$booking_id'>"; 
    // Inject the verified price data for the ticket representation
    echo "<input type='hidden' name='vd_discount' value='$discount'>";
    echo "<input type='hidden' name='vd_final' value='$final_price'>";
    echo "<input type='hidden' name='vd_code' value='$applied_coupon'>";
    echo "</form><script>document.getElementById('f').submit();</script>";
} else {
    // Update DB
    $conn->query("UPDATE bookings SET payment_status = 'Failed' WHERE booking_id = $booking_id");
    
    // Output auto-submit form to payment_failed.php
    echo "<form id='f' action='payment_failed.php' method='POST'>";
    foreach($_POST as $key => $val) {
        if(is_array($val)){
            foreach($val as $v) echo "<input type='hidden' name='{$key}[]' value='".htmlspecialchars($v, ENT_QUOTES)."'>";
        } else {
            echo "<input type='hidden' name='$key' value='".htmlspecialchars($val, ENT_QUOTES)."'>";
        }
    }
    echo "</form><script>document.getElementById('f').submit();</script>";
}
?>
