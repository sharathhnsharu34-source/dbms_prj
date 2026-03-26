<?php
session_start();
include("db/connect.php");

if(!isset($_SESSION['user_id'])){
    echo "<script>
        alert('Please login/signup to view your profile');
        window.location='login.php';
    </script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$session_name = explode(' ', $_SESSION['user_name'])[0];

// Fetch User Profile
$user_sql = "SELECT name, email, phone FROM users WHERE id = '$user_id'";
$user_res = $conn->query($user_sql);
$user_info = $user_res->fetch_assoc();

// Dashboard Statistics Count
$total_sql = "SELECT COUNT(*) as cnt FROM bookings WHERE user_id = '$user_id' AND payment_status = 'Success'";
$total_res = $conn->query($total_sql);
$total_bookings = $total_res->fetch_assoc()['cnt'];

$upcoming_count_sql = "SELECT COUNT(*) as cnt FROM bookings b JOIN buses bu ON b.bus_id = bu.bus_id WHERE b.user_id = '$user_id' AND b.payment_status = 'Success' AND b.booking_status = 'Active' AND bu.journey_date >= CURDATE()";
$upcoming_count_res = $conn->query($upcoming_count_sql);
$upcoming_trips = $upcoming_count_res->fetch_assoc()['cnt'];

$cancelled_sql = "SELECT COUNT(*) as cnt FROM bookings WHERE user_id = '$user_id' AND payment_status = 'Success' AND booking_status = 'Cancelled'";
$cancelled_res = $conn->query($cancelled_sql);
$cancelled_bookings = $cancelled_res->fetch_assoc()['cnt'];

// Fetch Upcoming Journeys
$upcoming_sql = "SELECT b.booking_id, b.seat_number, bu.bus_name, bu.source, bu.destination, bu.departure_time, bu.journey_date 
                 FROM bookings b 
                 JOIN buses bu ON b.bus_id = bu.bus_id 
                 WHERE b.user_id = '$user_id' AND b.payment_status = 'Success' AND b.booking_status = 'Active' AND bu.journey_date >= CURDATE()
                 ORDER BY bu.journey_date ASC";
$upcoming_res = $conn->query($upcoming_sql);

// Fetch Booking History
$history_sql = "SELECT b.booking_id, b.seat_number, b.booking_status, b.refund_amount, bu.bus_name, bu.source, bu.destination, bu.journey_date 
                FROM bookings b 
                JOIN buses bu ON b.bus_id = bu.bus_id 
                WHERE b.user_id = '$user_id' AND b.payment_status = 'Success' AND (bu.journey_date < CURDATE() OR b.booking_status = 'Cancelled')
                ORDER BY bu.journey_date DESC, b.booking_id DESC";
$history_res = $conn->query($history_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dashboard | BusBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .ticket-card { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); cursor: default; }
        .ticket-card:hover { transform: scale(1.02) translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
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
                    <a href="my_tickets.php" class="text-gray-500 hover:text-red-600 transition flex items-center gap-2 font-semibold">
                        <i class="fa-solid fa-ticket"></i> <span class="hidden sm:inline">My Tickets</span>
                    </a>
                    
                    <div class="relative group cursor-pointer z-50 ml-2">
                        <span class="font-bold text-red-500 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 border border-red-200"><i class="fa-solid fa-user text-sm"></i></span>
                            <span class="hidden sm:inline"><?php echo htmlspecialchars($session_name); ?></span>
                        </span>
                        <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl py-2 hidden group-hover:block border border-gray-100">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-red-600 bg-red-50 font-medium"><i class="fa-solid fa-id-badge mr-2"></i> Profile</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition font-medium"><i class="fa-solid fa-right-from-bracket mr-2"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 fade-in">
        
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-slate-800 to-slate-900 rounded-full flex items-center justify-center text-white text-xl shadow-md border-2 border-slate-100">
                    <i class="fa-solid fa-user-astronaut"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Profile Dashboard</h1>
                    <p class="text-sm text-slate-500 font-medium">Manage your personal details and travel history.</p>
                </div>
            </div>
            <a href="logout.php" class="hidden sm:flex text-[0.7rem] font-bold text-slate-500 hover:text-white bg-white hover:bg-rose-500 transition-all border border-slate-200 uppercase tracking-widest px-4 py-2 rounded-xl shadow-sm items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </div>

        <!-- Personal Information Section (Top Card) -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 mb-8 relative overflow-hidden flex flex-col md:flex-row items-center md:items-start gap-8">
            <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-bl-[100px] border-b border-l border-slate-100"></div>
            
            <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl shadow-md border-4 border-white flex-shrink-0 relative z-10">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="flex-1 text-center md:text-left relative z-10">
                <h3 class="text-2xl font-black text-slate-800 mb-3"><?php echo htmlspecialchars($user_info['name']); ?></h3>
                <div class="flex flex-col md:flex-row flex-wrap justify-center md:justify-start gap-4 md:gap-8">
                    <div class="flex items-center gap-2 text-slate-600 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                        <i class="fa-solid fa-envelope text-indigo-400"></i>
                        <span class="font-bold text-sm"><?php echo htmlspecialchars($user_info['email']); ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                        <i class="fa-solid fa-phone text-purple-400"></i>
                        <span class="font-bold tracking-wide text-sm font-mono"><?php echo htmlspecialchars($user_info['phone']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 md:mt-0 flex justify-end sm:hidden relative z-10 w-full md:w-auto">
                <a href="logout.php" class="w-full md:w-auto justify-center bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white transition-colors px-6 py-3 rounded-xl font-bold text-sm tracking-wide shadow-sm flex items-center gap-2 border border-rose-100">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Dashboard Summary Elements -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Total -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5 hover:-translate-y-1 transition-transform cursor-default">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl shadow-inner border border-indigo-100/50">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div>
                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Bookings</p>
                    <p class="text-3xl font-black text-slate-800 leading-none"><?php echo $total_bookings; ?></p>
                </div>
            </div>
            <!-- Upcoming -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5 hover:-translate-y-1 transition-transform cursor-default">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl shadow-inner border border-emerald-100/50">
                    <i class="fa-solid fa-bus-simple"></i>
                </div>
                <div>
                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Upcoming Trips</p>
                    <p class="text-3xl font-black text-slate-800 leading-none"><?php echo $upcoming_trips; ?></p>
                </div>
            </div>
            <!-- Cancelled -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5 hover:-translate-y-1 transition-transform cursor-default">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl shadow-inner border border-rose-100/50">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Cancelled</p>
                    <p class="text-3xl font-black text-slate-800 leading-none"><?php echo $cancelled_bookings; ?></p>
                </div>
            </div>
        </div>

        <!-- Upcoming Journeys Section -->
        <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2"><i class="fa-solid fa-location-arrow text-slate-300"></i> Upcoming Journeys</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php
            if($upcoming_res && $upcoming_res->num_rows > 0) {
                while($ticket = $upcoming_res->fetch_assoc()) {
                    $j_date = date('d M Y', strtotime($ticket['journey_date']));
                    $dep_time = date('h:i A', strtotime($ticket['departure_time']));
                    $seats_array = array_filter(array_map('trim', explode(',', $ticket['seat_number'])));
                    $num_seats = count($seats_array);
                    
                    echo '
                    <div class="ticket-card bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden relative">
                        <div class="absolute top-0 right-0 bg-emerald-500 text-white text-[0.55rem] font-black uppercase tracking-widest px-3 py-1 rounded-bl-lg shadow-sm">Upcoming</div>
                        <div class="p-6">
                            <h3 class="font-bold text-slate-800 text-lg mb-1 truncate pr-16">'.$ticket['bus_name'].'</h3>
                            
                            <div class="flex items-center gap-3 mt-4 mb-5">
                                <div class="w-1/2">
                                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1">From</p>
                                    <p class="font-black text-slate-700 truncate">'.$ticket['source'].'</p>
                                </div>
                                <div class="text-slate-300 text-xs"><i class="fa-solid fa-arrow-right"></i></div>
                                <div class="w-1/2 text-right">
                                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1">To</p>
                                    <p class="font-black text-slate-700 truncate">'.$ticket['destination'].'</p>
                                </div>
                            </div>
                            
                            <div class="bg-emerald-50 rounded-xl p-3 flex justify-between items-center border border-emerald-100/50">
                                <div>
                                    <p class="text-[0.6rem] font-bold text-emerald-600/70 uppercase tracking-widest mb-0.5">Date & Time</p>
                                    <p class="text-xs font-bold text-emerald-800">'.$j_date.' • '.$dep_time.'</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[0.6rem] font-bold text-emerald-600/70 uppercase tracking-widest mb-0.5">Seats ('.$num_seats.')</p>
                                    <p class="text-xs font-black text-emerald-800">'.$ticket['seat_number'].'</p>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '
                <div class="col-span-full bg-white rounded-2xl p-10 text-center shadow-sm border border-gray-100 border-dashed">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 mb-1">No upcoming journeys</h3>
                    <p class="text-xs text-slate-500 font-medium">You don\'t have any future trips planned right now.</p>
                </div>';
            }
            ?>
        </div>

        <!-- Booking History Section -->
        <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-slate-300"></i> Booking History</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            if($history_res && $history_res->num_rows > 0) {
                while($ticket = $history_res->fetch_assoc()) {
                    $j_date = date('d M Y', strtotime($ticket['journey_date']));
                    $seats_array = array_filter(array_map('trim', explode(',', $ticket['seat_number'])));
                    $num_seats = count($seats_array);
                    $status = $ticket['booking_status'];
                    
                    $status_class = $status === 'Cancelled' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100';
                    $status_icon = $status === 'Cancelled' ? '<i class="fa-solid fa-circle-xmark mr-1"></i> Cancelled' : '<i class="fa-solid fa-circle-check mr-1"></i> Confirmed';
                    
                    echo '
                    <div class="ticket-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="font-bold text-slate-800 text-lg truncate w-2/3">'.$ticket['bus_name'].'</h3>
                                <span class="text-[0.55rem] font-bold uppercase tracking-widest px-2 py-1 rounded-md border flex items-center '.$status_class.'">'.$status_icon.'</span>
                            </div>
                            
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-1/2">
                                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1">From</p>
                                    <p class="font-black text-slate-600 truncate text-sm">'.$ticket['source'].'</p>
                                </div>
                                <div class="text-slate-200 text-xs"><i class="fa-solid fa-arrow-right"></i></div>
                                <div class="w-1/2 text-right">
                                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1">To</p>
                                    <p class="font-black text-slate-600 truncate text-sm">'.$ticket['destination'].'</p>
                                </div>
                            </div>
                            
                            <div class="bg-slate-50 rounded-xl p-3 flex justify-between items-center border border-slate-100">
                                <div>
                                    <p class="text-[0.6rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Date</p>
                                    <p class="text-xs font-bold text-slate-700">'.$j_date.'</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[0.6rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Seats ('.$num_seats.')</p>
                                    <p class="text-xs font-black text-slate-700">'.($num_seats > 0 ? $ticket['seat_number'] : '-').'</p>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '
                <div class="col-span-full bg-white rounded-2xl p-10 text-center shadow-sm border border-gray-100 border-dashed">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fa-solid fa-ticket text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 mb-1">No bookings found</h3>
                    <p class="text-xs text-slate-500 font-medium">Your past travel history will appear here once you take a trip.</p>
                </div>';
            }
            ?>
        </div>
        
    </div>

</body>
</html>
