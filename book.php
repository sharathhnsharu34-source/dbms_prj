<?php
$bus_id = $_GET['bus_id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Passenger Details</title>

<link rel="stylesheet" href="css/style.css">

<style>

.bus-layout{
width:420px;
margin:30px auto;
background:#f8f8f8;
padding:20px;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.row{
display:flex;
justify-content:space-between;
margin:6px 0;
}

.seat{
width:45px;
height:40px;
background:#ecf0f1;
border-radius:6px;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
font-weight:bold;
}

.seat:hover{
background:#27ae60;
color:white;
}

.bus-front{
text-align:center;
font-weight:bold;
margin-bottom:10px;
}

.bus-rear{
text-align:center;
font-weight:bold;
margin-top:10px;
}

</style>

<script>

function selectSeat(seat){

document.getElementById("seat_number").value = seat;

}

</script>

</head>

<body>

<div class="navbar">
<h1>Enter Passenger Details</h1>
</div>

<div class="booking-container">

<form action="confirm_booking.php" method="POST">

<input type="hidden" name="bus_id" value="<?php echo $bus_id; ?>">

<label>Passenger Name</label>
<input type="text" name="passenger_name" required>

<label>Seat Number</label>
<input type="text" id="seat_number" name="seat_number" readonly required>

<button type="submit">Confirm Booking</button>

</form>

</div>

<!-- BUS SEAT LAYOUT -->

<div class="bus-layout">

<div class="bus-front">FRONT OF BUS</div>

<div class="row">
<div class="seat" onclick="selectSeat(1)">01</div>
<div class="seat" onclick="selectSeat(2)">02</div>

<div class="seat" onclick="selectSeat(3)">03</div>
<div class="seat" onclick="selectSeat(4)">04</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(5)">05</div>
<div class="seat" onclick="selectSeat(6)">06</div>

<div class="seat" onclick="selectSeat(7)">07</div>
<div class="seat" onclick="selectSeat(8)">08</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(9)">09</div>
<div class="seat" onclick="selectSeat(10)">10</div>

<div class="seat" onclick="selectSeat(11)">11</div>
<div class="seat" onclick="selectSeat(12)">12</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(13)">13</div>
<div class="seat" onclick="selectSeat(14)">14</div>

<div class="seat" onclick="selectSeat(15)">15</div>
<div class="seat" onclick="selectSeat(16)">16</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(17)">17</div>
<div class="seat" onclick="selectSeat(18)">18</div>

<div class="seat" onclick="selectSeat(19)">19</div>
<div class="seat" onclick="selectSeat(20)">20</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(21)">21</div>
<div class="seat" onclick="selectSeat(22)">22</div>

<div class="seat" onclick="selectSeat(23)">23</div>
<div class="seat" onclick="selectSeat(24)">24</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(25)">25</div>
<div class="seat" onclick="selectSeat(26)">26</div>

<div class="seat" onclick="selectSeat(27)">27</div>
<div class="seat" onclick="selectSeat(28)">28</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(29)">29</div>
<div class="seat" onclick="selectSeat(30)">30</div>
</div>

<div class="row">
<div class="seat" onclick="selectSeat(31)">31</div>
<div class="seat" onclick="selectSeat(32)">32</div>
</div>

<div class="bus-rear">REAR OF BUS</div>

</div>

</body>
</html>