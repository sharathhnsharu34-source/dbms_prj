<?php
session_start();
include("db/connect.php");

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id'])) {
    header("Location: my_tickets.php");
    exit();
}

$booking_id = intval($_POST['booking_id']);
$user_id = $_SESSION['user_id'];

// Get booking details
$sql = "SELECT b.booking_id, b.final_price, b.seat_number, b.booking_status, b.payment_status, bu.journey_date, bu.departure_time, bu.price 
        FROM bookings b 
        JOIN buses bu ON b.bus_id = bu.bus_id 
        WHERE b.booking_id = $booking_id AND b.user_id = '$user_id'";
$result = $conn->query($sql);

if($result->num_rows === 0) {
    die("Invalid booking or permission denied.");
}

$ticket = $result->fetch_assoc();

if($ticket['booking_status'] === 'Cancelled') {
    die("Ticket is already cancelled.");
}
if($ticket['payment_status'] !== 'Success') {
    die("Only successful bookings can be cancelled.");
}

// Calculate refund
$departure_str = $ticket['journey_date'] . ' ' . $ticket['departure_time'];
$departure_time = strtotime($departure_str);
if (!$departure_time) {
    // Fallback if date parsing fails
    $departure_time = strtotime($ticket['journey_date']);
}
$current_time = time();

$hours_left = ($departure_time - $current_time) / 3600;

if($hours_left <= 0) {
    die("Journey has already started or passed. Cancellation not allowed.");
}

// Fallback to base price if final_price is null (for older bookings before coupon system)
$num_seats = count(array_filter(explode(',', $ticket['seat_number'])));
$final_price = isset($ticket['final_price']) && $ticket['final_price'] !== null ? floatval($ticket['final_price']) : ($num_seats * $ticket['price']);

$refund_percent = 0;
if ($hours_left >= 24) {
    $refund_percent = 90;
} elseif ($hours_left >= 12) {
    $refund_percent = 80;
} elseif ($hours_left >= 6) {
    $refund_percent = 50;
} else {
    $refund_percent = 0;
}

$refund_amount = ($final_price * $refund_percent) / 100;

// Update Database
$update_sql = "UPDATE bookings 
               SET booking_status = 'Cancelled', 
                   refund_amount = $refund_amount, 
                   cancelled_at = NOW() 
               WHERE booking_id = $booking_id";

if($conn->query($update_sql)) {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Ticket Cancelled</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <meta http-equiv="refresh" content="6;url=my_tickets.php">
        <style>
            body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
            .pop-in { animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
            @keyframes popIn { 0% { opacity: 0; transform: scale(0.8); } 100% { opacity: 1; transform: scale(1); } }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden p-8 text-center pop-in border border-slate-100">
            <div class="w-20 h-20 bg-emerald-100 rounded-full mx-auto flex items-center justify-center mb-5 border-4 border-white shadow-sm">
                <i class="fa-solid fa-check text-4xl text-emerald-500"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Ticket Cancelled Successfully</h2>
            <p class="text-slate-500 font-medium mb-6">Your booking has been cancelled and the refund process has been initiated.</p>
            
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Refund Amount</p>
                <p class="text-3xl font-black text-emerald-600">₹<?= number_format($refund_amount, 2) ?></p>
                <p class="text-[0.65rem] text-slate-500 font-medium mt-1">Based on <?= $refund_percent ?>% policy for <?= round($hours_left, 1) ?> hrs remaining</p>
            </div>
            
            <a href="my_tickets.php" class="inline-flex w-full justify-center items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-md">
                <i class="fa-solid fa-arrow-left mt-0.5"></i> Back to My Tickets
            </a>
            <p class="text-[0.65rem] text-slate-400 mt-4 font-semibold">Redirecting automatically in 5 seconds...</p>
        </div>
    </body>
    </html>
<?php
} else {
    echo "Error processing cancellation: " . $conn->error;
}
?>
