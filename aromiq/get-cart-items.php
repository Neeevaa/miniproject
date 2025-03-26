<?php
session_start();
include 'connect.php';

$session_id = session_id();
$sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'name' => $row['food_name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'image' => $row['image_path']
    ];
}

// Store in session
$_SESSION['cart'] = array_column($items, null, 'name');

echo json_encode($items); 