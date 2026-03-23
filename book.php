<?php
session_start();
include("db/connect.php");

$bus_id = $_GET['bus_id'];

// Default visual parameters directly from URL or fallbacks
$src = isset($_GET['src']) ? $_GET['src'] : 'Source';
$dest = isset($_GET['dest']) ? $_GET['dest'] : 'Destination';
$price = isset($_GET['price']) ? $_GET['price'] : 0;
$bname = isset($_GET['bname']) ? $_GET['bname'] : 'Luxury Bus';
$type = isset($_GET['type']) ? $_GET['type'] : 'A/C Sleeper';
$dep = isset($_GET['dep']) ? $_GET['dep'] : '00:00';
$arr = isset($_GET['arr']) ? $_GET['arr'] : '00:00';

$bookedSeats = [];

$sql = "SELECT seat_number FROM bookings WHERE bus_id='$bus_id'";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    // Handle cases where multiple seats were recorded as comma separated string
    $seats = explode(',', $row['seat_number']);
    foreach($seats as $s){
        if(trim($s) !== '') {
            $bookedSeats[] = trim($s);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Selection | Premium Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f5f7fa; color: #1e293b; }
        .fade-in { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Bus Layout Grid */
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            padding: 20px 0;
        }
        
        /* Aisle (2+2 layout): 3rd column is space */
        .seat:nth-child(5n-2) {
            margin-right: 48px; 
        }
        
        .seat {
            width: 52px;
            height: 56px;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            border-radius: 10px 10px 6px 6px; 
            position: relative;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            color: #64748b;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            user-select: none;
        }
        
        /* Seat cushion visual */
        .seat::before {
            content: '';
            position: absolute;
            bottom: 4px;
            width: 75%;
            height: 5px;
            background: #cbd5e1;
            border-radius: 6px;
            transition: all 0.25s;
        }

        .seat:hover:not(.booked) {
            border-color: #94a3b8;
            color: #334155;
            transform: translateY(-4px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.06);
        }
        .seat:hover:not(.booked)::before {
            background: #94a3b8;
        }

        .seat.selected {
            background: #10b981; 
            border-color: #059669;
            color: #ffffff;
            transform: scale(1.08) translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
        .seat.selected::before {
            background: #047857;
        }

        .seat.booked {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #cbd5e1;
            cursor: not-allowed;
            background-image: repeating-linear-gradient(45deg, transparent, transparent 4px, rgba(0,0,0,0.02) 4px, rgba(0,0,0,0.02) 8px);
            box-shadow: none;
            opacity: 0.8;
        }
        
        /* Dashboard/Steering visual */
        .steering-wheel {
            width: 44px;
            height: 44px;
            border: 6px solid #94a3b8;
            border-radius: 50%;
            position: relative;
            margin-left: auto;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
        .steering-wheel::after {
            content: '';
            position: absolute;
            top: 50%; left: -6px; right: -6px;
            height: 6px; background: #94a3b8;
            transform: translateY(-50%);
        }
        .steering-wheel::before {
            content: '';
            position: absolute;
            top: 0; left: 50%; bottom: 50%;
            width: 6px; background: #94a3b8;
            transform: translateX(-50%);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.08);
        }
        
        .pulse-once {
            animation: pulseOnce 0.4s ease-out;
        }
        @keyframes pulseOnce { 0% { transform: scale(1); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }
    </style>
</head>
<body class="pb-24">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer transition transform hover:-translate-y-0.5" onclick="window.history.back()">
                    <i class="fa-solid fa-bus text-2xl text-red-500"></i>
                    <span class="font-bold text-xl tracking-wide text-gray-900">Bus<span class="text-red-500">Book</span></span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="javascript:history.back()" class="text-gray-500 hover:text-slate-900 transition-colors font-semibold flex items-center gap-2 text-sm uppercase tracking-wide hidden md:flex">
                        <i class="fa-solid fa-arrow-left"></i> Back to Buses
                    </a>
                    
                    <div class="w-px h-6 bg-gray-200 hidden md:block"></div>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="my_tickets.php" class="text-gray-600 hover:text-red-500 font-medium transition flex items-center gap-2">
                            <i class="fa-solid fa-ticket text-red-500"></i> <span class="hidden sm:inline">My Tickets</span>
                        </a>
                        <div class="relative group cursor-pointer z-50">
                            <span class="font-bold text-red-500 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600"><i class="fa-solid fa-user text-sm"></i></span>
                                <span class="hidden sm:inline"><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></span>
                            </span>
                            <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl py-2 hidden group-hover:block border border-gray-100">
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition font-medium"><i class="fa-solid fa-right-from-bracket mr-2"></i> Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-600 hover:text-red-500 font-medium transition">Login</a>
                        <a href="signup.php" class="bg-gradient-to-r from-red-500 to-rose-600 text-white px-5 py-2 rounded-xl transition-all shadow-md transform hover:-translate-y-0.5 text-sm font-semibold">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 fade-in">
        
        <!-- Header Info Banner -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-[2rem] p-8 md:p-10 text-white mb-10 shadow-2xl relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-8 border border-slate-700/50">
            <div class="absolute -right-10 -top-10 opacity-5 transform rotate-12">
                <i class="fa-solid fa-van-shuttle text-[12rem]"></i>
            </div>
            
            <div class="flex-1 z-10 w-full">
                <div class="inline-block bg-white/10 px-3.5 py-1.5 rounded-lg text-xs font-bold tracking-widest backdrop-blur-md mb-5 uppercase border border-white/10 text-emerald-300">
                    <?php echo htmlspecialchars($type); ?>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-5 tracking-tight"><?php echo htmlspecialchars($bname); ?></h1>
                
                <div class="flex items-center gap-6 text-slate-300 bg-white/5 p-4 rounded-2xl w-max border border-white/5">
                    <div class="flex flex-col">
                        <span class="font-bold text-xl text-white"><?php echo htmlspecialchars($dep); ?></span>
                        <span class="text-sm font-medium tracking-wide uppercase mt-0.5"><?php echo htmlspecialchars($src); ?></span>
                    </div>
                    <div class="flex flex-col items-center justify-center px-4">
                        <p class="text-[0.65rem] text-slate-400 mb-1 tracking-widest uppercase">06h 30m</p>
                        <i class="fa-solid fa-arrow-right-long text-red-400 text-lg"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-xl text-white"><?php echo htmlspecialchars($arr); ?></span>
                        <span class="text-sm font-medium tracking-wide uppercase mt-0.5"><?php echo htmlspecialchars($dest); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-10">
            
            <!-- Left: Seat Layout Container -->
            <div class="w-full lg:w-7/12">
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 md:p-10 relative">
                    
                    <h2 class="text-xl font-bold text-slate-800 mb-8 border-b border-gray-100 pb-4 text-center">Select Your Seats</h2>

                    <!-- Seat Legends -->
                    <div class="flex flex-wrap justify-center gap-6 mb-12 bg-slate-50 py-4 px-6 rounded-2xl border border-slate-100 max-w-md mx-auto">
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 bg-white border-2 border-slate-300 rounded shadow-sm"></div>
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Available</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 bg-emerald-500 border border-emerald-600 rounded shadow-sm"></div>
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Selected</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 bg-slate-100 border border-slate-200 rounded" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 3px, rgba(0,0,0,0.05) 3px, rgba(0,0,0,0.05) 6px);"></div>
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Booked</span>
                        </div>
                    </div>

                    <!-- Bus Body Styling -->
                    <div class="bg-slate-50 border-[3px] border-slate-200 rounded-[3.5rem] p-8 md:p-12 max-w-[420px] mx-auto relative shadow-inner">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 bg-slate-200 px-8 py-1.5 rounded-full text-xs font-black text-slate-500 border-[3px] border-white shadow-sm tracking-widest">
                            FRONT
                        </div>
                        
                        <!-- Driver Section -->
                        <div class="flex justify-between items-center mb-10 pb-8 border-b-[3px] border-slate-200 border-dashed">
                            <div class="w-14 h-14 bg-white rounded-xl shadow-sm border border-slate-100 flex flex-col items-center justify-center text-slate-400">
                                <i class="fa-solid fa-door-open text-lg mb-0.5"></i>
                                <span class="text-[0.55rem] font-bold uppercase tracking-wider">Entry</span>
                            </div>
                            <div class="steering-wheel"></div>
                        </div>

                        <!-- Seats Grid Generation -->
                        <div class="seat-grid">
                            <?php
                            $seat_price = $price;
                            for($i=1; $i<=32; $i++) {
                                // Add booked class if seat is booked
                                if(in_array($i, $bookedSeats)) {
                                    echo "<div class='seat booked' title='Seat $i (Booked)'>$i</div>";
                                } else {
                                    echo "<div class='seat' id='seat_$i' onclick='toggleSeat($i, $seat_price)' title='Seat $i'>$i</div>";
                                }
                            }
                            ?>
                        </div>
                        
                        <!-- Rear -->
                        <div class="absolute -bottom-5 left-1/2 transform -translate-x-1/2 bg-slate-200 px-8 py-1.5 rounded-full text-xs font-black text-slate-500 border-[3px] border-white shadow-sm tracking-widest">
                            REAR
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right: Booking Summary -->
            <div class="w-full lg:w-5/12">
                <div class="glass-panel rounded-[2rem] p-8 md:p-10 sticky top-24 transition-all" id="summary-panel">
                    <h3 class="text-2xl font-extrabold mb-8 text-slate-800 flex items-center gap-3 border-b border-gray-100 pb-5">
                        <i class="fa-solid fa-receipt text-red-500 bg-red-50 p-2.5 rounded-xl"></i> 
                        Booking Summary
                    </h3>
                    
                    <div class="space-y-5 mb-10">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-slate-500 font-semibold text-sm uppercase tracking-wide">Selected Seats</span>
                            <span id="selected-seats-display" class="font-bold text-lg text-slate-800">
                                <span class="text-gray-400 text-sm italic font-normal">None selected</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-slate-500 font-semibold text-sm uppercase tracking-wide">Price per Seat</span>
                            <span class="font-bold text-slate-800 text-lg">₹<?php echo $price; ?></span>
                        </div>
                        
                        <div class="mt-4 border-t border-dashed border-slate-200 pt-6">
                            <div class="flex justify-between items-center bg-slate-800 px-6 py-5 rounded-2xl shadow-inner text-white">
                                <span class="font-bold uppercase tracking-widest text-sm text-slate-300">Total Amount</span>
                                <span id="total-amount" class="text-3xl font-black text-emerald-400 transition-all">₹0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Form -->
                    <form action="confirm_booking.php" method="POST" id="bookingForm" class="bg-slate-50 p-6 rounded-2xl border border-slate-100" onsubmit="return validateBookingForm()">
                        <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id); ?>">
                        <input type="hidden" id="seat_number" name="seat_number" required>
                        <input type="hidden" name="src" value="<?php echo htmlspecialchars($src); ?>">
                        <input type="hidden" name="dest" value="<?php echo htmlspecialchars($dest); ?>">
                        <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
                        <input type="hidden" name="bname" value="<?php echo htmlspecialchars($bname); ?>">
                        <input type="hidden" name="dep" value="<?php echo htmlspecialchars($dep); ?>">
                        <input type="hidden" name="arr" value="<?php echo htmlspecialchars($arr); ?>">
                        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                        
                        <!-- Primary Contact Information -->
                        <div class="mb-6 border-b border-slate-200 pb-5">
                            <h4 class="text-sm font-bold text-slate-700 mb-3 uppercase tracking-wide"><i class="fa-solid fa-address-book text-red-500 mr-2"></i>Primary Contact</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[0.7rem] font-bold text-slate-500 mb-1.5 uppercase tracking-widest">Email Address <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-envelope"></i></div>
                                        <input type="email" id="primary_email" name="primary_email" required placeholder="Enter email address" 
                                            value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                                            class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-semibold text-slate-800 bg-white placeholder-slate-300">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[0.7rem] font-bold text-slate-500 mb-1.5 uppercase tracking-widest">Mobile Number <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-phone"></i></div>
                                        <input type="tel" id="primary_phone" name="primary_phone" required placeholder="10-digit mobile number" pattern="[0-9]{10}"
                                            value="<?php echo isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : ''; ?>"
                                            class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-semibold text-slate-800 bg-white placeholder-slate-300">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Passenger Forms Container -->
                        <div id="passengers-container" class="mb-6 space-y-4 empty:hidden">
                            <!-- Passenger cards will be injected here by JS -->
                        </div>
                        
                        <button type="button" id="submitBtn" onclick="submitBooking()" disabled 
                            class="group w-full py-4 rounded-xl text-white font-black text-sm tracking-widest transition-all shadow-md hover:shadow-xl hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none bg-gradient-to-r from-red-500 to-rose-600 flex items-center justify-center gap-3">
                            <span>PROCEED TO BOOK</span>
                            <i class="fa-solid fa-arrow-right group-disabled:hidden transform group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <!-- Interactive Script -->
    <script>
        let selectedSeats = [];
        
        // Pass session data to JS for auto-filling the first passenger
        const loggedInUser = {
            name: "<?php echo isset($_SESSION['user_name']) ? addslashes(explode(' ', $_SESSION['user_name'])[0]) : (isset($_SESSION['name']) ? addslashes($_SESSION['name']) : ''); ?>",
            email: "<?php echo isset($_SESSION['email']) ? addslashes($_SESSION['email']) : ''; ?>",
            phone: "<?php echo isset($_SESSION['phone']) ? addslashes($_SESSION['phone']) : ''; ?>"
        };
        
        function toggleSeat(seatNum, price) {
            const seatEl = document.getElementById('seat_' + seatNum);
            
            if (seatEl.classList.contains('selected')) {
                seatEl.classList.remove('selected');
                selectedSeats = selectedSeats.filter(s => s !== String(seatNum) && s !== seatNum);
            } else {
                seatEl.classList.add('selected');
                selectedSeats.push(seatNum);
            }
            
            updateSummary(price);
        }
        
        function updateSummary(pricePerSeat) {
            const seatsDisplay = document.getElementById('selected-seats-display');
            const totalDisplay = document.getElementById('total-amount');
            const inputField = document.getElementById('seat_number');
            const submitBtn = document.getElementById('submitBtn');
            const summaryPanel = document.getElementById('summary-panel');
            
            if (selectedSeats.length > 0) {
                totalDisplay.classList.remove('pulse-once');
                void totalDisplay.offsetWidth; // trigger reflow
                totalDisplay.classList.add('pulse-once');
                
                const badges = selectedSeats.map(s => `<span class="bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-md border border-emerald-200 text-sm truncate inline-block max-w-[60px] text-center mb-1 mr-1 shadow-sm">${s}</span>`).join('');
                seatsDisplay.innerHTML = `<div class="flex flex-wrap justify-end items-center">${badges} <span class="text-slate-500 text-xs ml-1 font-semibold uppercase tracking-wider">(${selectedSeats.length} seats)</span></div>`;
                
                const totalPrice = pricePerSeat * selectedSeats.length;
                totalDisplay.innerText = '₹' + totalPrice;
                
                // Set hidden input value as comma-separated
                inputField.value = selectedSeats.join(',');
                
                submitBtn.disabled = false;
                summaryPanel.classList.add('shadow-2xl');
                summaryPanel.classList.remove('shadow-sm');
                
            } else {
                seatsDisplay.innerHTML = '<span class="text-slate-400 text-sm italic font-normal">None selected</span>';
                totalDisplay.innerText = '₹0';
                inputField.value = '';
                
                submitBtn.disabled = true;
                summaryPanel.classList.remove('shadow-2xl');
                summaryPanel.classList.add('shadow-sm');
            }
            
            generatePassengerForms();
        }

        function generatePassengerForms() {
            const container = document.getElementById('passengers-container');
            container.innerHTML = '';
            
            if (selectedSeats.length === 0) return;
            
            let html = `<h4 class="text-sm font-bold text-slate-700 mb-3 uppercase tracking-wide border-b border-slate-200 pb-2"><i class="fa-solid fa-users text-red-500 mr-2"></i>Passenger Details</h4>`;
            
            selectedSeats.forEach((seat, index) => {
                const isFirst = index === 0;
                const autoName = isFirst && loggedInUser.name ? loggedInUser.name : '';
                
                html += `
                <div class="bg-white p-4 sm:p-5 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden transition-all hover:border-slate-300">
                    <div class="absolute top-0 right-0 bg-slate-100 text-slate-500 text-[0.65rem] font-bold px-3 py-1 rounded-bl-lg border-b border-l border-slate-200 uppercase tracking-widest shadow-sm">
                        Seat ${seat}
                    </div>
                    
                    <h5 class="text-xs font-bold text-slate-800 mb-4 flex items-center gap-2"><div class="w-5 h-5 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-[0.65rem]">${index + 1}</div> Passenger</h5>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[0.65rem] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="passenger_names[]" required placeholder="Full Name" value="${autoName}"
                                class="w-full px-3 py-2.5 text-sm font-medium rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-red-500/50 bg-slate-50 focus:bg-white text-slate-800 transition-all placeholder-slate-300">
                        </div>
                        <div class="flex gap-3">
                            <div class="w-1/2">
                                <label class="block text-[0.65rem] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Age <span class="text-red-500">*</span></label>
                                <input type="number" name="passenger_ages[]" required min="1" max="100" placeholder="Age"
                                    class="w-full px-3 py-2.5 text-sm font-medium rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-red-500/50 bg-slate-50 focus:bg-white text-slate-800 transition-all placeholder-slate-300">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-[0.65rem] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Gender <span class="text-red-500">*</span></label>
                                <select name="passenger_genders[]" required class="w-full px-2 py-2.5 text-sm font-medium rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-red-500/50 bg-slate-50 focus:bg-white text-slate-800 transition-all cursor-pointer">
                                    <option value="" disabled selected>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[0.65rem] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">ID Type <span class="text-red-500">*</span></label>
                            <select name="passenger_id_types[]" required class="w-full px-2 py-2.5 text-sm font-medium rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-red-500/50 bg-slate-50 focus:bg-white text-slate-800 transition-all cursor-pointer">
                                <option value="" disabled selected>Select ID Proof</option>
                                <option value="Aadhar">Aadhar Card</option>
                                <option value="PAN">PAN Card</option>
                                <option value="DL">Driving License</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[0.65rem] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">ID Number <span class="text-red-500">*</span></label>
                            <input type="text" name="passenger_id_numbers[]" required placeholder="Enter ID Proof Number"
                                class="w-full px-3 py-2.5 text-sm font-medium rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-red-500/50 bg-slate-50 focus:bg-white text-slate-800 transition-all placeholder-slate-300">
                        </div>
                    </div>
                </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function validateBookingForm() {
            const priPhone = document.getElementById('primary_phone').value;
            if (priPhone && !/^\d{10}$/.test(priPhone)) {
                alert("Primary phone must be exactly 10 digits.");
                return false;
            }
            return true;
        }

        function submitBooking() {
            if(selectedSeats.length > 0) {
                if(!validateBookingForm()) return;
                
                // Form validity check API
                if(!document.getElementById('bookingForm').checkValidity()) {
                    document.getElementById('bookingForm').reportValidity();
                    return;
                }
                
                const btn = document.getElementById('submitBtn');
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span class="ml-2">PROCESSING...</span>';
                btn.disabled = true;
                
                // Slight delay to allow UI to render spinner
                setTimeout(() => {
                    document.getElementById('bookingForm').submit();
                }, 100);
            }
        }
    </script>
</body>
</html>