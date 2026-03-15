<!DOCTYPE html>
<html>
<head>
<title>Bus Ticket Booking</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="navbar">
<h1>🚌 Bus Ticket Booking System</h1>
</div>

<div class="container">

<h2>Search Buses</h2>

<form action="search_bus.php" method="POST">

<input type="text" name="source" placeholder="From City" required>

<input type="text" name="destination" placeholder="To City" required>

<button type="submit">Search Bus</button>

</form>

</div>

</body>
</html>