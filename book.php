<?php
$bus_id=$_GET['bus_id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Passenger Details</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="navbar">
<h1>Enter Passenger Details</h1>
</div>

<div class="form-container">

<form action="confirm_booking.php" method="POST">

<input type="hidden" name="bus_id" value="<?php echo $bus_id; ?>">

<label>Passenger Name</label>
<input type="text" name="passenger_name" required>

<label>Seat Number</label>
<input type="number" name="seat_number" required>

<button type="submit">Confirm Booking</button>

</form>

</div>

</body>
</html>