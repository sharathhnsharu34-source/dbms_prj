<?php
session_start();
include("../db/connect.php");

$buses_res = $conn->query("SELECT bus_id, bus_name, source, destination, departure_time, arrival_time FROM buses ORDER BY bus_id DESC");
$buses = [];
if($buses_res) while($r = $buses_res->fetch_assoc()) $buses[] = $r;

$selected_bus_id = isset($_GET['bus_id']) ? intval($_GET['bus_id']) : (count($buses) > 0 ? $buses[0]['bus_id'] : 0);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_boarding' || $action === 'add_dropping') {
        $bus_id = intval($_POST['bus_id']);
        $location = $conn->real_escape_string(trim($_POST['location_name']));
        $time = $conn->real_escape_string($_POST['time']);
        $table = $action === 'add_boarding' ? 'boarding_points' : 'dropping_points';
        
        if($conn->query("INSERT INTO $table (bus_id, location_name, time) VALUES ($bus_id, '$location', '$time')")) {
            header("Location: manage_points.php?bus_id=$bus_id&success=added");
            exit();
        } else {
            $error = "Failed to add point.";
        }
    } elseif ($action === 'delete_point') {
        $id = intval($_POST['id']);
        $type = $_POST['type'];
        $bus_id = intval($_POST['bus_id']);
        $table = $type === 'boarding' ? 'boarding_points' : 'dropping_points';
        
        $conn->query("DELETE FROM $table WHERE id=$id");
        header("Location: manage_points.php?bus_id=$bus_id&success=deleted");
        exit();
    }
}

if(isset($_GET['success'])) {
    if($_GET['success'] === 'added') $success = "Point added successfully.";
    if($_GET['success'] === 'deleted') $success = "Point deleted successfully.";
}

// Fetch current points for the selected bus
$boarding_points = [];
$dropping_points = [];
if($selected_bus_id > 0) {
    $bp_res = $conn->query("SELECT * FROM boarding_points WHERE bus_id=$selected_bus_id ORDER BY time ASC");
    if($bp_res) while($r = $bp_res->fetch_assoc()) $boarding_points[] = $r;
    
    $dp_res = $conn->query("SELECT * FROM dropping_points WHERE bus_id=$selected_bus_id ORDER BY time ASC");
    if($dp_res) while($r = $dp_res->fetch_assoc()) $dropping_points[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Points | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .glass-header {
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <nav class="glass-header sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-[72px] items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-map-location-dot text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-xl tracking-tight text-slate-900 leading-none block">Admin<span class="text-indigo-600">Points</span></span>
                        <span class="text-[0.65rem] uppercase tracking-wider text-slate-500 font-semibold block mt-0.5">Route Stops</span>
                    </div>
                </div>
                <div class="flex items-center gap-1 md:gap-3 flex-wrap pb-2 md:pb-0 justify-end mt-4 md:mt-0">
                    <a href="view_bookings.php" class="flex items-center justify-center gap-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-chart-pie text-xs"></i> <span class="hidden lg:inline">Dashboard</span>
                    </a>
                    <a href="manage_buses.php" class="flex items-center justify-center gap-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-bus text-xs"></i> <span class="hidden lg:inline">Buses</span>
                    </a>

                    <a href="manage_points.php" class="flex items-center justify-center gap-2 bg-indigo-50 text-indigo-600 border border-indigo-200 hover:bg-indigo-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-map-location-dot text-xs"></i> <span class="hidden lg:inline">Points</span>
                    </a>
                    <div class="w-px h-6 bg-slate-200 hidden md:block mx-1"></div>
                    <a href="../index.php" class="flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-md transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-arrow-left text-xs"></i> <span class="hidden sm:inline">Main</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
            
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Manage Boarding & Dropping Points</h1>
                <p class="text-slate-500 font-medium">Configure pickup and drop locations for your fleet.</p>
            </div>

            <?php if($success !== ''): ?>
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span class="font-bold text-sm"><?= $success ?></span>
            </div>
            <?php endif; ?>
            <?php if($error !== ''): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <span class="font-bold text-sm"><?= $error ?></span>
            </div>
            <?php endif; ?>

            <!-- Bus Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
                <form action="" method="GET" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Select Bus Fleet</label>
                        <select name="bus_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            <?php foreach($buses as $bus): ?>
                                <option value="<?= $bus['bus_id'] ?>" <?= $selected_bus_id == $bus['bus_id'] ? 'selected' : '' ?>>
                                    [BUS-<?= str_pad($bus['bus_id'],3,'0',STR_PAD_LEFT) ?>] <?= htmlspecialchars($bus['bus_name']) ?> (<?= htmlspecialchars($bus['source']) ?> → <?= htmlspecialchars($bus['destination']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>

            <?php if($selected_bus_id > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Boarding Points -->
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden">
                    <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100">
                        <h3 class="font-bold text-emerald-800 text-lg flex items-center gap-2"><i class="fa-solid fa-location-dot"></i> Boarding Points</h3>
                    </div>
                    
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <form action="" method="POST" class="flex gap-2 items-end">
                            <input type="hidden" name="action" value="add_boarding">
                            <input type="hidden" name="bus_id" value="<?= $selected_bus_id ?>">
                            <div class="flex-1">
                                <label class="block text-[0.60rem] font-bold text-slate-400 uppercase tracking-widest mb-1">Location Name</label>
                                <input type="text" name="location_name" required placeholder="e.g. Majestic Bus Stand" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[0.60rem] font-bold text-slate-400 uppercase tracking-widest mb-1">Time</label>
                                <input type="time" name="time" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-sm"><i class="fa-solid fa-plus"></i></button>
                        </form>
                    </div>

                    <div class="p-0">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 text-[0.65rem] uppercase tracking-widest font-bold text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-6">Time</th>
                                    <th class="py-3 px-6">Location</th>
                                    <th class="py-3 px-6 text-right">Delete</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-50">
                                <?php if(empty($boarding_points)): ?>
                                <tr><td colspan="3" class="py-6 text-center text-slate-400">No boarding points added.</td></tr>
                                <?php else: ?>
                                    <?php foreach($boarding_points as $p): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-6 font-bold text-emerald-700"><?= date('h:i A', strtotime($p['time'])) ?></td>
                                        <td class="py-3 px-6 font-semibold text-slate-700"><?= htmlspecialchars($p['location_name']) ?></td>
                                        <td class="py-3 px-6 text-right">
                                            <form action="" method="POST" onsubmit="return confirm('Delete this point?');">
                                                <input type="hidden" name="action" value="delete_point">
                                                <input type="hidden" name="type" value="boarding">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="bus_id" value="<?= $selected_bus_id ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 p-1"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Dropping Points -->
                <div class="bg-white rounded-2xl shadow-sm border border-rose-100 overflow-hidden">
                    <div class="bg-rose-50 px-6 py-4 border-b border-rose-100">
                        <h3 class="font-bold text-rose-800 text-lg flex items-center gap-2"><i class="fa-solid fa-location-dot"></i> Dropping Points</h3>
                    </div>
                    
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <form action="" method="POST" class="flex gap-2 items-end">
                            <input type="hidden" name="action" value="add_dropping">
                            <input type="hidden" name="bus_id" value="<?= $selected_bus_id ?>">
                            <div class="flex-1">
                                <label class="block text-[0.60rem] font-bold text-slate-400 uppercase tracking-widest mb-1">Location Name</label>
                                <input type="text" name="location_name" required placeholder="e.g. Swargate" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>
                            <div>
                                <label class="block text-[0.60rem] font-bold text-slate-400 uppercase tracking-widest mb-1">Time</label>
                                <input type="time" name="time" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>
                            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-sm"><i class="fa-solid fa-plus"></i></button>
                        </form>
                    </div>

                    <div class="p-0">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 text-[0.65rem] uppercase tracking-widest font-bold text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-6">Time</th>
                                    <th class="py-3 px-6">Location</th>
                                    <th class="py-3 px-6 text-right">Delete</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-50">
                                <?php if(empty($dropping_points)): ?>
                                <tr><td colspan="3" class="py-6 text-center text-slate-400">No dropping points added.</td></tr>
                                <?php else: ?>
                                    <?php foreach($dropping_points as $p): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-6 font-bold text-rose-700"><?= date('h:i A', strtotime($p['time'])) ?></td>
                                        <td class="py-3 px-6 font-semibold text-slate-700"><?= htmlspecialchars($p['location_name']) ?></td>
                                        <td class="py-3 px-6 text-right">
                                            <form action="" method="POST" onsubmit="return confirm('Delete this point?');">
                                                <input type="hidden" name="action" value="delete_point">
                                                <input type="hidden" name="type" value="dropping">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="bus_id" value="<?= $selected_bus_id ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 p-1"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>

</body>
</html>
