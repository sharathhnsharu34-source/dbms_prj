<?php
include("db/connect.php");

$bus_id = $_GET['bus_id'];

$bookedSeats = [];

$sql = "SELECT seat_number FROM bookings WHERE bus_id='$bus_id'";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
$bookedSeats[] = $row['seat_number'];
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Passenger Details</title>
<link rel="stylesheet" href="css/style.css">

<style>

body{
font-family:Arial;
background:#f4f6f9;
margin:0;
}

.navbar{
background:#34495e;
color:white;
text-align:center;
padding:20px;
}

.booking-container{
width:60%;
margin:30px auto;
}

input{
width:100%;
padding:10px;
margin:10px 0;
border-radius:5px;
border:1px solid #ccc;
}

button{
background:#27ae60;
color:white;
border:none;
padding:10px 20px;
border-radius:5px;
cursor:pointer;
}

button:hover{
background:#1e8449;
}

/* BUS LAYOUT */

.bus-layout{
width:420px;
margin:40px auto;
background:white;
padding:25px;
border-radius:12px;
box-shadow:0 8px 20px rgba(0,0,0,0.15);
text-align:center;
}

.bus-front,
.bus-rear{
font-weight:bold;
margin:10px;
}

.seat-grid{
display:grid;
grid-template-columns:repeat(5,1fr);
gap:12px;
margin-top:20px;
}

.aisle{
visibility:hidden;
}

.seat{
width:55px;
height:45px;
background:#ecf0f1;
border-radius:8px;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
font-weight:bold;
transition:all 0.25s ease;
box-shadow:0 3px 6px rgba(0,0,0,0.15);
}

.seat:hover{
transform:translateY(-6px);
background:#3498db;
color:white;
}

.selected{
background:#2ecc71 !important;
color:white;
transform:scale(1.1);
}

.booked{
background:#e74c3c;
color:white;
cursor:not-allowed;
box-shadow:none;
}

</style>

<script>

function selectSeat(seat){

document.getElementById("seat_number").value = seat;

let seats = document.querySelectorAll(".seat");

seats.forEach(function(s){
s.classList.remove("selected");
});

document.getElementById("seat"+seat).classList.add("selected");

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

<div class="bus-layout">

<div class="bus-front">🚌 FRONT OF BUS</div>

<div class="seat-grid">

<?php

for($i=1;$i<=32;$i++){

if($i%4==3){
echo "<div class='aisle'></div>";
}

if(in_array($i,$bookedSeats)){

echo "<div class='seat booked'>$i</div>";

}else{

echo "<div class='seat' id='seat$i' onclick='selectSeat($i)'>$i</div>";

}

}

?>

</div>

<div class="bus-rear">REAR OF BUS</div>

</div>

</body>
</html>