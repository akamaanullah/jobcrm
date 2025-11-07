<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$pdo = getDB();

try {
    // Mark all admin notifications as read
    $query = "
        UPDATE notifications 
        SET is_read = 1, updated_at = CURRENT_TIMESTAMP
        WHERE notify_for = 'admin' AND is_read = 0
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $affectedRows = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Marked {$affectedRows} notifications as read"
    ]);
    
} catch (Exception $e) {
    error_log("Mark All Read API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>
