<?php
session_start();
include("db/connect.php");

if(!isset($_POST['booking_id'])) {
    header("Location: index.php");
    exit();
}
$booking_id = $_POST['booking_id'];
$amount = isset($_POST['price']) && isset($_POST['seat_number']) ? 
    $_POST['price'] * count(array_filter(explode(',', $_POST['seat_number']))) : 0;

// Fetch active coupons
$coupons_query = $conn->query("SELECT * FROM coupons WHERE is_active = 1");
$coupons = [];
if ($coupons_query) {
    while($row = $coupons_query->fetch_assoc()) $coupons[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment | BusBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .option-radio:checked + label { border-color: #ef4444; background-color: #fef2f2; }
        
        /* Hide scrollbar for coupon list */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80')] bg-cover bg-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="w-full max-w-xl glass-panel text-slate-800 rounded-[2rem] shadow-2xl overflow-hidden relative z-10 p-8 border border-white/20 fade-in my-8">
        
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-white rounded-2xl shadow-md flex items-center justify-center mx-auto text-emerald-500 border border-slate-100 mb-3">
                <i class="fa-solid fa-shield-halved text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold tracking-tight">Checkout</h2>
        </div>

        <!-- Offers & Discounts Section -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-rose-100 to-transparent rounded-bl-full opacity-50 -mr-4 -mt-4 pointer-events-none"></div>
            
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-tags text-rose-500"></i> Offers & Discounts
            </h3>
            
            <div class="flex gap-2 mb-4 relative z-10">
                <input type="text" id="couponInput" placeholder="Enter Coupon Code" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-500/50 uppercase font-bold text-slate-700 placeholder-slate-400 text-sm shadow-inner transition-all">
                <button type="button" onclick="applyManualCoupon()" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-colors shadow-sm">Apply</button>
            </div>
            
            <p id="couponMsg" class="text-xs font-bold mb-3 hidden"></p>

            <div class="flex overflow-x-auto gap-3 pb-2 no-scrollbar relative z-10">
                <?php foreach($coupons as $c): ?>
                <div class="min-w-[200px] border border-dashed border-rose-300 bg-rose-50/50 rounded-xl p-3 flex flex-col justify-between hover:border-rose-400 transition-colors">
                    <div>
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-xs font-black text-rose-600 bg-rose-100 px-2 py-0.5 rounded uppercase tracking-wider"><?= htmlspecialchars($c['code']) ?></span>
                        </div>
                        <p class="text-[0.65rem] font-semibold text-slate-600 leading-snug"><?= htmlspecialchars($c['description']) ?></p>
                    </div>
                    <button type="button" onclick="applySnippetCoupon('<?= htmlspecialchars($c['code']) ?>')" class="mt-2 text-[0.65rem] font-bold text-rose-600 hover:text-rose-800 uppercase tracking-widest text-left">Tap to Apply →</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Price Summary Section -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-8 shadow-inner relative">
            
            <div class="flex justify-between items-center mb-2">
                <p class="text-xs text-slate-500 font-semibold">Booking ID</p>
                <p class="font-mono font-bold text-slate-700 text-sm">BUS-<?php echo str_pad($booking_id, 5, '0', STR_PAD_LEFT); ?></p>
            </div>
            
            <div class="flex justify-between items-center mb-2">
                <p class="text-xs text-slate-500 font-semibold">Subtotal</p>
                <p class="font-bold text-slate-700 text-sm">₹<span id="ui_original_price"><?php echo htmlspecialchars($amount); ?></span></p>
            </div>
            
            <div id="discount_row" class="flex justify-between items-center mb-3 hidden">
                <p class="text-xs text-emerald-600 font-bold flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Coupon (<span id="ui_applied_code"></span>)</p>
                <p class="font-bold text-emerald-600 text-sm">-₹<span id="ui_discount_amount">0</span></p>
            </div>
            
            <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
                <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest">Total Payable</p>
                <div class="text-right flex items-center gap-2">
                    <p id="ui_strikethrough" class="text-sm font-bold text-slate-400 line-through hidden">₹<?php echo htmlspecialchars($amount); ?></p>
                    <p class="text-3xl font-black text-slate-800 tracking-tighter" id="ui_final_price">₹<?php echo htmlspecialchars($amount); ?></p>
                </div>
            </div>
        </div>

        <form action="payment_process.php" method="POST" id="paymentForm" onsubmit="return handlePayment(event)">
            <!-- Forward all POST data -->
            <?php
            foreach($_POST as $key => $val) {
                if(is_array($val)) {
                    foreach($val as $v) { echo "<input type='hidden' name='{$key}[]' value='".htmlspecialchars($v, ENT_QUOTES)."'>"; }
                } else {
                    echo "<input type='hidden' name='$key' value='".htmlspecialchars($val, ENT_QUOTES)."'>";
                }
            }
            ?>
            <input type="hidden" name="applied_coupon" id="hidden_applied_coupon" value="">
            <input type="hidden" id="base_amount" value="<?php echo htmlspecialchars($amount); ?>">

            <div class="space-y-4 mb-8">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Payment Method</p>
                
                <!-- UPI Option -->
                <div class="relative">
                    <input type="radio" name="payment_method" id="pay_upi" value="upi" class="option-radio hidden" checked onchange="togglePaymentView()">
                    <label for="pay_upi" class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer transition-all hover:border-red-300 bg-white">
                        <div class="w-10 h-10 bg-white rounded-lg shadow-sm flex items-center justify-center mr-4 border border-slate-100">
                            <i class="fa-brands fa-google-pay text-2xl text-slate-700"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-slate-800">UPI Payment</h4>
                            <p class="text-xs text-slate-500 font-medium">Google Pay, PhonePe, Paytm</p>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center check-circle transition-colors"></div>
                    </label>
                </div>

                <!-- Card Option -->
                <div class="relative">
                    <input type="radio" name="payment_method" id="pay_card" value="card" class="option-radio hidden" onchange="togglePaymentView()">
                    <label for="pay_card" class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer transition-all hover:border-red-300 bg-white">
                        <div class="w-10 h-10 bg-white rounded-lg shadow-sm flex items-center justify-center mr-4 border border-slate-100">
                            <i class="fa-regular fa-credit-card text-xl text-slate-700"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-slate-800">Debit / Credit Card</h4>
                            <p class="text-xs text-slate-500 font-medium">Visa, Mastercard, RuPay</p>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center check-circle transition-colors"></div>
                    </label>
                </div>
            </div>

            <!-- Dynamic Input Fields -->
            <div id="upi_fields" class="mb-8 block fade-in">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">UPI ID</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-at"></i>
                    </div>
                    <input type="text" id="upi_id" placeholder="username@bank" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-slate-800 bg-white placeholder-slate-300">
                </div>
            </div>

            <div id="card_fields" class="mb-8 hidden">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Card Number</label>
                        <div class="relative">
                            <i class="fa-regular fa-credit-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="card_num" placeholder="0000 0000 0000 0000" maxlength="19" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 font-medium text-slate-800">
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Expiry</label>
                            <input type="text" id="card_exp" placeholder="MM/YY" maxlength="5" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 font-medium text-slate-800 text-center">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">CVV</label>
                            <input type="password" id="card_cvv" placeholder="•••" maxlength="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 font-medium text-slate-800 text-center">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Cardholder Name</label>
                        <input type="text" id="card_name" placeholder="Name on card" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 font-medium text-slate-800">
                    </div>
                </div>
            </div>

            <button type="submit" id="payBtn" class="w-full py-4 rounded-xl text-white font-bold tracking-wide transition-all shadow-md hover:shadow-xl hover:-translate-y-0.5 bg-gradient-to-r from-red-500 to-rose-600 flex justify-center items-center gap-2">
                <i class="fa-solid fa-lock text-sm"></i> PAY <span id="btn_pay_amount">₹<?php echo htmlspecialchars($amount); ?></span>
            </button>
        </form>
        
        <script>
            // Coupon Logic
            const baseAmount = parseFloat(document.getElementById('base_amount').value);
            
            function applySnippetCoupon(code) {
                document.getElementById('couponInput').value = code;
                applyManualCoupon();
            }

            async function applyManualCoupon() {
                const code = document.getElementById('couponInput').value.trim();
                const msgEl = document.getElementById('couponMsg');

                if (code === '') {
                    resetCouponUI();
                    msgEl.className = 'text-xs font-bold mb-3 text-slate-500';
                    msgEl.innerText = 'Coupon cleared.';
                    msgEl.classList.remove('hidden');
                    return;
                }

                msgEl.className = 'text-xs font-bold mb-3 text-slate-500';
                msgEl.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Checking coupon...';
                msgEl.classList.remove('hidden');

                const formData = new FormData();
                formData.append('coupon_code', code);
                formData.append('amount', baseAmount);

                try {
                    const res = await fetch('apply_coupon.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    
                    if (data.success) {
                        msgEl.className = 'text-xs font-black mb-3 text-emerald-500 uppercase tracking-widest';
                        msgEl.innerText = '✨ ' + data.message;
                        
                        document.getElementById('hidden_applied_coupon').value = data.code;
                        
                        // Update UI prices
                        document.getElementById('discount_row').classList.remove('hidden');
                        document.getElementById('ui_applied_code').innerText = data.code;
                        document.getElementById('ui_discount_amount').innerText = data.discount;
                        
                        document.getElementById('ui_strikethrough').classList.remove('hidden');
                        document.getElementById('ui_final_price').innerText = '₹' + data.final_price;
                        document.getElementById('btn_pay_amount').innerText = '₹' + data.final_price;
                        
                        document.getElementById('couponInput').value = data.code;
                    } else {
                        resetCouponUI();
                        msgEl.className = 'text-xs font-bold mb-3 text-red-500';
                        msgEl.innerText = '❌ ' + data.message;
                    }
                } catch(e) {
                    resetCouponUI();
                    msgEl.className = 'text-xs font-bold mb-3 text-red-500';
                    msgEl.innerText = 'Error applying coupon. Try again.';
                }
            }

            function resetCouponUI() {
                document.getElementById('hidden_applied_coupon').value = '';
                document.getElementById('discount_row').classList.add('hidden');
                document.getElementById('ui_strikethrough').classList.add('hidden');
                document.getElementById('ui_final_price').innerText = '₹' + baseAmount;
                document.getElementById('btn_pay_amount').innerText = '₹' + baseAmount;
            }

            // Payment View Toggles
            function togglePaymentView() {
                const isCard = document.getElementById('pay_card').checked;
                const upiFields = document.getElementById('upi_fields');
                const cardFields = document.getElementById('card_fields');
                
                if(isCard) {
                    upiFields.classList.add('hidden');
                    upiFields.querySelector('#upi_id').required = false;
                    
                    cardFields.classList.remove('hidden');
                    cardFields.classList.add('fade-in');
                    document.getElementById('card_num').required = true;
                    document.getElementById('card_exp').required = true;
                    document.getElementById('card_cvv').required = true;
                    document.getElementById('card_name').required = true;
                } else {
                    cardFields.classList.add('hidden');
                    document.getElementById('card_num').required = false;
                    document.getElementById('card_exp').required = false;
                    document.getElementById('card_cvv').required = false;
                    document.getElementById('card_name').required = false;
                    
                    upiFields.classList.remove('hidden');
                    upiFields.classList.add('fade-in');
                    upiFields.querySelector('#upi_id').required = true;
                }
            }

            // Card formatting
            document.getElementById('card_num').addEventListener('input', function (e) {
                let val = e.target.value.replace(/\D/g, '');
                e.target.value = val.replace(/(.{4})/g, '$1 ').trim();
            });
            document.getElementById('card_exp').addEventListener('input', function (e) {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length >= 2) val = val.substring(0, 2) + '/' + val.substring(2);
                e.target.value = val.substring(0, 5);
            });

            function handlePayment(e) {
                e.preventDefault();
                const btn = document.getElementById('payBtn');
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-xl"></i> <span class="ml-2">Processing Securely...</span>';
                btn.disabled = true;
                btn.classList.add('opacity-80', 'cursor-not-allowed');

                // Simulate processing locally, then submit the form to PHP
                setTimeout(() => {
                    document.getElementById('paymentForm').submit();
                }, 2000);
            }
        </script>
    </div>
</body>
</html>
