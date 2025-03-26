<?php
session_start();
include 'connect.php';

$session_id = session_id();
$sql = "SELECT SUM(quantity) as count FROM tbl_shoppingcart WHERE usersessionid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'] ?? 0;

echo json_encode(['count' => $count]);
?> 