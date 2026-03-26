<?php
session_start();
include("db/connect.php");

if(!isset($_SESSION['user_id'])){
    echo "<script>alert('Please login/signup to view tickets'); window.location='login.php';</script>";
    exit();
}

$booking_id = $_GET['id'] ?? 0;
// Strip leading zeros if they were passed
$booking_id = (int)$booking_id;
$user_id = $_SESSION['user_id'];

// Get booking, bus, passenger and user details
$sql = "SELECT b.*, bu.*, u.phone AS u_phone, u.email AS u_email, u.name as u_name
        FROM bookings b 
        JOIN buses bu ON b.bus_id = bu.bus_id 
        JOIN users u ON b.user_id = u.id
        WHERE b.booking_id = $booking_id AND b.user_id = '$user_id'";

$result = $conn->query($sql);

if(!$result || $result->num_rows == 0){
    die("Invalid ticket or permission denied.");
}

$ticket = $result->fetch_assoc();

// Get all passengers
$pass_sql = "SELECT * FROM passengers WHERE booking_id = $booking_id";
$pass_res = $conn->query($pass_sql);
$passengers = [];
if($pass_res && $pass_res->num_rows > 0){
    while($pr = $pass_res->fetch_assoc()){
        $passengers[] = $pr;
    }
}

// Data processing
$seats_array = array_filter(array_map('trim', explode(',', $ticket['seat_number'])));
$num_seats = count($seats_array);
$total_amount = $num_seats * $ticket['price'];
$discount = isset($ticket['discount_amount']) ? floatval($ticket['discount_amount']) : 0;
$final_amount = isset($ticket['final_price']) && $ticket['final_price'] !== null ? floatval($ticket['final_price']) : $total_amount;
$coupon_code = $ticket['coupon_code'] ?? '';

$dep_time = new DateTime($ticket['departure_time']);
$arr_time = new DateTime($ticket['arrival_time']);
if ($arr_time < $dep_time) {
    $arr_time->modify('+1 day');
}
$interval = $dep_time->diff($arr_time);
$duration = $interval->h . 'h ' . $interval->i . 'm';

$j_date = date('d F Y', strtotime($ticket['journey_date'] ?? date('Y-m-d')));
$bus_type = $ticket['bus_type'] ?? 'A/C Sleeper';
$booking_id_display = "BUS-" . str_pad($ticket['booking_id'], 5, "0", STR_PAD_LEFT);

// QR Code URL (Google Charts API)
$qr_data = "ID: $booking_id_display | Bus: {$ticket['bus_name']} | Date: $j_date | Seats: {$ticket['seat_number']}";
$qr_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket - <?= $booking_id_display ?> | SkylineTransit</title>
    <!-- Use Tailwind for easy markup -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #0f172a; }
        .print-area { max-width: 800px; margin: 40px auto; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden; }
        .dash-line { border-top: 2px dashed #cbd5e1; }
        .section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; }
        .data-value { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
        
        @media print {
            body { background: white; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .print-area { box-shadow: none; border: 1px solid #e2e8f0; margin: 0; max-width: 100%; border-radius: 0; padding: 20px; page-break-inside: avoid; }
            .no-print { display: none !important; }
            .tooth-decor { display: none !important; }
        }
    </style>
</head>
<body onload="setTimeout(function(){ window.print(); }, 500)">
    
    <div class="text-center mt-6 no-print">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition-colors">
            <i class="fa-solid fa-print"></i> Print / Save PDF
        </button>
        <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold shadow-md transition-colors ml-3">
            Close
        </button>
    </div>

    <!-- TICKET START -->
    <div class="print-area relative">
        
        <!-- Header -->
        <div class="bg-slate-900 border-b-4 border-rose-500 text-white p-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black tracking-tight mb-1 flex items-center gap-2"><i class="fa-solid fa-bus text-rose-500"></i> SkylineTransit Passenger Ticket</h1>
                <p class="text-slate-400 font-medium tracking-wide">Your trusted travel partner</p>
            </div>
            <div class="text-right">
                <div class="inline-block bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded text-xs font-black uppercase tracking-widest mb-2"><i class="fa-solid fa-circle-check"></i> Confirmed</div>
                <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest">Booking ID</p>
                <h2 class="text-3xl font-black text-white"><?= $booking_id_display ?></h2>
            </div>
        </div>

        <div class="p-8">
            
            <!-- Journey Info -->
            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 mb-8 flex justify-between items-center">
                <div class="w-2/5">
                    <p class="section-title">Source</p>
                    <p class="text-2xl font-black text-rose-600 truncate"><?= htmlspecialchars($ticket['source']) ?></p>
                    <p class="text-sm font-bold text-slate-600 mt-1"><?= date('h:i A', strtotime($ticket['departure_time'])) ?></p>
                </div>
                <div class="w-1/5 flex flex-col items-center justify-center text-slate-300 relative">
                    <p class="text-[0.65rem] font-bold text-slate-500 uppercase tracking-widest mb-2"><?= $duration ?></p>
                    <div class="w-full border-t-2 border-dashed border-slate-300 relative">
                        <i class="fa-solid fa-bus absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-slate-300 bg-slate-50 px-2"></i>
                    </div>
                </div>
                <div class="w-2/5 text-right">
                    <p class="section-title">Destination</p>
                    <p class="text-2xl font-black text-rose-600 truncate"><?= htmlspecialchars($ticket['destination']) ?></p>
                    <p class="text-sm font-bold text-slate-600 mt-1"><?= date('h:i A', strtotime($ticket['arrival_time'])) ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-x-12 gap-y-8 mb-8">
                
                <!-- Bus Details -->
                <div>
                    <h3 class="text-lg font-black border-b border-slate-200 pb-2 mb-4"><i class="fa-solid fa-van-shuttle mr-2 text-slate-400"></i> Bus Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="section-title">Bus Operator</p>
                            <p class="data-value"><?= htmlspecialchars($ticket['bus_name']) ?></p>
                        </div>
                        <div>
                            <p class="section-title">Bus Type</p>
                            <p class="data-value text-sm"><?= htmlspecialchars($bus_type) ?></p>
                        </div>
                        <div class="col-span-2 mt-1">
                            <p class="section-title">Journey Date</p>
                            <p class="data-value text-rose-600"><?= $j_date ?></p>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div>
                    <h3 class="text-lg font-black border-b border-slate-200 pb-2 mb-4"><i class="fa-solid fa-credit-card mr-2 text-slate-400"></i> Payment Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="section-title">Seat Numbers(s)</p>
                            <p class="data-value break-words"><?= htmlspecialchars($ticket['seat_number']) ?> <br><span class="text-xs text-slate-500 font-normal">(<?= $num_seats ?> Seats)</span></p>
                        </div>
                        <div>
                            <p class="section-title">Base Fare</p>
                            <p class="data-value">₹<?= $ticket['price'] ?> <br><span class="text-xs text-slate-500 font-normal">per seat (Total: ₹<?= $total_amount ?>)</span></p>
                        </div>
                        
                        <?php if($discount > 0): ?>
                        <div class="col-span-2 flex justify-between items-center bg-emerald-50 p-2 rounded-lg border border-emerald-100 mt-1">
                            <div>
                                <p class="section-title text-emerald-600 mb-0 font-black flex items-center gap-1"><i class="fa-solid fa-tag"></i> Coupon Applied</p>
                                <p class="text-xs font-bold text-emerald-800 bg-emerald-100 inline-block px-1.5 py-0.5 rounded mt-0.5"><?= htmlspecialchars($coupon_code) ?></p>
                            </div>
                            <p class="text-lg font-bold text-emerald-600">-₹<?= $discount ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="col-span-2 flex justify-between items-center bg-slate-100 p-3 rounded-lg border border-slate-200 mt-1">
                            <div>
                                <p class="section-title mb-0">Total Paid Amount</p>
                                <p class="text-[0.65rem] text-slate-400 font-medium">Online (Paid)</p>
                            </div>
                            <p class="text-2xl font-black text-emerald-600">₹<?= $final_amount ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passenger Details Table -->
            <div class="mb-8">
                <h3 class="text-lg font-black border-b border-slate-200 pb-2 mb-4"><i class="fa-solid fa-users mr-2 text-slate-400"></i> Passenger Details</h3>
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-xs uppercase tracking-widest">
                            <th class="p-3 font-bold rounded-tl-lg">Passenger Name</th>
                            <th class="p-3 font-bold">Age</th>
                            <th class="p-3 font-bold">Gender</th>
                            <th class="p-3 font-bold">Seat</th>
                            <th class="p-3 font-bold rounded-tr-lg text-right">ID Required</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($passengers) > 0): ?>
                            <?php foreach($passengers as $p): ?>
                            <tr class="border-b border-slate-100">
                                <td class="p-3 font-bold text-slate-800 text-sm"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="p-3 text-slate-600 text-sm"><?= htmlspecialchars($p['age']) ?></td>
                                <td class="p-3 text-slate-600 text-sm"><?= htmlspecialchars($p['gender']) ?></td>
                                <td class="p-3 font-bold text-slate-800 text-sm"><?= htmlspecialchars($p['seat_number'] ?? 'N/A') ?></td>
                                <td class="p-3 text-slate-500 text-xs font-semibold text-right"><i class="fa-solid fa-address-card text-slate-400 mr-1"></i> Valid Original ID</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="border-b border-slate-100">
                                <td class="p-3 font-bold text-slate-800 text-sm"><?= htmlspecialchars($ticket['passenger_name']) ?></td>
                                <td class="p-3 text-slate-400 italic text-sm">N/A</td>
                                <td class="p-3 text-slate-400 italic text-sm">N/A</td>
                                <td class="p-3 font-bold text-slate-800 text-sm"><?= htmlspecialchars($ticket['seat_number']) ?></td>
                                <td class="p-3 text-slate-500 text-xs font-semibold text-right"><i class="fa-solid fa-address-card text-slate-400 mr-1"></i> Valid Original ID</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Contact & Terms -->
            <div class="flex gap-8 mb-4">
                <div class="w-3/4">
                    <h4 class="font-bold text-slate-800 mt-2 mb-1 text-sm"><i class="fa-solid fa-address-book text-slate-400 mr-1"></i> Primary Contact</h4>
                    <p class="text-sm text-slate-600 mb-6 font-medium">
                        <i class="fa-solid fa-phone text-slate-300 w-4 text-center"></i> <?= htmlspecialchars($ticket['primary_phone'] ?? $ticket['u_phone']) ?> &nbsp;&nbsp;|&nbsp;&nbsp; 
                        <i class="fa-solid fa-envelope text-slate-300 w-4 text-center"></i> <?= htmlspecialchars($ticket['primary_email'] ?? $ticket['u_email']) ?>
                    </p>
                    
                    <h4 class="font-bold text-slate-800 mb-2 text-sm"><i class="fa-solid fa-clipboard-list text-slate-400 mr-1"></i> Terms & Conditions</h4>
                    <ul class="list-disc pl-4 text-xs text-slate-500 space-y-1.5 font-medium leading-relaxed">
                        <li>This ticket is non-transferable. Only the passenger named on the ticket can travel.</li>
                        <li>Passengers <span class="text-rose-600 font-bold">must carry a valid original photo ID proof</span> (Aadhar/PAN/DL/Passport).</li>
                        <li>Please reach the boarding point at least 15 minutes prior to the departure time.</li>
                        <li>The bus operator is not responsible for any delay or cancellation due to unavoidable reasons.</li>
                    </ul>
                </div>
                <div class="w-1/4 flex flex-col items-center justify-center border-l-2 border-dashed border-slate-200 pl-8">
                    <img src="<?= $qr_url ?>" alt="Ticket QR Code" class="w-32 h-32 mb-3 p-2 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <p class="text-[0.6rem] font-bold text-slate-400 uppercase tracking-widest text-center">Scan to Verify Ticket</p>
                </div>
            </div>
            
        </div>
        
        <div class="bg-slate-100 p-4 text-center border-t border-slate-200 tooth-decor">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest"><i class="fa-solid fa-mobile-screen-button mr-1"></i> Please show this ticket on your device or carry a printout during boarding.</p>
        </div>
        <!-- Sawtooth decorative bottom -->
        <div class="h-4 w-full tooth-decor absolute bottom-0 left-0" style="background-image: radial-gradient(circle at 10px 0, transparent 0, transparent 10px, #f1f5f9 10px); background-size: 20px 20px; background-position: -10px center; background-repeat: repeat-x; transform: rotate(180deg); margin-top: -1px;"></div>

    </div>

</body>
</html>
