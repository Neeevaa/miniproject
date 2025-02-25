<?php
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array(); // Initialize cart if not set
}

// Handle adding items to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['food_item'])) {
    $item = $_POST['food_item'];
    $_SESSION['cart'][] = $item; // Store selected items in session
    header("Location: menu.php"); // Prevent form resubmission on refresh
    exit();
}
?>

<html>
<body>
<h2>Menu</h2>
<form method="post">
    <label for="food">Select Food Item:</label>
    <input type="text" name="food_item" required>
    <button type="submit">Add to Platter</button>
</form>
<a href="checkout.php">Go to Checkout</a>
</body>
</html>
