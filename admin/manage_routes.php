<?php
session_start();
include("../db/connect.php");

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $source = $conn->real_escape_string(trim($_POST['source']));
            $destination = $conn->real_escape_string(trim($_POST['destination']));
            $distance = intval($_POST['distance']);
            $base_fare = floatval($_POST['base_fare']);
            
            $conn->query("INSERT INTO routes (source, destination, distance, base_fare) VALUES ('$source', '$destination', $distance, $base_fare)");
            header("Location: manage_routes.php?success=added");
            exit();
        } elseif ($action === 'edit') {
            $id = intval($_POST['route_id']);
            $source = $conn->real_escape_string(trim($_POST['source']));
            $destination = $conn->real_escape_string(trim($_POST['destination']));
            $distance = intval($_POST['distance']);
            $base_fare = floatval($_POST['base_fare']);
            
            $conn->query("UPDATE routes SET source='$source', destination='$destination', distance=$distance, base_fare=$base_fare WHERE id=$id");
            header("Location: manage_routes.php?success=updated");
            exit();
        } elseif ($action === 'delete') {
            $id = intval($_POST['route_id']);
            $conn->query("DELETE FROM routes WHERE id=$id");
            $conn->query("UPDATE buses SET route_id = NULL WHERE route_id=$id");
            header("Location: manage_routes.php?success=deleted");
            exit();
        }
    }
}

// Fetch all routes
$routes_result = $conn->query("SELECT * FROM routes ORDER BY source ASC, destination ASC");
$routes = [];
if($routes_result) {
    while($row = $routes_result->fetch_assoc()) {
        $routes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Routes | Admin</title>
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
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-route text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-xl tracking-tight text-slate-900 leading-none block">Admin<span class="text-indigo-600">Routes</span></span>
                        <span class="text-[0.65rem] uppercase tracking-wider text-slate-500 font-semibold block mt-0.5">Route Configuration</span>
                    </div>
                </div>
                <!-- Unified Navigation -->
                <div class="flex items-center gap-1 md:gap-3 flex-wrap pb-2 md:pb-0 justify-end mt-4 md:mt-0">
                    <a href="view_bookings.php" class="flex items-center justify-center gap-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-chart-pie text-xs"></i> 
                        <span class="hidden lg:inline">Dashboard</span>
                    </a>
                    <a href="manage_buses.php" class="flex items-center justify-center gap-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
                        <i class="fa-solid fa-bus text-xs"></i> 
                        <span class="hidden lg:inline">Buses</span>
                    </a>
                    <a href="manage_routes.php" class="flex items-center justify-center gap-2 bg-indigo-50 text-indigo-600 border border-indigo-200 hover:bg-indigo-100 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm">
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
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Manage Routes</h1>
                    <p class="text-slate-500 font-medium">Add, update, and manage bus routing logic and base fares.</p>
                </div>
                <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus text-sm"></i> Add New Route
                </button>
            </div>

            <?php if(isset($_GET['success'])): ?>
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span class="font-bold text-sm">Action completed successfully!</span>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest">ID</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest">Source Route</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest">Destination</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest text-center">Distance (KM)</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest text-center">Fares (₹)</th>
                                <th class="py-4 px-6 text-[0.7rem] font-bold text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-50">
                            <?php if(empty($routes)): ?>
                            <tr><td colspan="6" class="py-8 text-center text-slate-400 font-medium font-sans">No route data available. Add your first route!</td></tr>
                            <?php else: ?>
                                <?php foreach($routes as $route): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-6 font-mono font-bold text-slate-400 text-xs">#<?= $route['id'] ?></td>
                                    <td class="py-3.5 px-6 font-bold text-slate-800"><?= htmlspecialchars($route['source']) ?></td>
                                    <td class="py-3.5 px-6 font-bold text-slate-800"><?= htmlspecialchars($route['destination']) ?></td>
                                    <td class="py-3.5 px-6 font-medium text-slate-500 text-center"><?= $route['distance'] ?> km</td>
                                    <td class="py-3.5 px-6 font-bold text-emerald-600 text-center text-base">₹<?= $route['base_fare'] ?></td>
                                    <td class="py-3.5 px-6">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="openEditModal(<?= $route['id'] ?>, '<?= htmlspecialchars(addslashes($route['source'])) ?>', '<?= htmlspecialchars(addslashes($route['destination'])) ?>', <?= $route['distance'] ?>, <?= $route['base_fare'] ?>)" class="bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 px-3 py-1.5 rounded-lg font-bold text-xs transition-colors border border-slate-200">Edit</button>
                                            
                                            <form action="" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this route? Buses using this route will be disconnected.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="route_id" value="<?= $route['id'] ?>">
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

    <!-- Modal: Add Route -->
    <div id="addModal" class="modal fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Add New Route</h3>
                <button onclick="closeModal('addModal')" class="text-slate-400 hover:text-red-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="" method="POST" class="p-6">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Source City</label>
                        <input type="text" name="source" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Destination City</label>
                        <input type="text" name="destination" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Distance (KM)</label>
                        <input type="number" name="distance" min="1" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 pl-4 py-2">
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Base Fare (₹)</label>
                        <input type="number" name="base_fare" min="0" step="0.01" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" onclick="closeModal('addModal')" class="px-5 py-2 rounded-xl text-slate-500 font-bold text-sm bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold text-sm bg-indigo-600 hover:bg-indigo-700 shadow-md transition-colors">Save Route</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Route -->
    <div id="editModal" class="modal fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Edit Route Configuration</h3>
                <button onclick="closeModal('editModal')" class="text-slate-400 hover:text-red-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="" method="POST" class="p-6">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="route_id" id="edit_route_id">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Source City</label>
                        <input type="text" name="source" id="edit_source" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Destination City</label>
                        <input type="text" name="destination" id="edit_destination" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Distance (KM)</label>
                        <input type="number" name="distance" id="edit_distance" min="1" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 pl-4 py-2">
                    </div>
                    <div>
                        <label class="block text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Base Fare (₹)</label>
                        <input type="number" name="base_fare" id="edit_base_fare" min="0" step="0.01" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" onclick="closeModal('editModal')" class="px-5 py-2 rounded-xl text-slate-500 font-bold text-sm bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold text-sm bg-indigo-600 hover:bg-indigo-700 shadow-md transition-colors">Update Route</button>
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
        function openEditModal(id, source, destination, distance, fare) {
            document.getElementById('edit_route_id').value = id;
            document.getElementById('edit_source').value = source;
            document.getElementById('edit_destination').value = destination;
            document.getElementById('edit_distance').value = distance;
            document.getElementById('edit_base_fare').value = fare;
            openModal('editModal');
        }
    </script>
</body>
</html>
