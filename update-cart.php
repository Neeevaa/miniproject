<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_name = $_POST['food_name'];
    $session_id = session_id();
    $action = $_POST['action'];

    if ($action === 'update') {
        $quantity = intval($_POST['quantity']);
        $sql = "UPDATE tbl_shoppingcart SET quantity = ? WHERE usersessionid = ? AND food_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $quantity, $session_id, $food_name);
    } else if ($action === 'remove') {
        $sql = "DELETE FROM tbl_shoppingcart WHERE usersessionid = ? AND food_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $session_id, $food_name);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>