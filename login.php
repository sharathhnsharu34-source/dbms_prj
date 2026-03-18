<?php
session_start();
include("db/connect.php");

$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $login_id = $_POST['login_id']; // email or phone
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email=? OR phone=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login_id, $login_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: index.php");
            exit();
        }else{
            $error = "Incorrect password!";
        }
    }else{
        $error = "User not found with this Email/Phone!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BusBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80')] bg-cover bg-center">
    
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="w-full max-w-md glass-panel rounded-[2rem] shadow-2xl overflow-hidden relative z-10 p-8 md:p-10 border border-white/20">
        <div class="text-center mb-8">
            <a href="index.php" class="inline-block mb-6 transition transform hover:-translate-y-1">
                <div class="w-16 h-16 bg-white rounded-2xl shadow-md flex items-center justify-center mx-auto text-red-500 border border-slate-100">
                    <i class="fa-solid fa-bus text-3xl"></i>
                </div>
            </a>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Welcome Back</h2>
            <p class="text-slate-500 text-sm mt-1">Please enter your details to sign in.</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-medium border border-red-100 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-lg"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Email or Phone</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <input type="text" name="login_id" required placeholder="Enter Email or Phone number" 
                        class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider flex justify-between">
                    <span>Password</span>
                    <a href="#" class="text-red-500 hover:text-red-600 capitalize tracking-normal">Forgot?</a>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••" 
                        class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl text-white font-bold tracking-wide transition-all shadow-md hover:shadow-xl hover:-translate-y-0.5 bg-gradient-to-r from-red-500 to-rose-600 mt-2">
                Sign In
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-8 font-medium">
            Don't have an account? <a href="signup.php" class="text-red-500 hover:text-red-600 font-bold ml-1 transition-colors">Sign up</a>
        </p>
    </div>

</body>
</html>
