<?php
include 'connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT * FROM tbl_fooditem WHERE itemid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($item = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'item' => $item
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No item ID provided'
    ]);
}
?> 