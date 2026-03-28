<?php
// Script to generate Karnataka Bus Network Data
ini_set('max_execution_time', 300); // Allow 5 minutes just in case

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "bus_booking_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected.<br>";

// 1. Remove Existing Duplicates based on User's Criteria
echo "Removing existing duplicates...<br>";
// First, safely keep one unique row and delete the rest
$delete_dupes = "
DELETE b1 FROM buses b1
JOIN buses b2 
WHERE b1.bus_id > b2.bus_id 
  AND b1.bus_name = b2.bus_name 
  AND b1.source = b2.source 
  AND b1.destination = b2.destination 
  AND b1.departure_time = b2.departure_time
  AND b1.journey_date <=> b2.journey_date;
";
$conn->query($delete_dupes);

// 2. Add Unique Constraint to Prevent Future Duplicates
echo "Applying Unique Constraints...<br>";
// Ensure constraint doesn't already exist to prevent errors
try {
    $conn->query("ALTER TABLE buses DROP INDEX unique_bus_schedule");
} catch (Exception $e) {
    // ignore
}
$add_constraint = "
ALTER TABLE buses 
ADD UNIQUE INDEX unique_bus_schedule (bus_name, source, destination, departure_time, journey_date);
";
if(!$conn->query($add_constraint)){
    echo "Notice: Duplicate constraint might exist or data requires clean up: " . $conn->error . "<br>";
}

// 3. Define the Districts and Hubs for Karnataka
$hubs = [
    'Bengaluru', 'Mysuru', 'Hubballi', 'Mangaluru', 'Kalaburagi', 
    'Belagavi', 'Davangere', 'Udupi', 'Shivamogga', 'Uttara Kannada', 'Karwar'
];

$all_districts = [
    'Bagalkot', 'Ballari', 'Belagavi', 'Bengaluru Rural', 'Bengaluru', 
    'Bidar', 'Chamarajanagar', 'Chikkaballapur', 'Chikkamagaluru', 'Chitradurga', 
    'Mangaluru', 'Davangere', 'Hubballi', 'Gadag', 'Hassan', 
    'Haveri', 'Kalaburagi', 'Kodagu', 'Kolar', 'Koppal', 
    'Mandya', 'Mysuru', 'Raichur', 'Ramanagara', 'Shivamogga', 
    'Tumakuru', 'Udupi', 'Uttara Kannada', 'Karwar', 'Vijayapura', 'Yadgir', 'Vijayanagara'
];

// Clean up duplicate entries between hubs and districts
$all_districts = array_unique($all_districts);

$bus_operators = ['KSRTC Airavat', 'VRL Travels', 'Sugama Tourist', 'SRS Travels', 'Durgamba Motors', 'Sea Bird Tourist', 'KSRTC Express', 'Prajwal Travels', 'Kalyan Travels', 'Ganesh Travels'];
$bus_types = ['AC', 'Non-AC', 'A/C Sleeper', 'Non A/C Sleeper', 'Volvo Multi-Axle'];

function get_realistic_pricing_and_time($source, $dest) {
    // Rough estimate logic based on regions
    $long_routes = ['Kalaburagi', 'Bidar', 'Yadgir', 'Raichur', 'Vijayapura', 'Bagalkot', 'Belagavi', 'Mangaluru', 'Karwar', 'Uttara Kannada'];
    
    $is_s_long = in_array($source, $long_routes);
    $is_d_long = in_array($dest, $long_routes);
    
    // Determine distance factor roughly (1: short, 2: medium, 3: long)
    if(($is_s_long && $dest == 'Bengaluru') || ($is_d_long && $source == 'Bengaluru')) {
        $distance = 3; // long
    } elseif ($source == 'Mysuru' && $dest == 'Bengaluru' || $source == 'Bengaluru' && $dest == 'Mysuru' || $source == 'Udupi' && $dest == 'Mangaluru' || $source == 'Mangaluru' && $dest == 'Udupi') {
        $distance = 1; // short
    } else {
        $distance = 2; // medium
    }

    $buses = [];
    
    // Generate 3 buses per route
    for($i = 0; $i < 3; $i++) {
        $operator = $GLOBALS['bus_operators'][array_rand($GLOBALS['bus_operators'])];
        $type = $GLOBALS['bus_types'][array_rand($GLOBALS['bus_types'])];
        
        if($distance == 1) { // Short (₹150 - ₹400)
            $price = rand(15, 39) * 10;
            // Short routes happen during the day
            $dep_h = rand(5, 18);
            $arr_h = $dep_h + rand(2, 4);
        } elseif($distance == 2) { // Medium (₹400 - ₹900)
            $price = rand(40, 89) * 10;
            // Medium routes can be day or evening
            $dep_h = rand(8, 22);
            $arr_h = ($dep_h + rand(4, 7)) % 24;
        } else { // Long (₹900 - ₹1500)
            $price = rand(90, 150) * 10;
            // Long routes are almost always overnight sleepers
            $dep_h = rand(18, 23);
            $arr_h = ($dep_h + rand(8, 12)) % 24;
        }
        
        // Add premium for AC/Volvo
        if(strpos($type, 'AC') !== false || strpos($type, 'Volvo') !== false) {
            $price += rand(150, 300);
        }
        
        $dep_m = array('00', '15', '30', '45')[array_rand(['00', '15', '30', '45'])];
        $arr_m = array('00', '15', '30', '45')[array_rand(['00', '15', '30', '45'])];
        
        $dep_time = sprintf('%02d:%s:00', $dep_h, $dep_m);
        $arr_time = sprintf('%02d:%s:00', $arr_h, $arr_m);
        $seats = rand(36, 45);
        
        $buses[] = [
            'operator' => $operator,
            'type' => $type,
            'dep_time' => $dep_time,
            'arr_time' => $arr_time,
            'price' => $price,
            'seats' => $seats
        ];
    }
    
    return $buses;
}

echo "Generating buses for April 1 to April 30...<br>";
// Let's gather all unique pairs
$pairs = [];
foreach($hubs as $hub) {
    foreach($all_districts as $district) {
        if($hub != $district) {
            $pairs[] = [$hub, $district];
            $pairs[] = [$district, $hub];
        }
    }
}
// Also make sure hubs connect to hubs explicitly
foreach($hubs as $hub1) {
    foreach($hubs as $hub2) {
        if($hub1 != $hub2) {
            $pairs[] = [$hub1, $hub2];
        }
    }
}

// Remove duplicate pairs
$pairs = array_map("unserialize", array_unique(array_map("serialize", $pairs)));

// Define dates
$dates = [];
for($d = 1; $d <= 30; $d++) {
    $dates[] = sprintf('2026-04-%02d', $d);
}

$total_inserted = 0;
// We will generate the base buses first, then apply across dates
$base_buses_to_insert = [];

foreach($pairs as $pair) {
    $source = $pair[0];
    $dest = $pair[1];
    $buses = get_realistic_pricing_and_time($source, $dest);
    
    foreach($buses as $bus) {
        $base_buses_to_insert[] = [
            'n' => $conn->real_escape_string($bus['operator']),
            't' => $conn->real_escape_string($bus['type']),
            's' => $conn->real_escape_string($source),
            'd' => $conn->real_escape_string($dest),
            'dt' => $bus['dep_time'],
            'at' => $bus['arr_time'],
            'ts' => $bus['seats'],
            'p' => $bus['price']
        ];
    }
}

// Bulk insert using INSERT IGNORE
echo "Starting Bulk Insert. This creates a massive dataset (approx " . (count($base_buses_to_insert) * count($dates)) . " rows). Execution may take 30-60 secs...<br>";
flush();

// Process in chunks to avoid blowing up memory/SQL limits
$chunk_size = 500;
$query_values = [];

foreach($dates as $j_date) {
    foreach($base_buses_to_insert as $b) {
        $query_values[] = "('{$b['n']}', '{$b['t']}', '{$b['s']}', '{$b['d']}', '{$b['dt']}', '{$b['at']}', {$b['ts']}, {$b['p']}, '$j_date')";
        
        if(count($query_values) >= $chunk_size) {
            $val_str = implode(",", $query_values);
            $q = "INSERT IGNORE INTO buses (bus_name, bus_type, source, destination, departure_time, arrival_time, total_seats, price, journey_date) VALUES $val_str";
            $conn->query($q);
            $total_inserted += $conn->affected_rows;
            $query_values = [];
        }
    }
}

// Insert remaining
if(count($query_values) > 0) {
    $val_str = implode(",", $query_values);
    $q = "INSERT IGNORE INTO buses (bus_name, bus_type, source, destination, departure_time, arrival_time, total_seats, price, journey_date) VALUES $val_str";
    $conn->query($q);
    $total_inserted += $conn->affected_rows;
}

echo "Data Generation Complete! Successfully inserted $total_inserted new precise bus schedules for April 1 - April 30 connecting all specified Karnataka nodes.<br>";
?>
