<?php
session_start();
if(!isset($_POST['booking_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bus Ticket Booking | SkylineTransit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .pop-in { animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes popIn { 0% { opacity: 0; transform: scale(0.8); } 100% { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80')] bg-cover bg-center">
    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>

    <div class="w-full max-w-md glass-panel text-slate-800 rounded-[2rem] shadow-2xl overflow-hidden relative z-10 p-8 md:p-10 border border-white/20 pop-in text-center">
        
        <div class="w-20 h-20 bg-red-100 rounded-full mx-auto flex items-center justify-center mb-6 border-4 border-white shadow-lg">
            <i class="fa-solid fa-xmark text-4xl text-red-500"></i>
        </div>
        
        <h2 class="text-3xl font-black tracking-tight text-slate-800 mb-2">Payment Failed ❌</h2>
        <p class="text-slate-500 font-medium mb-8">Transaction declined / Network Error. <br>Your booking was not confirmed.</p>

        <form action="payment.php" method="POST" class="mb-3 w-full">
            <?php
            foreach($_POST as $key => $val) {
                if(is_array($val)){
                    foreach($val as $v) echo "<input type='hidden' name='{$key}[]' value='".htmlspecialchars($v, ENT_QUOTES)."'>";
                } else {
                    echo "<input type='hidden' name='$key' value='".htmlspecialchars($val, ENT_QUOTES)."'>";
                }
            }
            ?>
            <button type="submit" class="w-full py-3.5 rounded-xl text-white font-bold tracking-wide transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 bg-gradient-to-r from-red-500 to-rose-600 flex justify-center items-center gap-2">
                <i class="fa-solid fa-rotate-right"></i> Retry Payment
            </button>
        </form>

        <a href="index.php" class="w-full py-3.5 rounded-xl text-slate-600 font-bold tracking-wide transition-all shadow-sm hover:shadow-md hover:bg-slate-50 hover:-translate-y-0.5 bg-white border border-slate-200 flex justify-center items-center gap-2 mt-3 cursor-pointer select-none">
            <i class="fa-solid fa-ban"></i> Cancel Booking
        </a>
    </div>
</body>
</html>
