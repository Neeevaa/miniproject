<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemname = mysqli_real_escape_string($conn, $_POST['itemname']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    // Validate category
    $valid_categories = ['Starters', 'Main Course', 'Desserts', 'Beverages'];
    if (!in_array($category, $valid_categories)) {
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        exit;
    }
    
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $itemdescription = mysqli_real_escape_string($conn, $_POST['itemdescription']);
    $itemdetailed = mysqli_real_escape_string($conn, $_POST['itemdetailed']);
    
    // Handle image upload
    $targetDir = "images/";
    $imageName = "";
    
    if (isset($_FILES["itemimage"]) && $_FILES["itemimage"]["error"] == 0) {
        // Generate a unique filename to avoid overwriting
        $originalName = basename($_FILES["itemimage"]["name"]);
        $imageFileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $uniqueName = time() . "_" . uniqid() . "." . $imageFileType;
        $targetFile = $targetDir . $uniqueName;
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES["itemimage"]["tmp_name"]);
        if ($check !== false) {
            // Upload the file
            if (move_uploaded_file($_FILES["itemimage"]["tmp_name"], $targetFile)) {
                // Store ONLY the filename in the database, not the full path
                $imageName = $uniqueName;
                error_log("Image uploaded successfully: " . $targetFile);
            } else {
                error_log("Error uploading image");
            }
        } else {
            error_log("File is not an image");
        }
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert into tbl_fooditem
        $query = "INSERT INTO tbl_fooditem (itemname, category, price, itemdescription, itemimage) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssdss", $itemname, $category, $price, $itemdescription, $imageName);
        mysqli_stmt_execute($stmt);
        
        $itemid = mysqli_insert_id($conn);
        
        // Insert into tbl_fooditemdetailed
        $detailed_query = "INSERT INTO tbl_fooditemdetailed (itemid, itemdetailed, added_at) 
                         VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $detailed_query);
        mysqli_stmt_bind_param($stmt, "is", $itemid, $itemdetailed);
        mysqli_stmt_execute($stmt);
        
        mysqli_commit($conn);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?> 