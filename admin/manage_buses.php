<?php
session_start();
include("../db/connect.php");

// Fetch active routes for dropdowns
$routes_res = $conn->query("SELECT * FROM routes ORDER BY source ASC, destination ASC");
$routes = [];
if ($routes_res) {
    while($row = $routes_res->fetch_assoc()) $routes[] = $row;
}

$error = '';
$success = '';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add' || $action === 'edit') {
        $bus_name = $conn->real_escape_string(trim($_POST['bus_name']));
        $bus_type = $conn->real_escape_string(trim($_POST['bus_type']));
        $route_id = intval($_POST['route_id']);
        $dep_time = $conn->real_escape_string($_POST['departure_time']);
        $arr_time = $conn->real_escape_string($_POST['arrival_time']);
        $price = floatval($_POST['price']);
        
        // Lookup Route details to safely duplicate source/destination for legacy support
        $r_query = $conn->query("SELECT source, destination FROM routes WHERE id = $route_id");
        if($r_query && $r_query->num_rows > 0) {
            $r_data = $r_query->fetch_assoc();
            $source = $conn->real_escape_string($r_data['source']);
            $destination = $conn->real_escape_string($r_data['destination']);
            
            if ($action === 'add') {
                // Prevent duplicate bus on same route at same time
                $check = $conn->query("SELECT bus_id FROM buses WHERE bus_name='$bus_name' AND route_id=$route_id AND departure_time='$dep_time'");
                if($check->num_rows > 0) {
                    $error = "A bus with this name already exists on this route at this time.";
                } else {
                    $conn->query("INSERT INTO buses (bus_name, source, destination, departure_time, arrival_time, price, bus_type, route_id) 
                                  VALUES ('$bus_name', '$source', '$destination', '$dep_time', '$arr_time', $price, '$bus_type', $route_id)");
                    header("Location: manage_buses.php?success=added");
                    exit();
                }
            } else {
                $bus_id = intval($_POST['bus_id']);
                $conn->query("UPDATE buses SET bus_name='$bus_name', source='$source', destination='$destination', 
                              departure_time='$dep_time', arrival_time='$arr_time', price=$price, bus_type='$bus_type', route_id=$route_id 
                              WHERE bus_id=$bus_id");
                header("Location: manage_buses.php?success=updated");
                exit();
            }
        } else {
            $error = "Invalid Route Selected.";
        }
    } elseif ($action === 'delete') {
        $bus_id = intval($_POST['bus_id']);
        // Check if there are active bookings
        $chk = $conn->query("SELECT count(*) as c FROM bookings WHERE bus_id=$bus_id");
        $bk = $chk->fetch_assoc();
        if ($bk['c'] > 0) {
            $error = "Cannot delete a bus that has registered bookings.";
        } else {
            $conn->query("DELETE FROM buses WHERE bus_id=$bus_id");
            header("Location: manage_buses.php?success=deleted");
            exit();
        }
    }
}

if(isset($_GET['success'])) $success = "Action completed successfully.";

// Fetch Buses
$buses_result = $conn->query("
    SELECT b.*, r.distance 
    FROM buses b 
    LEFT JOIN routes r ON b.route_id = r.id 
    ORDER BY b.bus_id DESC
");
$buses = [];
if($buses_result) {
    while($row = $buses_result->fetch_assoc()) $buses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Buses | SkylineTransit Admin</title>
    <!-- Tailwind CSS for Modern Styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
        .modal { display: none; align-items: center; justify-content: center; }
        .modal.active { display: flex; animation: fadeIn 0.2s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Window -->
    <nav class="glass-header sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-[72px] items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-bus text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-xl tracking-tight text-slate-900 leading-none block">Admin<span class="text-blue-600">Buses</span></span>
                        <span class="text-[0.65rem] uppercase tracking-wider text-slate-500 font-semibold block mt-0.5">Fleet Management</span>
                    </div>
                </div>
                <!-- Unified Navigation -->
                <div class="flex items-center gap-1 md:gap-3 flex-wrap pb-2 md:pb-0 justify-end mt-4 md:mt-0">
                    <a href="view_bookings.php" class="flex items-center justify-center gap-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-chart-pie text-xs"></i> 
                        <span class="hidden lg:inline">Dashboard</span>
                    </a>
                    <a href="manage_buses.php" class="flex items-center justify-center gap-2 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
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
    <main class="flex-grow pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
            
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Manage Fleet</h1>
                    <p class="text-slate-500 font-medium">Add, update, and manage bus instances and their schedule allocations.</p>
                </div>
                <button onclick="openModal('addModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus text-sm"></i> Add New Bus
                </button>
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

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest">Bus Info</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest">Route Bind</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest">Timings</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest text-center">Price</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-50">
                            <?php if(empty($buses)): ?>
                            <tr><td colspan="5" class="py-8 text-center text-slate-400 font-medium font-sans">No buses available. Add your first bus!</td></tr>
                            <?php else: ?>
                                <?php foreach($buses as $bus): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($bus['bus_name']) ?></p>
                                        <p class="text-[0.65rem] font-bold px-2 py-0.5 mt-1 rounded bg-slate-100 text-slate-500 border border-slate-200 inline-block uppercase tracking-wider"><?= htmlspecialchars($bus['bus_type']) ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2 bg-slate-100/80 px-2.5 py-1 rounded-md inline-flex border border-slate-200/60">
                                            <span class="text-slate-800 font-bold text-xs"><?= htmlspecialchars($bus['source']) ?></span>
                                            <i class="fa-solid fa-arrow-right text-slate-400 text-[10px]"></i>
                                            <span class="text-slate-800 font-bold text-xs"><?= htmlspecialchars($bus['destination']) ?></span>
                                        </div>
                                        <?php if(!$bus['route_id']): ?>
                                            <p class="text-[0.6rem] text-rose-500 font-bold mt-1 ml-1"><i class="fa-solid fa-triangle-exclamation"></i> Legacy / Unbound</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800 text-sm"><?= date('h:i A', strtotime($bus['departure_time'])) ?> <span class="text-slate-400 text-xs font-normal mx-0.5">to</span> <?= date('h:i A', strtotime($bus['arrival_time'])) ?></p>
                                    </td>
                                    <td class="py-4 px-6 font-bold text-emerald-600 text-center text-base">₹<?= $bus['price'] ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="openEditModal(<?= $bus['bus_id'] ?>, '<?= htmlspecialchars(addslashes($bus['bus_name'])) ?>', '<?= htmlspecialchars(addslashes($bus['bus_type'])) ?>', <?= $bus['route_id'] ?? 0 ?>, '<?= $bus['departure_time'] ?>', '<?= $bus['arrival_time'] ?>', <?= $bus['price'] ?>)" class="bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600 px-3 py-1.5 rounded-lg font-bold text-xs transition-colors border border-slate-200">Edit</button>
                                            
                                            <form action="" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this bus?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="bus_id" value="<?= $bus['bus_id'] ?>">
                                                <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-lg font-bold text-xs transition-colors border border-red-100">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Add Bus -->
    <div id="addModal" class="modal fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Add New Bus</h3>
                <button onclick="closeModal('addModal')" class="text-slate-400 hover:text-red-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="" method="POST" class="p-6">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Bus Name / Travel Agency</label>
                        <input type="text" name="bus_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Coach Type</label>
                        <select name="bus_type" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="A/C Sleeper">A/C Sleeper</option>
                            <option value="Non A/C Sleeper">Non A/C Sleeper</option>
                            <option value="A/C Seater">A/C Seater</option>
                            <option value="Non A/C Seater">Non A/C Seater</option>
                            <option value="Volvo Multi-Axle">Volvo Multi-Axle</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Bind Route</label>
                        <select name="route_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">-- Select Route --</option>
                            <?php foreach($routes as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['source'].' → '.$r['destination']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Departure Time</label>
                        <input type="time" name="departure_time" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 pl-4 py-2">
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Arrival Time</label>
                        <input type="time" name="arrival_time" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 pl-4 py-2">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Ticket Price / Fare (₹)</label>
                    <input type="number" name="price" step="0.01" min="1" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" onclick="closeModal('addModal')" class="px-5 py-2 rounded-xl text-slate-500 font-bold text-sm bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold text-sm bg-blue-600 hover:bg-blue-700 shadow-md transition-colors">Save Bus</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Modal: Edit Bus -->
    <div id="editModal" class="modal fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Update Bus</h3>
                <button onclick="closeModal('editModal')" class="text-slate-400 hover:text-red-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="" method="POST" class="p-6">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="bus_id" id="e_bus_id">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Bus Name / Travel Agency</label>
                        <input type="text" name="bus_name" id="e_bus_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Coach Type</label>
                        <select name="bus_type" id="e_bus_type" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="A/C Sleeper">A/C Sleeper</option>
                            <option value="Non A/C Sleeper">Non A/C Sleeper</option>
                            <option value="A/C Seater">A/C Seater</option>
                            <option value="Non A/C Seater">Non A/C Seater</option>
                            <option value="Volvo Multi-Axle">Volvo Multi-Axle</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Bind Route</label>
                        <select name="route_id" id="e_route_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">-- Select Route --</option>
                            <?php foreach($routes as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['source'].' → '.$r['destination']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Departure Time</label>
                        <input type="time" name="departure_time" id="e_departure_time" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 pl-4 py-2">
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Arrival Time</label>
                        <input type="time" name="arrival_time" id="e_arrival_time" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 pl-4 py-2">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Ticket Price / Fare (₹)</label>
                    <input type="number" name="price" id="e_price" step="0.01" min="1" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" onclick="closeModal('editModal')" class="px-5 py-2 rounded-xl text-slate-500 font-bold text-sm bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold text-sm bg-blue-600 hover:bg-blue-700 shadow-md transition-colors">Update Bus</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        function openEditModal(bus_id, name, type, route_id, dep, arr, price) {
            document.getElementById('e_bus_id').value = bus_id;
            document.getElementById('e_bus_name').value = name;
            document.getElementById('e_bus_type').value = type;
            document.getElementById('e_route_id').value = route_id;
            document.getElementById('e_departure_time').value = dep;
            document.getElementById('e_arrival_time').value = arr;
            document.getElementById('e_price').value = price;
            openModal('editModal');
        }
    </script>
</body>
</html>
