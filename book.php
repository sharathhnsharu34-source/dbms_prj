<?php
include("db/connect.php");

$bus_id = $_GET['bus_id'];
?>

<form action="confirm.php" method="POST">

<input type="hidden" name="bus_id" value="<?php echo $bus_id; ?>">

Passenger Name:
<input type="text" name="name" required>

Seat Number:
<input type="number" name="seat" required>

<button type="submit">Confirm Booking</button>

</form>