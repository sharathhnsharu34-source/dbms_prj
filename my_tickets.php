<?php
session_start();
include("db/connect.php");

if(!isset($_SESSION['user_id'])){
    // Case 2: User is NOT LOGGED IN
    echo "<script>
        alert('Please login/signup to view your tickets');
        window.location='login.php';
    </script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = explode(' ', $_SESSION['user_name'])[0];

// Fetch all tickets for this user using JOIN to get bus details
$sql = "SELECT b.booking_id, b.seat_number, b.passenger_name, b.final_price, b.booking_status, b.refund_amount, b.cancelled_at, bu.bus_name, bu.source, bu.destination, bu.departure_time, bu.arrival_time, bu.price, bu.journey_date, bu.bus_type 
        FROM bookings b 
        JOIN buses bu ON b.bus_id = bu.bus_id 
        WHERE b.user_id = '$user_id' AND b.payment_status = 'Success'
        ORDER BY b.booking_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets | BusBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Ticket Animations */
        .ticket-card { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); cursor: pointer; }
        .ticket-card:hover { transform: scale(1.02) translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        
        /* Modal Animations */
        .modal { display: none; position: fixed; inset: 0; z-index: 9999; justify-content: center; align-items: center; }
        .modal.active { display: flex; animation: bgFadeIn 0.3s forwards; }
        .modal-content { opacity: 0; transform: translateY(20px); }
        .modal.active .modal-content { animation: scaleUpFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes bgFadeIn { from { background: rgba(0,0,0,0); } to { background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); } }
        @keyframes scaleUpFadeIn { from { opacity: 0; transform: translateY(40px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .modal.closing { animation: bgFadeOut 0.3s forwards; }
        .modal.closing .modal-content { animation: scaleDownFadeOut 0.3s forwards; }
        @keyframes bgFadeOut { from { background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); } to { background: rgba(0,0,0,0); backdrop-filter: blur(0px); } }
        @keyframes scaleDownFadeOut { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(20px) scale(0.95); } }
        
        /* Ticket Dashed Line */
        .ticket-dashed-border { border-top: 2px dashed #e2e8f0; position: relative; }
        .ticket-dashed-border::before, .ticket-dashed-border::after { content: ''; position: absolute; top: -10px; width: 20px; height: 20px; background-color: #f8fafc; border-radius: 50%; z-index: 10; }
        .ticket-dashed-border::before { left: -42px; }
        .ticket-dashed-border::after { right: -42px; }
    </style>
</head>
<body class="pb-20">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer transition transform hover:-translate-y-0.5" onclick="window.location.href='index.php'">
                    <i class="fa-solid fa-bus text-2xl text-red-500"></i>
                    <span class="font-bold text-xl tracking-wide text-gray-900">Bus<span class="text-red-500">Book</span></span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="index.php" class="text-gray-500 hover:text-slate-900 transition-colors font-semibold flex items-center gap-2 text-sm uppercase tracking-wide hidden md:flex">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                    
                    <div class="w-px h-6 bg-gray-200 hidden md:block"></div>
                    
                    <a href="my_tickets.php" class="text-red-600 font-bold transition flex items-center gap-2">
                        <i class="fa-solid fa-ticket"></i> <span class="hidden sm:inline">My Tickets</span>
                    </a>
                    <div class="relative group cursor-pointer z-50">
                        <span class="font-bold text-red-500 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600"><i class="fa-solid fa-user text-sm"></i></span>
                            <span class="hidden sm:inline"><?php echo htmlspecialchars($user_name); ?></span>
                        </span>
                        <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl py-2 hidden group-hover:block border border-gray-100">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 hover:text-indigo-600 transition font-medium"><i class="fa-solid fa-id-badge mr-2"></i> Profile</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-600 transition font-medium"><i class="fa-solid fa-right-from-bracket mr-2"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 fade-in">
        
        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-200">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-1 text-red-500 text-xl shadow-sm">
                <i class="fa-solid fa-ticket-simple"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">My Bookings</h1>
                <p class="text-sm text-slate-500 font-medium">View and manage your past and upcoming travels.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            if($result && $result->num_rows > 0) {
                $count = 0;
                while($ticket = $result->fetch_assoc()) {
                    $seats_array = array_filter(array_map('trim', explode(',', $ticket['seat_number'])));
                    $num_seats = count($seats_array);
                    $base_fare = $num_seats * $ticket['price'];
                    $total_fare = isset($ticket['final_price']) && $ticket['final_price'] !== null ? floatval($ticket['final_price']) : $base_fare;

                    $departure_str = $ticket['journey_date'] . ' ' . $ticket['departure_time'];
                    $departure_time = strtotime($departure_str);
                    if (!$departure_time) $departure_time = strtotime($ticket['journey_date']);
                    $is_future = ($departure_time - time()) > 0;
                    
                    $b_id_display = "BUS-" . str_pad($ticket['booking_id'], 5, "0", STR_PAD_LEFT);
                    
                    // Calculate Duration
                    $dep_time = new DateTime($ticket['departure_time']);
                    $arr_time = new DateTime($ticket['arrival_time']);
                    if ($arr_time < $dep_time) {
                        // Crosses midnight
                        $arr_time->modify('+1 day');
                    }
                    $interval = $dep_time->diff($arr_time);
                    $duration = $interval->h . 'h ' . $interval->i . 'm';
                    
                    $j_date = date('d M Y', strtotime($ticket['journey_date'] ?? date('Y-m-d')));
                    $bus_type = $ticket['bus_type'] ?? 'A/C Sleeper';
                    
                    // Fetch passenger names dynamically
                    $b_id_current = $ticket['booking_id'];
                    $pass_sql = "SELECT name FROM passengers WHERE booking_id = '$b_id_current'";
                    $pass_res = $conn->query($pass_sql);
                    $pass_names = [];
                    if($pass_res && $pass_res->num_rows > 0){
                        while($pr = $pass_res->fetch_assoc()){
                            $pass_names[] = trim($pr['name']);
                        }
                    } else {
                        $pass_names[] = trim($ticket['passenger_name'] ?? 'Passenger');
                    }
                    $pass_names_json = htmlspecialchars(json_encode($pass_names), ENT_QUOTES, 'UTF-8');
                    
                    echo '
                    <div class="ticket-card bg-white rounded-2xl shadow-sm border border-gray-100/80 overflow-hidden">
                        <!-- Top Header -->
                        <div class="p-5 pb-4 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex justify-between items-center relative">
                            <div class="absolute right-0 top-0 w-24 h-24 bg-white/5 rounded-bl-[100px]"></div>
                            <div>
                                <h3 class="font-bold text-lg truncate relative z-10">'.$ticket['bus_name'].'</h3>
                                <p class="text-[0.65rem] text-emerald-400 font-bold uppercase tracking-widest relative z-10"><i class="fa-solid fa-circle-check"></i> Confirmed</p>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                ';
                                if($ticket['booking_status'] === 'Cancelled'):
                                echo '
                                <div class="bg-red-100/50 text-red-600 border border-red-200/50 px-2 py-0.5 rounded flex items-center gap-1 overflow-hidden relative group">
                                    <i class="fa-solid fa-circle-xmark text-[0.6rem]"></i>
                                    <span class="text-[0.65rem] font-bold uppercase tracking-widest">Cancelled</span>
                                </div>
                                ';
                                else:
                                echo '
                                <div class="bg-emerald-100/50 text-emerald-600 border border-emerald-200/50 px-2 py-0.5 rounded flex items-center gap-1 overflow-hidden relative group">
                                    <span class="absolute inset-0 bg-emerald-400 opacity-20 transform -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-in-out"></span>
                                    <i class="fa-solid fa-circle-check text-[0.6rem]"></i>
                                    <span class="text-[0.65rem] font-bold uppercase tracking-widest">Confirmed</span>
                                </div>
                                ';
                                endif;
                                echo '
                                <span class="text-[#0f172a] font-mono font-black tracking-wide text-xs mt-1.5">'.$b_id_display.'</span>
                            </div>
                        </div>
                        
                        <!-- Middle Body -->
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-5">
                                <div class="w-[45%]">
                                    <p class="text-xl font-bold text-slate-800 tracking-tight">'.date('h:i A', strtotime($ticket['departure_time'])).'</p>
                                    <p class="text-[0.75rem] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate">'.$ticket['source'].'</p>
                                </div>
                                <div class="w-[10%] flex justify-center text-slate-300">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                                <div class="w-[45%] text-right">
                                    <p class="text-xl font-bold text-slate-800 tracking-tight">'.date('h:i A', strtotime($ticket['arrival_time'])).'</p>
                                    <p class="text-[0.75rem] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate">'.$ticket['destination'].'</p>
                                </div>
                            </div>
                            
                            <!-- Sub-info Row -->
                            <div class="flex justify-between items-center bg-slate-50 px-4 py-3 rounded-xl mb-5 space-x-2">
                                <div class="flex flex-col">
                                    <span class="text-[0.6rem] uppercase tracking-widest text-slate-400 font-bold mb-0.5">Date</span>
                                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">'.$j_date.'</span>
                                </div>
                                <div class="w-px h-6 bg-slate-200"></div>
                                <div class="flex flex-col text-center">
                                    <span class="text-[0.6rem] uppercase tracking-widest text-slate-400 font-bold mb-0.5">Duration</span>
                                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">'.$duration.'</span>
                                </div>
                                <div class="w-px h-6 bg-slate-200"></div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[0.6rem] uppercase tracking-widest text-slate-400 font-bold mb-0.5">Passengers</span>
                                    <span class="text-xs font-bold text-slate-700">'.$num_seats.'</span>
                                </div>
                            </div>
                            
                            <hr class="border-t border-dashed border-gray-200 mb-5">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Seats ('.$num_seats.')</p>
                                    <p class="font-bold text-emerald-600 text-sm truncate">'.$ticket['seat_number'].'</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Total Paid</p>
                                    <p class="font-bold text-rose-500 text-lg leading-none">₹'.$total_fare.'</p>
                                </div>
                            </div>
                            <!-- Action Buttons -->
                            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center relative z-20">
                                <button onclick="openModal(\''.addslashes($ticket['booking_id']).'\', '.$pass_names_json.', \''.addslashes($ticket['source']).'\', \''.addslashes($ticket['destination']).'\', \''.addslashes($j_date).'\', \''.addslashes($ticket['departure_time']).'\', \''.addslashes($ticket['arrival_time']).'\', \''.addslashes($ticket['bus_name']).'\', \''.addslashes($bus_type).'\', \''.addslashes($ticket['seat_number']).'\', \''.addslashes($total_fare).'\', \''.addslashes($ticket['booking_status']).'\')" class="text-xs font-bold text-slate-500 hover:text-emerald-600 transition-colors uppercase tracking-widest flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-emerald-50 border border-transparent hover:border-emerald-100">
                                    <i class="fa-solid fa-eye text-emerald-500/70"></i> View Details
                                </button>
                                
                                <div class="flex items-center gap-2">
                                    ';
                                    if($ticket['booking_status'] === 'Cancelled'):
                                    echo '
                                        <div class="text-[0.55rem] font-black text-slate-500 bg-slate-100 border border-slate-200 uppercase tracking-widest px-2 py-1.5 rounded-xl flex items-center justify-center flex-col leading-tight cursor-default">
                                            <span>Cancelled ❌</span>
                                            <span class="text-emerald-600 mt-0.5">Refund: ₹'.htmlspecialchars($ticket['refund_amount']).'</span>
                                        </div>
                                    ';
                                    else:
                                    echo '
                                        ';
                                        if($is_future):
                                        echo '
                                            <a href="cancel_ticket.php?booking_id='.$ticket['booking_id'].'" class="text-[0.65rem] font-black text-slate-500 hover:text-white bg-slate-50 hover:bg-slate-700 transition-all border border-slate-200 uppercase tracking-widest px-3 py-2 rounded-xl shadow-sm hover:shadow-md flex items-center gap-1.5 m-0">
                                                <i class="fa-solid fa-ban"></i> Cancel
                                            </a>
                                        ';
                                        endif;
                                        echo '
                                        <a href="download_ticket.php?id='.$ticket['booking_id'].'" class="text-[0.65rem] font-black text-rose-500 hover:text-white bg-rose-50 hover:bg-rose-500 transition-all border border-rose-100 uppercase tracking-widest px-4 py-2 rounded-xl shadow-sm hover:shadow-md flex items-center gap-1.5 relative z-20">
                                            <i class="fa-solid fa-download"></i> e-Ticket
                                        </a>
                                    ';
                                    endif;
                                    echo '
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '
                <div class="col-span-full bg-white rounded-[2rem] p-16 text-center shadow-sm border border-gray-100 flex flex-col items-center">
                    <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mb-6 text-red-300">
                        <i class="fa-solid fa-ticket text-4xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 mb-2">No Tickets Found</h2>
                    <p class="text-slate-500 text-sm mb-6 max-w-sm mx-auto">You haven\'t booked any tickets yet. Ready to start your adventure?</p>
                    <a href="index.php" class="px-6 py-3 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 text-sm tracking-wide">
                        Book a Bus Now
                    </a>
                </div>';
            }
            ?>
        </div>
    </div>

</body>
    <!-- Ticket Modal -->
    <div id="ticketModal" class="modal" onclick="closeModal()">
        <div class="modal-content w-[95%] sm:w-[450px] bg-white rounded-3xl overflow-hidden shadow-2xl relative" onclick="event.stopPropagation()">
            <!-- Ticket Header -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-6 text-white text-center relative">
                <button onclick="closeModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/40 transition-colors text-white focus:outline-none">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="w-16 h-16 bg-white/10 border border-white/20 rounded-full flex items-center justify-center text-white mx-auto mb-3 shadow-sm backdrop-blur-sm">
                    <i class="fa-solid fa-bus text-2xl"></i>
                </div>
                <h3 class="text-2xl font-black tracking-widest uppercase mb-1 drop-shadow-md" id="m_bus_name">Volvo Express</h3>
                <p class="text-[0.65rem] font-bold text-slate-300 uppercase tracking-widest" id="m_bus_type">A/C Sleeper</p>
                <div id="m_status_badge" class="mt-4 inline-block bg-emerald-500 text-white px-3 py-1 rounded-full text-[0.65rem] font-black uppercase tracking-widest shadow-md">
                    <i class="fa-solid fa-circle-check mr-1"></i> Booking Confirmed
                </div>
            </div>
            
            <!-- Ticket Body -->
            <div class="px-8 py-7 bg-white relative">
                
                <div class="flex justify-between items-center mb-7">
                    <div class="w-[40%]">
                        <p class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Source</p>
                        <p class="text-lg font-black text-slate-800 tracking-tight leading-none mb-1.5 truncate" id="m_src">BENGALURU</p>
                        <p class="text-sm font-bold text-rose-500" id="m_dep">08:00 AM</p>
                    </div>
                    <div class="w-[20%] flex flex-col items-center justify-center text-slate-200">
                        <i class="fa-solid fa-bus text-xl text-slate-300 mb-2"></i>
                        <div class="w-full border-t-2 border-dashed border-slate-200"></div>
                    </div>
                    <div class="w-[40%] text-right">
                        <p class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Destination</p>
                        <p class="text-lg font-black text-slate-800 tracking-tight leading-none mb-1.5 truncate" id="m_dest">MUMBAI</p>
                        <p class="text-sm font-bold text-rose-500" id="m_arr">10:00 PM</p>
                    </div>
                </div>
                
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-7">
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                        <div>
                            <p class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Journey Date</p>
                            <p class="font-bold text-slate-700 text-sm" id="m_date">25 Mar 2026</p>
                        </div>
                        <div>
                            <p class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Booking ID</p>
                            <p class="font-bold text-slate-700 text-sm" id="m_bid">BUS-00123</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Passenger(s)</p>
                            <div class="font-bold text-slate-700 text-sm" id="m_passenger">John Doe + 2 others</div>
                        </div>
                    </div>
                </div>
                
                <!-- Separator -->
                <div class="ticket-dashed-border mb-7"></div>
                
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <p class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Seat Number(s)</p>
                        <div class="w-32"><p class="font-black text-slate-800 text-sm break-words" id="m_seats">A1, A2</p></div>
                    </div>
                    <div class="text-right">
                        <p class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Total Amount</p>
                        <p class="font-black text-rose-500 text-2xl leading-none truncate" id="m_total">₹1500</p>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Action Area -->
            <div class="bg-slate-50 px-8 py-5 flex items-center justify-between border-t border-slate-100 relative">
                <!-- Inner cutouts -->
                <div class="absolute left-0 top-0 -mt-3 -ml-3 w-6 h-6 bg-slate-900 rounded-full"></div>
                <div class="absolute right-0 top-0 -mt-3 -mr-3 w-6 h-6 bg-slate-900 rounded-full"></div>
                
                <div class="w-14 h-14 bg-white rounded shadow-sm border border-slate-200 flex items-center justify-center p-1">
                    <i class="fa-solid fa-qrcode text-3xl text-slate-800"></i>
                </div>
                <button onclick="downloadTicket()" class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold rounded-xl shadow-md transition-all text-sm tracking-wide flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-download"></i> Download
                </button>
            </div>
            
            <!-- Sawtooth decorative bottom -->
            <div class="h-3 w-full bg-slate-50 relative bottom-0" style="background-image: radial-gradient(circle at 10px 0, transparent 0, transparent 10px, white 10px); background-size: 20px 20px; background-position: -10px center; background-repeat: repeat-x; transform: rotate(180deg);"></div>
        </div>
    </div>

    <script>
        function openModal(bid, pass_arr, src, dest, date, dep, arr, bus, type, seats, total, status) {
            document.getElementById('m_bid').innerText = bid;
            
            let passHtml = '';
            if (Array.isArray(pass_arr) && pass_arr.length > 1) {
                passHtml = '<ul class="list-inside list-disc pl-1 space-y-0.5 mt-1">';
                pass_arr.forEach(name => {
                    let safeName = document.createElement("div"); safeName.innerText = name;
                    passHtml += `<li>${safeName.innerHTML}</li>`;
                });
                passHtml += '</ul>';
            } else {
                let name = Array.isArray(pass_arr) ? pass_arr[0] : pass_arr;
                let safeName = document.createElement("div"); safeName.innerText = name || 'Passenger';
                passHtml = safeName.innerHTML;
            }
            document.getElementById('m_passenger').innerHTML = passHtml;
            document.getElementById('m_src').innerText = src;
            document.getElementById('m_dest').innerText = dest;
            document.getElementById('m_date').innerText = date;
            
            // Format time 12h
            const formatTime = (timeStr) => {
                let [h, m] = timeStr.split(':');
                h = parseInt(h);
                let ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return `${String(h).padStart(2, '0')}:${m} ${ampm}`;
            };
            
            document.getElementById('m_dep').innerText = formatTime(dep);
            document.getElementById('m_arr').innerText = formatTime(arr);
            document.getElementById('m_bus_name').innerText = bus;
            document.getElementById('m_bus_type').innerText = type;
            document.getElementById('m_seats').innerText = seats;
            document.getElementById('m_total').innerText = '₹' + total;
            
            let statusBadge = document.getElementById('m_status_badge');
            if (status === 'Cancelled') {
                statusBadge.className = 'mt-4 inline-block bg-red-500 text-white px-3 py-1 rounded-full text-[0.65rem] font-black uppercase tracking-widest shadow-md';
                statusBadge.innerHTML = '<i class="fa-solid fa-circle-xmark mr-1"></i> Booking Cancelled';
            } else {
                statusBadge.className = 'mt-4 inline-block bg-emerald-500 text-white px-3 py-1 rounded-full text-[0.65rem] font-black uppercase tracking-widest shadow-md';
                statusBadge.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i> Booking Confirmed';
            }
            
            
            const modal = document.getElementById('ticketModal');
            modal.style.display = 'flex';
            modal.classList.remove('closing');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            const modal = document.getElementById('ticketModal');
            modal.classList.remove('active');
            modal.classList.add('closing');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('closing');
                document.body.style.overflow = 'auto';
            }, 300);
        }
        
        function downloadTicket() {
            let rawBid = document.getElementById('m_bid').innerText;
            // e.g., "BUS-00123" -> "123"
            let numericBid = parseInt(rawBid.replace('BUS-', ''), 10);
            window.open('download_ticket.php?id=' + numericBid, '_blank');
        }
    </script>
</body>
</html>
