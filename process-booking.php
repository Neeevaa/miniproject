<?php
// Ensure no output before JSON
ob_clean();

// Set proper headers
header('Content-Type: application/json');

// Database connection parameters
include 'connect.php';
// Response function
function sendResponse($success, $message = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

try {
    // Validate that it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method');
    }

    // Create database connection
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Basic input validation
    $requiredFields = ['name', 'email', 'table_number', 'datetime', 'people_count'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            sendResponse(false, "Missing required field: $field");
        }
    }

    // Prepare SQL statement
    $sql = "INSERT INTO tbl_bookings (
        name, 
        email, 
        table_number, 
        datetime, 
        people_count, 
        special_request, 
        special_option, 
        status, 
        notification_sent, 
        created_at
    ) VALUES (
        :name, 
        :email, 
        :table_number, 
        :datetime, 
        :people_count, 
        :special_request, 
        :special_option, 
        'Pending', 
        0, 
        NOW()
    )";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':table_number' => $_POST['table_number'],
        ':datetime' => $_POST['datetime'],
        ':people_count' => $_POST['people_count'],
        ':special_request' => $_POST['special_request'] ?? null,
        ':special_option' => $_POST['special_option'] ?? null
    ]);

    // Send success response
    sendResponse(true, 'Booking successfully submitted');

} catch (PDOException $e) {
    // Log database errors
    error_log('Database error: ' . $e->getMessage());
    sendResponse(false, 'Database error occurred: ' . $e->getMessage());
} catch (Exception $e) {
    // Log other unexpected errors
    error_log('Unexpected error: ' . $e->getMessage());
    sendResponse(false, 'An unexpected error occurred: ' . $e->getMessage());
}