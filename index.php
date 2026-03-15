<?php
if(isset($_GET['success'])){
echo "<h3 style='color:green;'>Booking Successful! You can search another bus.</h3>";
}

if(isset($_GET['error'])){
echo "<h3 style='color:red;'>Seat already booked for this bus.</h3>";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Bus Ticket Booking</title>
</head>

<body>

<h2>Search Bus</h2>

<form action="search_bus.php" method="POST">

From:
<input type="text" name="source" required>

To:
<input type="text" name="destination" required>

<button type="submit">Search</button>

</form>

</body>
</html>