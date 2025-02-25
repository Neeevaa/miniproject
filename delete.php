<?php
session_start();
if(!isset($_SESSION['uname'])) {
    die('Unauthorized access');
}

$servername = "localhost";
$username = "admin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['itemid'])) {
    $itemid = filter_var($_POST['itemid'], FILTER_VALIDATE_INT);
    
    if ($itemid) {
        // First get the image filename
        $sql = "SELECT itemimage FROM tbl_fooditem WHERE itemid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $itemid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $image_path = 'images/' . $row['itemimage'];
            if (file_exists($image_path)) {
                unlink($image_path); // Delete the image file
            }
        }
        
        // Then delete the database record
        $sql = "DELETE FROM tbl_fooditem WHERE itemid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $itemid);
        
        if ($stmt->execute()) {
            echo "Food item deleted successfully";
        } else {
            echo "Error deleting food item";
        }
        $stmt->close();
    } else {
        echo "Invalid item ID";
    }
} else {
    echo "Invalid request";
}

mysqli_close($conn);
?>