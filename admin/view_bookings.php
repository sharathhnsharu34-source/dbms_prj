<?php
session_start();
include("../db/connect.php");

// Fetch statistics for summary
$stats_query = "
SELECT 
    (SELECT COUNT(DISTINCT bus_id) FROM buses) AS total_buses, 
    (SELECT COUNT(booking_id) FROM bookings WHERE payment_status = 'Success' AND booking_status = 'Active') AS total_bookings,
    (SELECT SUM(final_price) FROM bookings WHERE payment_status = 'Success' AND booking_status = 'Active') AS total_revenue
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
$total_buses = $stats['total_buses'] ?? 0;
$total_bookings = $stats['total_bookings'] ?? 0;
$total_revenue = $stats['total_revenue'] ?? 0;

// Fetch unique buses that have bookings, along with details
$bus_query = "
SELECT DISTINCT buses.bus_id, buses.bus_name, buses.source, buses.destination, buses.departure_time, buses.arrival_time
FROM buses
JOIN bookings ON buses.bus_id = bookings.bus_id
ORDER BY buses.bus_name ASC
";
$bus_result = $conn->query($bus_query);

$buses = [];
if ($bus_result) {
    while($bus = $bus_result->fetch_assoc()){
        // Fetch bookings for this bus
        $booking_query = "SELECT * FROM bookings WHERE bus_id = " . $bus['bus_id'] . " ORDER BY seat_number ASC";
        $b_result = $conn->query($booking_query);
        $bus_bookings = [];
        if ($b_result) {
            while($row = $b_result->fetch_assoc()){
                $bus_bookings[] = $row;
            }
        }
        $bus['bookings'] = $bus_bookings;
        $buses[] = $bus;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | View Bookings</title>
    <!-- Tailwind CSS for Modern Styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #f8fafc; 
        }
        
        /* Smooth transitions */
        .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; border: 1px solid rgba(0,0,0,0.05); }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); }
        .table-row-hover:hover { background-color: #f8fafc; transition: background-color 0.2s ease; }
        
        /* Custom scrollbar for horizontal overflow */
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Badges */
        .badge-ac { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-nonac { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-avail { background-color: #f0fdf4; color: #166534; }
        .badge-full { background-color: #fef2f2; color: #991b1b; }
        
        /* Glass Header Effect */
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Window -->
    <nav class="glass-header sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-[72px] items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-md transform hover:scale-105 transition-transform">
                        <i class="fa-solid fa-bus text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-xl tracking-tight text-slate-900 leading-none block">Admin<span class="text-blue-600">Dashboard</span></span>
                        <span class="text-[0.65rem] uppercase tracking-wider text-slate-500 font-semibold block mt-0.5">Booking Management System</span>
                    </div>
                </div>
                <div class="flex items-center gap-1 md:gap-3 flex-wrap pb-2 md:pb-0 justify-end mt-4 md:mt-0">
                    <a href="view_bookings.php" class="flex items-center justify-center gap-2 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-chart-pie text-xs"></i> 
                        <span class="hidden lg:inline">Dashboard</span>
                    </a>
                    <a href="manage_buses.php" class="flex items-center justify-center gap-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-bus text-xs"></i> 
                        <span class="hidden lg:inline">Buses</span>
                    </a>
                    <a href="manage_routes.php" class="flex items-center justify-center gap-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-route text-xs"></i> 
                        <span class="hidden lg:inline">Routes</span>
                    </a>
                    <div class="w-px h-6 bg-slate-200 hidden md:block mx-1"></div>
                    <a href="../index.php" class="flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-md transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-arrow-left text-xs"></i> 
                        <span class="hidden sm:inline">Main</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
            
            <!-- System Overview header -->
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">System Overview</h1>
                    <p class="text-slate-500 font-medium">Monitor active buses and passenger bookings in real-time.</p>
                </div>
                <div class="text-sm font-semibold text-slate-500 bg-white border border-slate-200 px-4 py-2 rounded-lg shadow-sm">
                    <i class="fa-regular fa-calendar mr-2 text-blue-500"></i> <?= date('l, F j, Y') ?>
                </div>
            </div>

            <!-- Summary Cards section (Bonus Feature) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
                
                <div class="bg-white rounded-[1rem] p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex items-center gap-4 card-hover">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0 shadow-inner">
                        <i class="fa-solid fa-bus-simple text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[0.8rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Active Buses</p>
                        <h3 class="text-2xl font-extrabold text-slate-800 leading-none"><?= $total_buses ?></h3>
                    </div>
                </div>

                <div class="bg-white rounded-[1rem] p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex items-center gap-4 card-hover">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0 shadow-inner">
                        <i class="fa-solid fa-indian-rupee-sign text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[0.8rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Revenue</p>
                        <h3 class="text-2xl font-extrabold text-slate-800 leading-none">₹<?= number_format($total_revenue) ?></h3>
                    </div>
                </div>
                
                <div class="bg-white rounded-[1rem] p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex items-center gap-4 card-hover">
                    <div class="w-14 h-14 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 shrink-0 shadow-inner">
                        <i class="fa-solid fa-ticket-simple text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[0.8rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Bookings</p>
                        <h3 class="text-2xl font-extrabold text-slate-800 leading-none"><?= $total_bookings ?></h3>
                    </div>
                </div>

                <div class="bg-white rounded-[1rem] p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex items-center gap-4 card-hover relative overflow-hidden">
                    <div class="absolute right-0 top-0 bottom-0 w-2 bg-gradient-to-b from-amber-400 to-orange-500"></div>
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 shrink-0 shadow-inner">
                        <i class="fa-solid fa-chart-pie text-2xl"></i>
                    </div>
                    <div class="w-full">
                        <p class="text-[0.8rem] font-bold text-slate-400 uppercase tracking-wider mb-1 flex justify-between">Occupancy <span class="text-amber-500"><?= ($total_buses > 0) ? round(($total_bookings / ($total_buses * 40)) * 100) : 0 ?>%</span></p>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                            <div class="bg-amber-500 h-1.5 rounded-full" style="width: <?= ($total_buses > 0) ? min(100, round(($total_bookings / ($total_buses * 40)) * 100)) : 0 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Filtering & Search Tools -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row gap-3 justify-between items-center z-10 relative">
                
                <div class="relative w-full md:w-2/5 md:ml-2">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-4 py-3 bg-transparent border-0 text-sm focus:ring-0 placeholder-slate-400 font-medium text-slate-700 outline-none" placeholder="Search by passenger name or bus name...">
                </div>

                <div class="w-px h-8 bg-slate-200 hidden md:block"></div>

                <div class="flex flex-wrap gap-2 w-full md:w-auto md:mr-2 pb-2 md:pb-0 px-2 md:px-0 justify-end">
                    
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-van-utilities text-slate-400 text-xs"></i>
                        </div>
                        <select id="typeFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block py-2.5 pl-9 pr-8 outline-none transition cursor-pointer appearance-none">
                            <option value="all">Bus Type: All</option>
                            <option value="ac">Type: AC Only</option>
                            <option value="non-ac">Type: Non-AC</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-location-dot text-slate-400 text-xs"></i>
                        </div>
                        <select id="routeFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block py-2.5 pl-9 pr-8 outline-none transition cursor-pointer appearance-none max-w-[160px] truncate">
                            <option value="all">Route: All</option>
                            <?php 
                                // Generate unique route options based on the returned buses array
                                $unique_routes = [];
                                foreach($buses as $bus) {
                                    $route_key = $bus['source'] . ' to ' . $bus['destination'];
                                    if(!in_array($route_key, $unique_routes)) {
                                        $unique_routes[] = $route_key;
                                    }
                                }
                                foreach($unique_routes as $route) {
                                    echo '<option value="'.htmlspecialchars(strtolower($route)).'">'.htmlspecialchars($route).'</option>';
                                }
                            ?>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>

                </div>
            </div>

            <!-- Main Bus Booking List -->
            <div class="space-y-6" id="busList">

                <?php if(empty($buses)): ?>
                    <div class="text-center py-20 bg-white rounded-[1.5rem] shadow-sm border border-slate-200/60">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-5 mx-auto border-4 border-white shadow-sm">
                            <i class="fa-solid fa-clipboard-list text-4xl text-slate-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 tracking-tight">No Active Bookings</h3>
                        <p class="text-slate-500 mt-2 max-w-sm mx-auto text-sm">There are currently no buses with active bookings in the system right now.</p>
                    </div>
                <?php else: ?>
                    
                    <?php 
                    // Dynamic card generation
                    foreach($buses as $index => $bus): 
                        $bus_id = $bus['bus_id'];
                        $bookings = $bus['bookings'];
                        $booking_count = count($bookings);
                        
                        // Derived or mock attributes
                        $total_seats = 40; // Default hypothetical capacity
                        $available_seats = max(0, $total_seats - $booking_count);
                        
                        // Derived AC / Non-AC 
                        $is_ac = ($bus_id % 2 == 0); 
                        $type_label = $is_ac ? 'AC' : 'Non-AC';
                        $type_class = $is_ac ? 'badge-ac' : 'badge-nonac';
                        
                        $dep_time = !empty($bus['departure_time']) ? date('h:i A', strtotime($bus['departure_time'])) : 'N/A';
                        $arr_time = !empty($bus['arrival_time']) ? date('h:i A', strtotime($bus['arrival_time'])) : 'N/A';
                        $route_idstr = htmlspecialchars(strtolower($bus['source'] . ' to ' . $bus['destination']));
                    ?>
                    
                    <!-- Bus Card Component Wrapper -->
                    <div class="bg-white rounded-[1.25rem] shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow bus-item" 
                         data-bus-name="<?= strtolower(htmlspecialchars($bus['bus_name'])) ?>" 
                         data-route="<?= $route_idstr ?>"
                         data-type="<?= strtolower($type_label) ?>">
                        
                        <!-- Header Section of Card -->
                        <div class="bg-slate-50/50 p-5 md:p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-5 border-b border-slate-100">
                            
                            <!-- Identity & Route info -->
                            <div class="flex items-start md:items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-blue-600 shrink-0 hidden sm:flex relative">
                                    <div class="absolute inset-0 bg-blue-500/10 rounded-2xl"></div>
                                    <i class="fa-solid fa-bus text-2xl relative z-10 text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-1.5 flex-wrap">
                                        <h2 class="text-xl font-extrabold text-slate-800 tracking-tight"><?= htmlspecialchars($bus['bus_name']) ?></h2>
                                        <span class="text-[0.65rem] font-bold px-2 py-0.5 rounded uppercase tracking-wider <?= $type_class ?>"><?= $type_label ?></span>
                                        <span class="text-[0.65rem] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-500 border border-slate-200 font-mono">ID: #<?= $bus_id ?></span>
                                    </div>
                                    
                                    <div class="flex items-center text-slate-500 text-sm font-medium flex-wrap gap-y-2 gap-x-3">
                                        <div class="flex items-center gap-2 bg-slate-100/80 px-2.5 py-1 rounded-md">
                                            <span class="text-slate-800 font-bold"><?= htmlspecialchars($bus['source']) ?></span>
                                            <i class="fa-solid fa-arrow-right text-slate-400 text-[10px]"></i>
                                            <span class="text-slate-800 font-bold"><?= htmlspecialchars($bus['destination']) ?></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 px-1 relative">
                                            <span class="absolute -left-1.5 text-slate-300">|</span>
                                            <i class="fa-regular fa-clock text-blue-500"></i> 
                                            <span class="text-slate-600 tracking-wide"><?= $dep_time ?> <span class="text-slate-400 text-xs mx-1">to</span> <?= $arr_time ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Metrics/Status Pills -->
                            <div class="flex items-stretch gap-3 w-full md:w-auto overflow-x-auto pb-2 md:pb-0 hide-scrollbar pt-3 md:pt-0 border-t md:border-0 border-slate-100">
                                
                                <div class="bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm min-w-[110px] text-center">
                                    <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Seats Filled</p>
                                    <p class="text-lg font-extrabold text-slate-800 leading-none"><?= $booking_count ?> <span class="text-xs text-slate-400 font-medium">/ <?= $total_seats ?></span></p>
                                </div>
                                
                                <div class="bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm min-w-[110px] text-center flex flex-col justify-center items-center">
                                    <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-wider mb-1">Status</p>
                                    <?php if($available_seats == 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 min-w-[70px] justify-center rounded text-[10px] font-bold badge-full uppercase tracking-wider">
                                            Full
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 min-w-[70px] justify-center rounded text-[10px] font-bold badge-avail uppercase tracking-wider">
                                            <?= $available_seats ?> Available
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                            </div>
                        </div>

                        <!-- Table Section corresponding to this bus -->
                        <div class="p-0 overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse min-w-[650px]">
                                <thead>
                                    <tr class="bg-white border-b border-slate-100">
                                        <th class="py-3.5 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest w-[20%]">Booking Ref</th>
                                        <th class="py-3.5 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest w-[50%]">Passenger Details</th>
                                        <th class="py-3.5 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest w-[30%]">Allotted Seat</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-slate-50">
                                    <?php foreach($bookings as $row): ?>
                                    <tr class="table-row-hover transition-colors passenger-row" data-passenger-name="<?= strtolower(htmlspecialchars($row['passenger_name'])) ?>">
                                        
                                        <td class="py-4 px-6">
                                            <span class="inline-block px-2 py-1 rounded-md bg-slate-50 border border-slate-200 text-slate-600 font-mono text-xs font-bold tracking-wider">
                                                #<?= str_pad($row['booking_id'], 5, '0', STR_PAD_LEFT) ?>
                                            </span>
                                        </td>
                                        
                                        <td class="py-4 px-6 font-medium">
                                            <div class="flex items-center gap-3.5">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 text-white shadow-sm flex items-center justify-center text-xs font-bold ring-2 ring-white">
                                                    <?= strtoupper(substr($row['passenger_name'], 0, 1)) ?>
                                                </div>
                                                <span class="text-slate-800 font-bold"><?= htmlspecialchars($row['passenger_name']) ?></span>
                                            </div>
                                        </td>
                                        
                                        <td class="py-4 px-6">
                                            <div class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-lg shadow-sm">
                                                <i class="fa-solid fa-couch text-emerald-500 text-xs"></i>
                                                <span class="text-emerald-700 font-bold text-sm">S-<?= htmlspecialchars($row['seat_number']) ?></span>
                                            </div>
                                        </td>

                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- Footer Area -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm text-slate-500 font-medium font-sans">
                &copy; <?= date("Y") ?> Bus Admin Dashboard. Built for premium operations.
            </p>
        </div>
    </footer>

    <!-- Smart Client-Side Filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const typeFilter = document.getElementById('typeFilter');
            const routeFilter = document.getElementById('routeFilter');
            const busItems = document.querySelectorAll('.bus-item');

            function filterDashboard() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const typeValue = typeFilter.value.toLowerCase();
                const routeValue = routeFilter.value.toLowerCase();

                let activeVisibleCount = 0;

                busItems.forEach(bus => {
                    const busName = bus.getAttribute('data-bus-name');
                    const route = bus.getAttribute('data-route');
                    const type = bus.getAttribute('data-type');
                    
                    // Route matches condition
                    let matchesRoute = (routeValue === 'all') || (route === routeValue);
                    let matchesType = (typeValue === 'all') || (type === typeValue);
                    
                    let busMatch = (busName.includes(searchTerm));
                    
                    // Sub-filtering check within passenger rows
                    const passengerRows = bus.querySelectorAll('.passenger-row');
                    let passengerMatchFound = false;
                    
                    passengerRows.forEach(row => {
                        const passengerName = row.getAttribute('data-passenger-name');
                        if (passengerName.includes(searchTerm)) {
                            passengerMatchFound = true;
                            row.style.display = ''; // highlight mode 
                        } else {
                            if (searchTerm !== '' && !busMatch) {
                                row.style.display = 'none'; // hide if not searching parent
                            } else {
                                row.style.display = ''; // restore
                            }
                        }
                    });

                    // General bus search validation
                    if (!busMatch && passengerMatchFound) {
                        busMatch = true; // passenger match keeps bus card visible
                    }
                    if (searchTerm === '') {
                        busMatch = true; // no search word means full match assumption
                    }

                    if (busMatch && matchesType && matchesRoute) {
                        bus.style.display = '';
                        bus.style.opacity = '1';
                        bus.style.transform = 'translateY(0)';
                        activeVisibleCount++;
                    } else {
                        bus.style.display = 'none';
                        bus.style.opacity = '0';
                    }
                });
            }

            // Bind listeners for real-time evaluation
            ['input', 'change', 'keyup'].forEach(eventType => {
                searchInput.addEventListener(eventType, filterDashboard);
                typeFilter.addEventListener(eventType, filterDashboard);
                routeFilter.addEventListener(eventType, filterDashboard);
            });
        });
    </script>
</body>
</html>