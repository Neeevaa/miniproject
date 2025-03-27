<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always set content type to JSON
header('Content-Type: application/json');

try {
    session_start();
    if(!isset($_SESSION['uname'])){
        throw new Exception('Not authenticated');
    }

    // Database connection
    $servername = "localhost";
    $username = "kitchenadmin";
    $password = "1234";
    $database = "aromiq";

    // Connect to MySQL
    $conn = mysqli_connect($servername, $username, $password, $database);

    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }

    // Query to fetch all menu items
    $sql = "SELECT * FROM tbl_food ORDER BY category, itemname";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'itemid' => $row['itemid'],
            'itemname' => $row['itemname'],
            'itemdetailed' => $row['itemdetailed'],
            'itemdescription' => $row['itemdescription'],
            'price' => $row['price'],
            'category' => $row['category'],
            'itemimage' => $row['itemimage']
        ];
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?> 