<?php
include 'connect.php';

// Remove any extra output from connect.php
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "SELECT f.itemid, f.itemname, f.itemimage, f.price, f.itemdescription, 
           COALESCE(d.itemdetailed, f.itemdescription) as itemdetailed 
           FROM tbl_fooditem f 
           LEFT JOIN tbl_fooditemdetailed d ON f.itemid = d.itemid 
           WHERE f.itemid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'itemid' => $row['itemid'],
            'itemname' => $row['itemname'],
            'itemimage' => $row['itemimage'],
            'price' => $row['price'],
            'itemdescription' => $row['itemdescription'],
            'itemdetailed' => $row['itemdetailed']
        ]);
    } else {
        echo json_encode(['error' => 'Item not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>