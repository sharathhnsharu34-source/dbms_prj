<?php
session_start();
include("db/connect.php");

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check GET or POST for booking_id
if(!isset($_REQUEST['booking_id'])) {
    header("Location: my_tickets.php");
    exit();
}
$booking_id = intval($_REQUEST['booking_id']);

// Fetch Booking details
$sql = "SELECT b.*, bu.bus_name, bu.bus_type, bu.source, bu.destination, bu.departure_time, bu.arrival_time, bu.journey_date, bu.price 
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

// Calculate refund based on exactly 80% rule as requested
$num_seats = count(array_filter(explode(',', $ticket['seat_number'])));
$final_price = isset($ticket['final_price']) && $ticket['final_price'] !== null ? floatval($ticket['final_price']) : ($num_seats * $ticket['price']);

$refund_percent = 80;
$deduction_percent = 20;

$refund_amount = ($final_price * $refund_percent) / 100;
$deduction_amount = ($final_price * $deduction_percent) / 100;

// Handle POST request (Confirm Cancellation)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update_sql = "UPDATE bookings 
                   SET booking_status = 'Cancelled', 
                       refund_amount = $refund_amount, 
                       cancelled_at = NOW() 
                   WHERE booking_id = $booking_id";
    if($conn->query($update_sql)) {
        header("Location: my_tickets.php");
        exit();
    } else {
        $error = "Cancellation failed: " . $conn->error;
    }
}

// Fetch passenger details
$pass_sql = "SELECT name, age, gender, phone, email, seat_number FROM passengers WHERE booking_id = $booking_id";
$pass_res = $conn->query($pass_sql);
$passengers = [];
if($pass_res && $pass_res->num_rows > 0) {
    while($p = $pass_res->fetch_assoc()) {
        $passengers[] = $p;
    }
} else {
    // Fallback if no passengers in passenger table
    // Construct single passenger from booking table
    $passengers[] = [
        'name' => $ticket['passenger_name'],
        'age' => 'N/A',
        'gender' => 'N/A',
        'phone' => $ticket['primary_phone'] ?? 'N/A',
        'email' => $ticket['primary_email'] ?? 'N/A',
        'seat_number' => $ticket['seat_number']
    ];
}

// Format duration and dates
$dep_time = new DateTime($ticket['departure_time']);
$arr_time = new DateTime($ticket['arrival_time']);
if ($arr_time < $dep_time) {
    $arr_time->modify('+1 day');
}
$interval = $dep_time->diff($arr_time);
$duration = $interval->h . 'h ' . $interval->i . 'm';
$j_date = date('d M Y', strtotime($ticket['journey_date']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bus Ticket Booking | SkylineTransit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="pb-20">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer transition transform hover:-translate-y-0.5" onclick="window.location.href='index.php'">
                    <i class="fa-solid fa-bus text-2xl text-red-500"></i>
                    <span class="font-bold text-xl tracking-wide text-gray-900">Skyline<span class="text-red-500">Transit</span></span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="my_tickets.php" class="text-gray-500 hover:text-slate-900 transition-colors font-semibold flex items-center gap-2 text-sm uppercase tracking-wide">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 fade-in">
        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-200">
            <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center text-rose-500 text-xl shadow-sm">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Cancel Ticket</h1>
                <p class="text-sm text-slate-500 font-medium">Review cancellation details and refund policy</p>
            </div>
        </div>

        <?php if(isset($error)): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-200">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Refund Warning Section -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8 flex items-start gap-4">
            <div class="bg-amber-100 text-amber-600 p-3 rounded-full flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-amber-800 tracking-tight mb-1">⚠️ Cancellation Policy</h3>
                <p class="text-amber-700 text-sm mb-1 font-medium">Only <span class="font-extrabold text-amber-900">80% refund</span> will be provided.</p>
                <p class="text-amber-700 text-sm font-medium"><span class="font-extrabold text-amber-900">20%</span> will be deducted as cancellation charges.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Info column -->
            <div class="md:col-span-2 space-y-6">
                <!-- Bus Details -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-slate-50 flex items-center gap-2">
                        <i class="fa-solid fa-bus text-slate-400"></i>
                        <h2 class="font-bold text-slate-700 uppercase tracking-wider text-sm">Bus Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($ticket['bus_name']); ?></h3>
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1"><?php echo htmlspecialchars($ticket['bus_type']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-800"><?php echo $j_date; ?></p>
                                <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Journey Date</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="w-2/5">
                                <p class="text-2xl font-black text-slate-800"><?php echo date('h:i A', strtotime($ticket['departure_time'])); ?></p>
                                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mt-1"><?php echo htmlspecialchars($ticket['source']); ?></p>
                            </div>
                            <div class="flex-1 flex flex-col items-center justify-center text-slate-300">
                                <span class="text-xs font-bold text-slate-400 mb-1"><?php echo $duration; ?></span>
                                <div class="w-full relative flex items-center justify-center">
                                    <div class="w-full h-px bg-slate-200"></div>
                                    <i class="fa-solid fa-arrow-right absolute bg-white px-2"></i>
                                </div>
                            </div>
                            <div class="w-2/5 text-right">
                                <p class="text-2xl font-black text-slate-800"><?php echo date('h:i A', strtotime($ticket['arrival_time'])); ?></p>
                                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mt-1"><?php echo htmlspecialchars($ticket['destination']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Passenger Details -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-slate-50 flex items-center gap-2">
                        <i class="fa-solid fa-users text-slate-400"></i>
                        <h2 class="font-bold text-slate-700 uppercase tracking-wider text-sm">Passenger Details</h2>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-white text-[0.65rem] uppercase tracking-widest text-slate-400 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-bold">Full Name</th>
                                    <th class="px-6 py-3 font-bold">Age/Gender</th>
                                    <th class="px-6 py-3 font-bold">Seat</th>
                                    <th class="px-6 py-3 font-bold text-right">Contact</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach($passengers as $p): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-800"><?php echo htmlspecialchars($p['name'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($p['age'] ?? 'N/A') . ' / ' . htmlspecialchars($p['gender'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 font-bold text-emerald-600"><?php echo htmlspecialchars($p['seat_number'] ?? $ticket['seat_number']); ?></td>
                                    <td class="px-6 py-4 text-right text-xs">
                                        <?php if(!empty($p['phone']) && $p['phone'] !== 'N/A') echo '<div class="mb-1"><i class="fa-solid fa-phone mr-1 text-slate-400"></i>'.htmlspecialchars($p['phone']).'</div>'; ?>
                                        <?php if(!empty($p['email']) && $p['email'] !== 'N/A') echo '<div><i class="fa-solid fa-envelope mr-1 text-slate-400"></i>'.htmlspecialchars($p['email']).'</div>'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Payment/Action column -->
            <div class="space-y-6">
                <!-- Payment Details -->
                <div class="bg-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden text-white border border-slate-700">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                    <h2 class="font-bold text-slate-300 uppercase tracking-wider text-sm mb-6 flex items-center gap-2"><i class="fa-solid fa-receipt"></i> Payment Summary</h2>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center py-1 border-b border-white/10">
                            <span class="text-slate-400 font-medium">Booking ID</span>
                            <span class="font-black text-white tracking-widest">BUS-<?php echo str_pad($ticket['booking_id'], 5, "0", STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-white/10">
                            <span class="text-slate-400 font-medium">Total Paid</span>
                            <span class="font-bold text-white">₹<?php echo number_format($final_price, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-white/10">
                            <span class="text-rose-400 font-medium">Deduction (20%)</span>
                            <span class="font-bold text-rose-500">- ₹<?php echo number_format($deduction_amount, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-emerald-400 font-bold text-base uppercase tracking-wider">Refund Amount</span>
                            <span class="font-black text-emerald-400 text-2xl">₹<?php echo number_format($refund_amount, 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <form action="cancel_ticket.php" method="POST" class="space-y-3">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <button type="submit" class="w-full py-4 rounded-xl text-white font-black text-sm tracking-widest transition-all shadow-md hover:shadow-xl hover:-translate-y-0.5 bg-gradient-to-r from-red-500 to-rose-600 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-xmark"></i> CONFIRM CANCELLATION
                    </button>
                    <a href="my_tickets.php" class="w-full py-4 rounded-xl text-slate-600 font-black text-sm tracking-widest transition-all bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-800 text-center block shadow-sm hover:shadow">
                        GO BACK
                    </a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
