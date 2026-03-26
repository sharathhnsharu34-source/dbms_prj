<?php
// We are already inside confirm_booking.php context, so $_POST is available
if(!isset($_POST['bus_id'])) {
    header("Location: index.php");
    exit();
}

$b_name = isset($_POST['bname']) ? $_POST['bname'] : 'Luxury Bus';
$src = isset($_POST['src']) ? $_POST['src'] : 'Source';
$dest = isset($_POST['dest']) ? $_POST['dest'] : 'Destination';
$price = isset($_POST['price']) ? $_POST['price'] : 0;
$dep = isset($_POST['dep']) ? $_POST['dep'] : '00:00';
$arr = isset($_POST['arr']) ? $_POST['arr'] : '00:00';
$type = isset($_POST['type']) ? $_POST['type'] : 'A/C';

$passenger_name = $_POST['passenger_name'];
$seats_str = $_POST['seat_number'];
$seats_array = array_filter(array_map('trim', explode(',', $seats_str)));
$num_seats = count($seats_array);
$total_amount = $num_seats * $price;
$discount = isset($_POST['vd_discount']) ? floatval($_POST['vd_discount']) : 0;
$final_amount = isset($_POST['vd_final']) ? floatval($_POST['vd_final']) : $total_amount;
$coupon_code = isset($_POST['vd_code']) ? $_POST['vd_code'] : '';

$booking_id = isset($_POST['db_booking_id']) ? "BUS-" . str_pad($_POST['db_booking_id'], 5, "0", STR_PAD_LEFT) : "BUS-" . mt_rand(100000, 999999);
$journey_date = date('d M Y'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Ticket Booking | SkylineTransit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        
        /* Animations */
        .fade-in-up { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(30px); }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        
        .success-check {
            animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            transform: scale(0);
        }
        @keyframes scaleIn { to { transform: scale(1); } }
        
        /* Ticket styling */
        .ticket-cutout {
            position: relative;
        }
        .ticket-cutout::before, .ticket-cutout::after {
            content: '';
            position: absolute;
            top: -12px;
            width: 24px;
            height: 24px;
            background-color: #f8fafc;
            border-radius: 50%;
            z-index: 10;
        }
        .ticket-cutout::before { left: -12px; }
        .ticket-cutout::after { right: -12px; }
        
        .dashed-line {
            border-top: 2px dashed #cbd5e1;
            position: relative;
            margin: 0 -32px;
        }

        /* Print Specifics */
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .ticket-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; transform: none !important; animation: none !important; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-xl w-full mx-auto relative z-10">
        
        <!-- Header Success Message -->
        <div class="text-center mb-8 fade-in-up">
            <div class="w-20 h-20 bg-emerald-100 rounded-full mx-auto flex items-center justify-center mb-4 success-check border-4 border-white shadow-lg">
                <i class="fa-solid fa-check text-4xl text-emerald-500"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-800 mb-2">🎉 Booking Confirmed!</h1>
            <p class="text-slate-500 font-medium tracking-wide">Your e-ticket has been successfully generated.</p>
        </div>

        <!-- The Ticket Component -->
        <div class="ticket-card bg-white rounded-3xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] overflow-hidden fade-in-up delay-100" id="ticket-container">
            
            <!-- Ticket Top half -->
            <div class="p-8 pb-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white relative">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-bl-full -mr-8 -mt-8"></div>
                
                <div class="flex justify-between items-start mb-8 relative z-10">
                    <div>
                        <p class="text-emerald-400 text-[0.65rem] font-black tracking-widest uppercase mb-1.5 flex items-center gap-1.5"><i class="fa-solid fa-ticket"></i> E-Ticket</p>
                        <h2 class="text-2xl font-black tracking-tight"><?php echo htmlspecialchars($b_name); ?></h2>
                        <p class="text-slate-400 text-xs font-semibold mt-1 tracking-wide uppercase"><?php echo htmlspecialchars($type); ?></p>
                    </div>
                    <div class="text-right bg-white/10 px-4 py-2 rounded-xl backdrop-blur-sm border border-white/10">
                        <p class="text-slate-400 text-[0.65rem] font-bold uppercase tracking-widest mb-0.5">Booking ID</p>
                        <p class="text-base font-black font-mono tracking-wider"><?php echo $booking_id; ?></p>
                    </div>
                </div>

                <div class="flex justify-between items-center relative z-10 pt-5 border-t border-white/10">
                    <div class="w-2/5">
                        <p class="text-3xl font-black tracking-tighter"><?php echo htmlspecialchars($dep); ?></p>
                        <p class="text-sm font-bold text-slate-300 mt-1 uppercase tracking-wider truncate"><?php echo htmlspecialchars($src); ?></p>
                    </div>
                    
                    <div class="w-1/5 flex flex-col items-center justify-center">
                        <i class="fa-solid fa-bus text-slate-400 mb-2 text-lg"></i>
                        <div class="w-full h-px bg-slate-500 relative">
                            <div class="absolute right-0 top-1/2 mt-[0.5px] transform -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                            <div class="absolute left-0 top-1/2 mt-[0.5px] transform -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                        </div>
                    </div>

                    <div class="w-2/5 text-right">
                        <p class="text-3xl font-black tracking-tighter"><?php echo htmlspecialchars($arr); ?></p>
                        <p class="text-sm font-bold text-slate-300 mt-1 uppercase tracking-wider truncate"><?php echo htmlspecialchars($dest); ?></p>
                    </div>
                </div>
            </div>

            <!-- Divisor -->
            <div class="ticket-cutout">
                <div class="dashed-line"></div>
            </div>

            <!-- Ticket Bottom half -->
            <div class="p-8 pt-7 bg-white">
                <div class="grid grid-cols-2 gap-y-7 gap-x-4 mb-5">
                    <div>
                        <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Passenger</p>
                        <p class="font-bold text-slate-800 text-lg tracking-tight truncate"><?php echo htmlspecialchars($passenger_name); ?></p>
                    </div>
                    <div>
                        <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Date of Journey</p>
                        <p class="font-bold text-slate-800 text-lg tracking-tight"><?php echo $journey_date; ?></p>
                    </div>
                    
                    <div>
                        <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Selected Seat(s)</p>
                        <p class="font-black text-emerald-600 text-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-couch text-sm opacity-80"></i> <?php echo htmlspecialchars($seats_str); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Total Passengers</p>
                        <p class="font-bold text-slate-800 text-lg tracking-tight"><?php echo $num_seats; ?> Person(s)</p>
                    </div>
                </div>

                <!-- Primary Contact Info -->
                <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 mb-7">
                    <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-2 border-b border-slate-100 pb-1.5"><i class="fa-solid fa-address-book mr-1.5 text-slate-300"></i>Primary Contact</p>
                    <div class="flex justify-between items-center text-sm">
                        <div class="w-1/2 pr-2">
                            <p class="text-xs text-slate-500 mb-0.5">Email ID</p>
                            <p class="font-semibold text-slate-700 truncate"><?php echo isset($_POST['primary_email']) && !empty($_POST['primary_email']) ? htmlspecialchars($_POST['primary_email']) : 'N/A'; ?></p>
                        </div>
                        <div class="w-1/2 pl-2 border-l border-slate-200">
                            <p class="text-xs text-slate-500 mb-0.5">Mobile No</p>
                            <p class="font-semibold text-slate-700"><?php echo isset($_POST['primary_phone']) && !empty($_POST['primary_phone']) ? htmlspecialchars($_POST['primary_phone']) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 shadow-inner">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Base Fare</p>
                            <p class="text-slate-500 text-sm font-semibold"><span class="font-bold text-slate-700"><?php echo $num_seats; ?></span> × ₹<?php echo $price; ?></p>
                        </div>
                        <p class="text-lg font-bold text-slate-700">₹<?php echo $total_amount; ?></p>
                    </div>
                    <?php if($discount > 0): ?>
                    <div class="flex justify-between items-center mb-4 pb-4 border-b border-slate-200 border-dashed">
                        <div>
                            <p class="text-[0.65rem] text-emerald-500 font-bold uppercase tracking-widest mb-1 flex items-center gap-1"><i class="fa-solid fa-tag"></i> Coupon Applied</p>
                            <p class="text-emerald-600 text-sm font-bold bg-emerald-100/50 inline-block px-2 py-0.5 rounded"><?php echo htmlspecialchars($coupon_code); ?></p>
                        </div>
                        <p class="text-lg font-bold text-emerald-600">-₹<?php echo $discount; ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between items-end <?php echo $discount == 0 ? 'border-t border-slate-200 border-dashed pt-4' : ''; ?>">
                        <div>
                            <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Final Paid Amount</p>
                            <p class="text-[0.6rem] text-emerald-500 font-bold uppercase tracking-widest flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</p>
                        </div>
                        <p class="text-4xl font-black text-rose-500 tracking-tighter">₹<?php echo $final_amount; ?></p>
                    </div>
                </div>
                
                <div class="text-center mt-6">
                    <p class="text-xs text-slate-400 font-medium">Thank you for booking with SkylineTransit!</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 fade-in-up delay-200 no-print">
            <button onclick="window.print()" class="flex-1 bg-white border border-slate-200 text-slate-700 font-bold py-3.5 px-6 rounded-xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 border-2 border-dashed">
                <i class="fa-solid fa-print"></i> Print Ticket
            </button>
            <a href="index.php" class="flex-1 bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold py-3.5 px-6 rounded-xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-house"></i> Go to Home
            </a>
            <button onclick="downloadTicket()" class="flex-1 bg-gradient-to-r from-slate-900 to-slate-800 text-white font-bold py-3.5 px-6 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </button>
        </div>

        <!-- Auto Redirect Indicator -->
        <div class="mt-8 text-center fade-in-up delay-200 no-print">
            <div class="inline-flex items-center gap-3 bg-white px-5 py-2.5 rounded-full shadow-sm border border-slate-100">
                <i class="fa-solid fa-circle-notch fa-spin text-red-500"></i>
                <p class="text-sm font-semibold text-slate-600">
                    Redirecting to homepage in <span id="countdown" class="font-black text-slate-900 mx-1 w-3 inline-block">5</span> seconds...
                </p>
            </div>
        </div>

    </div>

    <!-- Interactive Script -->
    <script>
        // Redirect Logic
        let timeLeft = 5;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            timeLeft--;
            countdownEl.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
                window.location.href = 'index.php';
            }
        }, 1000);

        // Simple Download Logic (Frontend mapping to Print)
        function downloadTicket() {
            // Using print dialog as lightweight save-as-pdf option to avoid heavy dependencies 
            // per constraint of keeping simple JS
            window.print();
        }
    </script>
</body>
</html>
