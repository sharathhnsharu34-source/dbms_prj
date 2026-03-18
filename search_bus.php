<?php
session_start();
include("db/connect.php");

$source=$_POST['source'];
$destination=$_POST['destination'];

$sql="SELECT * FROM buses WHERE source='$source' AND destination='$destination'";
$result=$conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Buses | Premium Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f5f7fa; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .hover-scale { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-scale:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
        .fade-in { animation: fadeIn 0.8s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .gradient-text { background: linear-gradient(135deg, #FF416C, #FF4B2B); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-gradient { background: linear-gradient(135deg, #ef4444, #dc2626); transition: all 0.3s ease; }
        .btn-gradient:hover { box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3); transform: translateY(-2px); }
    </style>
</head>
<body class="text-gray-800">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer transition transform hover:scale-105" onclick="window.location.href='index.php'">
                    <i class="fa-solid fa-bus text-2xl text-red-500 fa-bounce" style="--fa-animation-iteration-count: 2;"></i>
                    <span class="font-bold text-xl tracking-wide text-gray-900">Bus<span class="text-red-500">Book</span></span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="index.php" class="text-gray-500 hover:text-red-500 transition-colors font-medium flex items-center gap-2 hidden md:flex">
                        <i class="fa-solid fa-magnifying-glass"></i> Modify Search
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
                        <a href="signup.php" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2 rounded-xl transition-all shadow-md transform hover:-translate-y-0.5 text-sm font-semibold">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header & Filters -->
    <div class="bg-slate-900 text-white py-12 px-4 relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10 bg-[url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80')] bg-cover bg-center blend-overlay"></div>
        <div class="absolute -right-20 -bottom-20 opacity-5">
            <i class="fa-solid fa-bus text-[15rem]"></i>
        </div>
        <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-extrabold mb-3 flex items-center justify-center md:justify-start flex-wrap gap-2">
                    <?php echo htmlspecialchars($source); ?> <i class="fa-solid fa-arrow-right-long text-red-500 mx-2 animate-pulse"></i> <?php echo htmlspecialchars($destination); ?>
                </h1>
                <p class="text-gray-300 font-medium tracking-wide"><i class="fa-regular fa-calendar-check mr-2 text-red-400"></i> Showing available buses for today</p>
            </div>
            
            <!-- Static Filter Bar -->
            <div class="flex bg-white/10 backdrop-blur-md rounded-xl p-1 shadow-inner border border-white/10 text-sm font-semibold overflow-x-auto max-w-full no-scrollbar">
                <button class="px-6 py-2.5 rounded-lg bg-white text-slate-900 shadow-sm transition transform hover:scale-105 whitespace-nowrap">Cheapest First</button>
                <button class="px-6 py-2.5 rounded-lg text-white hover:bg-white/20 transition whitespace-nowrap">Fastest First</button>
                <button class="px-6 py-2.5 rounded-lg text-white hover:bg-white/20 transition whitespace-nowrap">Early Departure</button>
            </div>
        </div>
    </div>

    <!-- Bus List Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in relative min-h-[50vh]">
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Sidebar (Filters) -->
            <div class="w-full lg:w-1/4 hidden lg:block">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24 border border-gray-100 hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-lg mb-5 flex items-center gap-2 text-gray-800">
                        <i class="fa-solid fa-filter text-red-500"></i> Filters
                    </h3>
                    
                    <div class="mb-8">
                        <h4 class="font-semibold text-gray-400 mb-4 text-xs uppercase tracking-widest border-b pb-2">Bus Type</h4>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" checked class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">AC</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" checked class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">Non-AC</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" checked class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">Sleeper</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" checked class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">Seater</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content (Bus Cards) -->
            <div class="w-full lg:w-3/4 flex flex-col gap-6">
                
                <?php
                if($result->num_rows>0){
                    while($row=$result->fetch_assoc()){
                        // Generating random UI data for visuals
                        $bus_types = ['A/C Sleeper (2+1)', 'Non A/C Seater (2+2)', 'Volvo A/C Semi Sleeper', 'Scania A/C Multi Axle'];
                        $bus_type = $bus_types[$row['bus_id'] % 4];
                        $seats_left = mt_rand(2, 25);
                        $rating = number_format(mt_rand(35, 49) / 10, 1);
                        $reviews = mt_rand(10, 300);
                        
                        // Maintain existing GET logic of book.php while injecting extra purely visual parameters
                        $book_url = "book.php?bus_id=".$row['bus_id']."&src=".urlencode($source)."&dest=".urlencode($destination)."&price=".$row['price']."&type=".urlencode($bus_type)."&dep=".urlencode($row['departure_time'])."&arr=".urlencode($row['arrival_time'])."&bname=".urlencode($row['bus_name']);

                        echo '
                        <!-- Bus Card -->
                        <div class="bg-white rounded-[1.25rem] shadow-sm hover-scale border border-gray-100/80 overflow-hidden flex flex-col md:flex-row group transition-all">
                            <!-- Left: Image/Brand -->
                            <div class="md:w-[28%] bg-slate-50 p-6 flex flex-col justify-center border-r border-gray-100 relative group-hover:bg-red-50/30 transition-colors">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-red-500 to-rose-400"></div>
                                <h3 class="font-extrabold text-xl text-slate-800 mb-2 truncate">'.$row['bus_name'].'</h3>
                                <p class="text-xs text-slate-500 font-semibold bg-white border border-slate-200 inline-block px-2.5 py-1 rounded-md w-max mb-4 shadow-sm">'.$bus_type.'</p>
                                <div class="flex items-center gap-1.5 text-sm bg-emerald-50 border border-emerald-100 text-emerald-700 px-2.5 py-1.5 rounded-lg w-max shadow-sm">
                                    <i class="fa-solid fa-star text-xs text-emerald-500"></i> <span class="font-bold">'.$rating.'</span> <span class="text-emerald-600/70 text-xs font-medium">('.$reviews.')</span>
                                </div>
                            </div>
                            
                            <!-- Middle: Schedule -->
                            <div class="md:w-[44%] p-6 flex items-center justify-between relative">
                                <div class="text-center w-24">
                                    <h4 class="text-[1.35rem] font-bold text-slate-800 tracking-tight">'.$row['departure_time'].'</h4>
                                    <p class="text-[0.8rem] text-slate-500 font-medium mt-1 uppercase tracking-wide truncate">'.htmlspecialchars($source).'</p>
                                </div>
                                
                                <div class="flex-1 px-2 flex flex-col items-center">
                                    <p class="text-[0.7rem] text-slate-400 mb-2 font-medium tracking-wide"><i class="fa-regular fa-clock mr-1"></i>06h 30m</p>
                                    <div class="w-full h-[2px] bg-slate-200 relative flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-slate-300 absolute left-0"></div>
                                        <div class="w-2 h-2 rounded-full bg-slate-300 absolute right-0"></div>
                                        <div class="text-slate-300 absolute bg-white px-2 group-hover:text-red-400 transition-colors duration-500">
                                            <i class="fa-solid fa-bus text-lg"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center w-24">
                                    <h4 class="text-[1.35rem] font-bold text-slate-800 tracking-tight">'.$row['arrival_time'].'</h4>
                                    <p class="text-[0.8rem] text-slate-500 font-medium mt-1 uppercase tracking-wide truncate">'.htmlspecialchars($destination).'</p>
                                </div>
                            </div>
                            
                            <!-- Right: Price & CTA -->
                            <div class="md:w-[28%] p-6 flex flex-col justify-center items-end border-l border-gray-100 bg-white">
                                <p class="text-xs text-slate-400 mb-0.5 line-through font-medium">₹'.($row['price'] + mt_rand(150, 400)).'</p>
                                <h3 class="text-3xl font-extrabold text-slate-800 mb-3 tracking-tight">₹'.$row['price'].'</h3>
                                <p class="text-xs font-bold '.($seats_left < 5 ? 'text-red-500 bg-red-50' : 'text-emerald-600 bg-emerald-50').' mb-5 px-2.5 py-1 rounded w-max self-end border '.($seats_left < 5 ? 'border-red-100' : 'border-emerald-100').'">
                                    <i class="fa-solid fa-couch mr-1"></i> '.$seats_left.' Seats left
                                </p>
                                <a href="'.$book_url.'" class="w-full text-center py-3.5 rounded-xl text-white font-bold btn-gradient shadow-md tracking-wide text-sm flex items-center justify-center gap-2">
                                    VIEW SEATS <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </div>
                        </div>';
                    }
                } else {
                    echo '
                    <div class="bg-white rounded-[2rem] p-16 text-center shadow-sm border border-gray-100 flex flex-col items-center">
                        <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mb-6 border-8 border-white shadow-sm">
                            <i class="fa-solid fa-bus-simple text-[4rem] text-slate-300"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-3">No Buses Found</h2>
                        <p class="text-slate-500 mb-8 max-w-md leading-relaxed text-sm">We couldn\'t find any buses for your requested route today. Please try selecting a different source or destination.</p>
                        <a href="index.php" class="px-8 py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl transition-all shadow-md transform hover:-translate-y-1">
                            Modify Search
                        </a>
                    </div>';
                }
                ?>

            </div>
        </div>
    </div>

    <!-- Optional Footer snippet -->
    <div class="mt-20 py-8 border-t border-gray-200 text-center text-sm text-gray-500 bg-white">
        &copy; <?php echo date("Y"); ?> BusBook. All rights reserved.
    </div>

</body>
</html>