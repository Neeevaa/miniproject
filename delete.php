<?php
// Include database connection
include 'connect.php';

// Check if itemid is set
if (!isset($_POST['itemid'])) {
    echo json_encode(['success' => false, 'message' => 'No item ID provided']);
    exit;
}

$itemid = $_POST['itemid'];

// Output the received itemid for debugging
error_log("Received itemid: " . $itemid);

// Validate itemid is numeric
if (!is_numeric($itemid)) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID format']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // First check if the item exists in the main table
    $checkStmt = $conn->prepare("SELECT itemid FROM tbl_fooditem WHERE itemid = ?");
    $checkStmt->bind_param("i", $itemid);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Item ID $itemid does not exist in database");
    }
    
    // Delete from tbl_fooditemdetailed if it exists
    $stmt1 = $conn->prepare("DELETE FROM tbl_fooditemdetailed WHERE itemid = ?");
    $stmt1->bind_param("i", $itemid);
    $stmt1->execute();
    
    // Then delete from main food item table
    $stmt2 = $conn->prepare("DELETE FROM tbl_fooditem WHERE itemid = ?");
    $stmt2->bind_param("i", $itemid);
    $stmt2->execute();
    
    if ($stmt2->affected_rows === 0) {
        throw new Exception("No records were deleted");
    }

    // If we got here without errors, commit the transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
} catch (Exception $e) {
    // If there was an error, rollback the transaction
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting item: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>