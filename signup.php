<?php
session_start();
include("db/connect.php");

$error = "";
$success = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($email) && empty($phone)) {
        $error = "Either Email or Phone Number is required.";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if user exists
        $sql = "SELECT id FROM users WHERE email=? OR phone=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        if($stmt->get_result()->num_rows > 0) {
            $error = "User already exists with this Email or Phone.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
            $ins_stmt = $conn->prepare($insert_sql);
            $ins_stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            
            if($ins_stmt->execute()){
                $success = "Account created successfully! You can now login.";
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}
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
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80')] bg-cover bg-center pt-10 pb-10">
    
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="w-full max-w-lg glass-panel rounded-[2rem] shadow-2xl overflow-hidden relative z-10 p-8 md:p-10 border border-white/20 my-8">
        <div class="text-center mb-8">
            <a href="index.php" class="inline-block mb-4 transition transform hover:-translate-y-1">
                <div class="w-14 h-14 bg-white rounded-2xl shadow-md flex items-center justify-center mx-auto text-red-500 border border-slate-100">
                    <i class="fa-solid fa-user-plus text-2xl"></i>
                </div>
            </a>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Create an Account</h2>
            <p class="text-slate-500 text-sm mt-1">Join us for the best travel experience.</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-medium border border-red-100 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-lg"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 text-sm font-medium border border-emerald-100 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-lg"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Full Name *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-id-badge"></i>
                    </div>
                    <input type="text" name="name" required placeholder="John Doe" 
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input type="email" name="email" placeholder="john@example.com" 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Phone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <input type="text" name="phone" placeholder="+1234567890" 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Password *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••" minlength="6"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Confirm Password *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <input type="password" name="confirm_password" required placeholder="••••••••" minlength="6"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl text-white font-bold tracking-wide transition-all shadow-md hover:shadow-xl hover:-translate-y-0.5 bg-gradient-to-r from-red-500 to-rose-600 mt-4">
                Create Account
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-8 font-medium">
            Already have an account? <a href="login.php" class="text-red-500 hover:text-red-600 font-bold ml-1 transition-colors">Sign in</a>
        </p>
    </div>

</body>
</html>
