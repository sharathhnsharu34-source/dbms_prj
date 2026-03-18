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
$sql = "SELECT b.booking_id, b.seat_number, bu.bus_name, bu.source, bu.destination, bu.departure_time, bu.arrival_time, bu.price 
        FROM bookings b 
        JOIN buses bu ON b.bus_id = bu.bus_id 
        WHERE b.user_id = '$user_id'
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
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition font-medium"><i class="fa-solid fa-right-from-bracket mr-2"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 fade-in">
        
        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-200">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-500 text-xl shadow-sm">
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
                while($row = $result->fetch_assoc()) {
                    $seats_array = array_filter(array_map('trim', explode(',', $row['seat_number'])));
                    $num_seats = count($seats_array);
                    $total_amount = $num_seats * $row['price'];
                    $b_id_display = "BUS-" . str_pad($row['booking_id'], 5, "0", STR_PAD_LEFT);
                    $count++;
                    
                    echo '
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all border border-gray-100/80 overflow-hidden transform hover:-translate-y-1">
                        <!-- Top Header -->
                        <div class="p-5 pb-4 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex justify-between items-center relative">
                            <div class="absolute right-0 top-0 w-24 h-24 bg-white/5 rounded-bl-[100px]"></div>
                            <div>
                                <h3 class="font-bold text-lg truncate relative z-10">'.$row['bus_name'].'</h3>
                                <p class="text-[0.65rem] text-emerald-400 font-bold uppercase tracking-widest relative z-10"><i class="fa-solid fa-circle-check"></i> Confirmed</p>
                            </div>
                            <div class="text-right flex flex-col items-end relative z-10">
                                <span class="bg-white/20 px-2 py-0.5 rounded text-[0.65rem] uppercase tracking-wider mb-1 font-semibold">ID: '.$b_id_display.'</span>
                            </div>
                        </div>
                        
                        <!-- Middle Body -->
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div class="w-[45%]">
                                    <p class="text-xl font-bold text-slate-800 tracking-tight">'.$row['departure_time'].'</p>
                                    <p class="text-[0.75rem] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate">'.$row['source'].'</p>
                                </div>
                                <div class="w-[10%] flex justify-center text-slate-300">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                                <div class="w-[45%] text-right">
                                    <p class="text-xl font-bold text-slate-800 tracking-tight">'.$row['arrival_time'].'</p>
                                    <p class="text-[0.75rem] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate">'.$row['destination'].'</p>
                                </div>
                            </div>
                            
                            <hr class="border-t border-dashed border-gray-200 mb-5">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Seats ('.$num_seats.')</p>
                                    <p class="font-bold text-emerald-600 text-sm">'.$row['seat_number'].'</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest mb-1">Total Paid</p>
                                    <p class="font-bold text-rose-500 text-lg leading-none">₹'.$total_amount.'</p>
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
</html>
