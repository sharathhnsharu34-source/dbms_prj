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
    <title>Bus Ticket Booking | SkylineTransit</title>
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
                    <span class="font-bold text-xl tracking-wide text-gray-900">Skyline<span class="text-red-500">Transit</span></span>
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
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 hover:text-indigo-600 transition font-medium"><i class="fa-solid fa-id-badge mr-2"></i> Profile</a>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-600 transition font-medium"><i class="fa-solid fa-right-from-bracket mr-2"></i> Logout</a>
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
            
            <!-- Dynamic Filter Bar -->
            <div class="flex bg-white/10 backdrop-blur-md rounded-xl p-1 shadow-inner border border-white/10 text-sm font-semibold overflow-x-auto max-w-full no-scrollbar" id="sort-bar">
                <button class="px-6 py-2.5 rounded-lg bg-white text-slate-900 shadow-sm transition transform hover:scale-105 whitespace-nowrap sort-btn" data-sort="price">Cheapest First</button>
                <button class="px-6 py-2.5 rounded-lg text-white hover:bg-white/20 transition whitespace-nowrap sort-btn" data-sort="fastest">Fastest First</button>
                <button class="px-6 py-2.5 rounded-lg text-white hover:bg-white/20 transition whitespace-nowrap sort-btn" data-sort="early">Early Departure</button>
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
                                <input type="checkbox" checked value="AC" class="bus-filter w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">AC</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" checked value="Non-AC" class="bus-filter w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">Non-AC</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" checked value="Sleeper" class="bus-filter w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">Sleeper</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" checked value="Seater" class="bus-filter w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 accent-red-500 transition">
                                <span class="text-gray-600 group-hover:text-gray-900 font-medium">Seater</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content (Bus Cards) -->
            <div class="w-full lg:w-3/4 flex flex-col gap-6" id="bus-results" style="transition: opacity 0.3s ease;">
                
                <?php
                if($result->num_rows>0){
                    while($row=$result->fetch_assoc()){
                        $bus_type = $row['bus_type'];
                        if(empty($bus_type) || trim($bus_type) == '') $bus_type = 'Non-AC';
                        
                        // Count officially booked seats
                        $b_id = $row['bus_id'];
                        $seat_sql = "SELECT seat_number FROM bookings WHERE bus_id='$b_id' AND booking_status != 'Cancelled' AND payment_status != 'Failed'";
                        $seat_res = $conn->query($seat_sql);
                        $hc = 0;
                        if($seat_res && $seat_res->num_rows > 0) {
                            while($srow = $seat_res->fetch_assoc()) {
                                $c_a = explode(',', $srow['seat_number']);
                                foreach($c_a as $s) { if(trim($s) !== '') $hc++; }
                            }
                        }
                        
                        // Count Reserved holds explicitly
                        $hold_sql = "SELECT COUNT(*) as held FROM seats WHERE bus_id='$b_id' AND status='Reserved' AND updated_at >= NOW() - INTERVAL 5 MINUTE";
                        $hold_res = $conn->query($hold_sql);
                        $held = ($hold_res && $hold_res->num_rows > 0) ? $hold_res->fetch_assoc()['held'] : 0;
                        
                        $seats_left = max(0, 32 - $hc - $held);

                        mt_srand($row['bus_id']);
                        $rating = number_format(mt_rand(35, 49) / 10, 1);
                        $reviews = mt_rand(10, 300);
                        mt_srand();
                        
                        // Maintain existing GET logic of book.php while injecting extra purely visual parameters
                        $book_url = "book.php?bus_id=".$row['bus_id']."&src=".urlencode($source)."&dest=".urlencode($destination)."&price=".$row['price']."&type=".urlencode($bus_type)."&dep=".urlencode($row['departure_time'])."&arr=".urlencode($row['arrival_time'])."&bname=".urlencode($row['bus_name']);

                        echo '
                        <!-- Bus Card -->
                        <div class="bg-white rounded-[1.25rem] shadow-sm hover-scale border border-gray-100/80 overflow-hidden flex flex-col md:flex-row group transition-all" data-bus-id="'.$row['bus_id'].'">
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
                                <p class="seat-counter text-xs font-bold '.($seats_left < 5 ? 'text-red-500 bg-red-50 border-red-100' : 'text-emerald-600 bg-emerald-50 border-emerald-100').' mb-5 px-2.5 py-1 rounded w-max self-end border" id="seat_count_'.$row['bus_id'].'">
                                    <i class="fa-solid fa-couch mr-1"></i> <span>'.$seats_left.'</span> Seats left
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
        &copy; <?php echo date("Y"); ?> SkylineTransit. All rights reserved.
    </div>

    <!-- Interactive Search & Live Polling Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const source = "<?php echo addslashes($source); ?>";
            const destination = "<?php echo addslashes($destination); ?>";
            let currentSort = 'price'; 
            
            const resultsContainer = document.getElementById('bus-results');
            const filterCheckboxes = document.querySelectorAll('.bus-filter');
            const sortButtons = document.querySelectorAll('.sort-btn');
            
            filterCheckboxes.forEach(cb => cb.addEventListener('change', fetchBuses));
            
            sortButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    sortButtons.forEach(b => {
                        b.classList.remove('bg-white', 'text-slate-900', 'shadow-sm', 'transform', 'hover:scale-105');
                        b.classList.add('text-white', 'hover:bg-white/20');
                    });
                    const target = e.target;
                    target.classList.remove('text-white', 'hover:bg-white/20');
                    target.classList.add('bg-white', 'text-slate-900', 'shadow-sm', 'transform', 'hover:scale-105');
                    
                    currentSort = target.getAttribute('data-sort');
                    fetchBuses();
                });
            });
            
            function fetchBuses() {
                resultsContainer.style.opacity = '0.5';
                
                const activeFilters = Array.from(filterCheckboxes)
                                           .filter(cb => cb.checked)
                                           .map(cb => cb.value);
                
                const formData = new FormData();
                formData.append('source', source);
                formData.append('destination', destination);
                formData.append('filters', JSON.stringify(activeFilters));
                formData.append('sort', currentSort);
                
                fetch('api/fetch_buses.php', { method: 'POST', body: formData })
                .then(res => res.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                    resultsContainer.style.opacity = '1';
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    resultsContainer.style.opacity = '1';
                });
            }
            
            setInterval(() => {
                const busCards = document.querySelectorAll('[data-bus-id]');
                if(busCards.length === 0) return;
                
                const ids = Array.from(busCards).map(card => card.getAttribute('data-bus-id'));
                const fd = new FormData();
                fd.append('bus_ids', JSON.stringify(ids));
                
                fetch('api/live_seats.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(json => {
                    if(json.success && json.data) {
                        for(const [bId, count] of Object.entries(json.data)) {
                            const counterEl = document.getElementById('seat_count_' + bId);
                            if(counterEl) {
                                counterEl.querySelector('span').innerText = count;
                                if(count < 5) {
                                    counterEl.className = 'seat-counter text-xs font-bold text-red-500 bg-red-50 mb-5 px-2.5 py-1 rounded w-max self-end border border-red-100';
                                } else {
                                    counterEl.className = 'seat-counter text-xs font-bold text-emerald-600 bg-emerald-50 mb-5 px-2.5 py-1 rounded w-max self-end border border-emerald-100';
                                }
                            }
                        }
                    }
                })
                .catch(err => console.error('Live seats poll failed:', err));
            }, 5000); 
        });
    </script>
</body>
</html>