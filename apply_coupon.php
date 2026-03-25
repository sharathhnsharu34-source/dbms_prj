<?php
session_start();
include("db/connect.php");
header('Content-Type: application/json');

if (!isset($_POST['coupon_code']) || !isset($_POST['amount'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data.']);
    exit();
}

$code = strtoupper(trim($conn->real_escape_string($_POST['coupon_code'])));
$amount = floatval($_POST['amount']);
$user_id = $_SESSION['user_id'] ?? 0;

if ($code === '') {
    echo json_encode(['success' => true, 'discount' => 0, 'final_price' => $amount, 'message' => 'Coupon cleared.']);
    exit();
}

// Fetch coupon
$result = $conn->query("SELECT * FROM coupons WHERE code = '$code' AND is_active = 1");
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon!']);
    exit();
}

$coupon = $result->fetch_assoc();

// Check validations
if ($amount < $coupon['min_amount']) {
    echo json_encode(['success' => false, 'message' => 'Minimum order amount must be ₹' . $coupon['min_amount']]);
    exit();
}

if ($coupon['valid_days'] !== null) {
    $current_day = date('l');
    $valid_days = array_map('trim', explode(',', $coupon['valid_days']));
    if (!in_array($current_day, $valid_days)) {
        echo json_encode(['success' => false, 'message' => 'This coupon is only valid on: ' . str_replace(',', ', ', $coupon['valid_days'])]);
        exit();
    }
}

if ($coupon['valid_time_from'] !== null) {
    $current_time = date('H:i:s');
    if ($current_time < $coupon['valid_time_from']) {
        echo json_encode(['success' => false, 'message' => 'This coupon is only valid after ' . date('h:i A', strtotime($coupon['valid_time_from']))]);
        exit();
    }
}

if ($coupon['new_user_only'] == 1) {
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Please login to verify new user status!']);
        exit();
    }
    // Check if user has past successful bookings
    $chk = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id AND payment_status = 'Success'");
    $past_bookings = $chk->fetch_assoc()['count'];
    if ($past_bookings > 0) {
        echo json_encode(['success' => false, 'message' => 'This coupon is valid for first-time bookings only!']);
        exit();
    }
}

// Calculate discount
$discount = 0;
if ($coupon['discount_type'] === 'flat') {
    $discount = floatval($coupon['discount_value']);
} else {
    $discount = ($amount * floatval($coupon['discount_value'])) / 100;
}

if ($coupon['max_discount'] > 0 && $discount > $coupon['max_discount']) {
    $discount = floatval($coupon['max_discount']);
}

// Ensure discount doesn't exceed amount
if ($discount > $amount) {
    $discount = $amount;
}

$final_price = $amount - $discount;

echo json_encode([
    'success' => true,
    'discount' => round($discount, 2),
    'final_price' => round($final_price, 2),
    'code' => $code,
    'message' => 'Coupon applied successfully!'
]);
?>
