<?php
session_start();
include("../db/connect.php");

$source = isset($_POST['source']) ? $conn->real_escape_string($_POST['source']) : '';
$destination = isset($_POST['destination']) ? $conn->real_escape_string($_POST['destination']) : '';
$filters = isset($_POST['filters']) ? json_decode($_POST['filters'], true) : [];
$sort = isset($_POST['sort']) ? $_POST['sort'] : '';

if(empty($source) || empty($destination)) {
    echo '<div class="col-span-full text-center py-10 text-red-500 font-bold">Invalid parameters.</div>';
    exit;
}

$sql = "SELECT * FROM buses WHERE bus_id IN (SELECT MIN(bus_id) FROM buses WHERE source='$source' AND destination='$destination' GROUP BY bus_name, source, destination, departure_time)";
$result = $conn->query($sql);

$buses = [];

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $bus_type = $row['bus_type'];
        if(empty($bus_type) || trim($bus_type) == '') $bus_type = 'Non-AC';
        
        // Count confirmed active bookings logically mapped via comma-string
        $b_id = $row['bus_id'];
        $seat_sql = "SELECT seat_number FROM bookings WHERE bus_id='$b_id' AND booking_status != 'Cancelled' AND payment_status != 'Failed'";
        $seat_res = $conn->query($seat_sql);
        $booked_count = 0;
        if($seat_res && $seat_res->num_rows > 0) {
            while($srow = $seat_res->fetch_assoc()) {
                $count_arr = explode(',', $srow['seat_number']);
                foreach($count_arr as $s) {
                    if(trim($s) !== '') $booked_count++;
                }
            }
        }
        
        // Count officially Reserved holds within the last 5 minutes (300 seconds) securely
        $hold_sql = "SELECT COUNT(*) as held FROM seats WHERE bus_id='$b_id' AND status='Reserved' AND updated_at >= NOW() - INTERVAL 5 MINUTE";
        $hold_res = $conn->query($hold_sql);
        $held_count = ($hold_res && $hold_res->num_rows > 0) ? $hold_res->fetch_assoc()['held'] : 0;
        
        // Current actual Availability (Hardcoded standard 32 capacity temporarily)
        $total_capacity = 32;
        // Or if total_seats is populated properly: $total_capacity = $row['total_seats'] > 0 ? $row['total_seats'] : 32;
        $seats_left = max(0, $total_capacity - $booked_count - $held_count);
        
        // Generate pseudo-visual properties reliably reproducing search_bus visual parity gracefully
        mt_srand($row['bus_id']); // Seed guarantees persistent fake review constants matching UI consistently
        $rating = number_format(mt_rand(35, 49) / 10, 1);
        $reviews = mt_rand(10, 300);
        mt_srand(); // Release purely to avoid locking all future randomness unpredictably
        
        // Execute Filtering Checks securely
        $passes_filter = true;
        if(!empty($filters)) {
            $type_upper = strtoupper($bus_type);
            
            $is_ac = (strpos($type_upper, 'AC') !== false && strpos($type_upper, 'NON-AC') === false && strpos($type_upper, 'NON AC') === false);
            $is_non_ac = !$is_ac;
            
            $is_sleeper = (strpos($type_upper, 'SLEEPER') !== false);
            $is_seater = !$is_sleeper; 
            
            $sel_ac = in_array('AC', $filters);
            $sel_non_ac = in_array('Non-AC', $filters);
            $sel_sleeper = in_array('Sleeper', $filters);
            $sel_seater = in_array('Seater', $filters);
            
            $pass_ac = true;
            if($sel_ac || $sel_non_ac) {
                $pass_ac = false;
                if($sel_ac && $is_ac) $pass_ac = true;
                if($sel_non_ac && $is_non_ac) $pass_ac = true;
            }
            
            $pass_seat = true;
            if($sel_sleeper || $sel_seater) {
                $pass_seat = false;
                if($sel_sleeper && $is_sleeper) $pass_seat = true;
                if($sel_seater && $is_seater) $pass_seat = true;
            }
            
            $passes_filter = ($pass_ac && $pass_seat);
        }
        
        if($passes_filter) {
            $duration = strtotime($row['arrival_time']) - strtotime($row['departure_time']);
            if($duration < 0) $duration += 86400; // overnight boundary crossing logic safely
            
            $buses[] = [
                'row' => $row,
                'type' => $bus_type,
                'seats' => $seats_left,
                'rating' => $rating,
                'reviews' => $reviews,
                'duration' => $duration
            ];
        }
    }
}

// Sorting logic
if($sort == 'price') {
    usort($buses, function($a, $b) { return $a['row']['price'] - $b['row']['price']; });
} elseif($sort == 'fastest') {
    usort($buses, function($a, $b) { return $a['duration'] - $b['duration']; });
} elseif($sort == 'early') {
    usort($buses, function($a, $b) { return strtotime($a['row']['departure_time']) - strtotime($b['row']['departure_time']); });
}

// Visual HTML rendering gracefully
if(count($buses) > 0) {
    foreach($buses as $bus) {
        $row = $bus['row'];
        $bus_type = $bus['type'];
        $seats_left = $bus['seats'];
        $rating = $bus['rating'];
        $reviews = $bus['reviews'];
        
        $book_url = "book.php?bus_id=".$row['bus_id']."&src=".urlencode($source)."&dest=".urlencode($destination)."&price=".$row['price']."&type=".urlencode($bus_type)."&dep=".urlencode($row['departure_time'])."&arr=".urlencode($row['arrival_time'])."&bname=".urlencode($row['bus_name']);
        
        // Visuals
        $seat_badge = $seats_left < 5 ? 'text-red-500 bg-red-50 border-red-100' : 'text-emerald-600 bg-emerald-50 border-emerald-100';

        // Duration Formatting
        $h = floor($bus['duration'] / 3600);
        $m = floor(($bus['duration'] % 3600) / 60);
        $dur_str = sprintf("%02dh %02dm", $h, $m);
        
        echo '
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
                    <p class="text-[0.7rem] text-slate-400 mb-2 font-medium tracking-wide"><i class="fa-regular fa-clock mr-1"></i>'.$dur_str.'</p>
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
                <p class="text-xs text-slate-400 mb-0.5 line-through font-medium">₹'.($row['price'] + 250).'</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mb-3 tracking-tight">₹'.$row['price'].'</h3>
                <p class="seat-counter text-xs font-bold '.$seat_badge.' mb-5 px-2.5 py-1 rounded w-max self-end border" id="seat_count_'.$row['bus_id'].'">
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
    <div class="bg-white rounded-[2rem] p-16 text-center shadow-sm border border-gray-100 flex flex-col items-center w-full">
        <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mb-6 border-8 border-white shadow-sm">
            <i class="fa-solid fa-bus-simple text-[4rem] text-slate-300"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-3">No Buses Found</h2>
        <p class="text-slate-500 mb-8 max-w-md leading-relaxed text-sm">We couldn\'t find any buses seamlessly matching your requested filters. Try clearing your filters to see more results temporarily.</p>
    </div>';
}
?>
