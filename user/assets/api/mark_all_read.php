<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $pdo = getDB();
    $userId = $_SESSION['user_id'];
    
    // Update all user notifications to read
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE notify_for = 'user' 
        AND user_id = :user_id 
        AND is_read = 0
    ");
    
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $affectedRows = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Marked {$affectedRows} notifications as read",
        'affected_rows' => $affectedRows
    ]);
    
} catch (PDOException $e) {
    error_log("Mark All Read Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Mark All Read Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>