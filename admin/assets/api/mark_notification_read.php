<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['notification_id']) || empty($input['notification_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

$pdo = getDB();
$notificationId = $input['notification_id'];

try {
    // First, verify the notification exists and belongs to admin
    $checkQuery = "
        SELECT id, is_read 
        FROM notifications 
        WHERE id = :notification_id 
        AND notify_for = 'admin'
    ";
    
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    $notification = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit;
    }
    
    if ($notification['is_read'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Notification is already marked as read']);
        exit;
    }
    
    // Mark the notification as read
    $updateQuery = "
        UPDATE notifications 
        SET is_read = 1, updated_at = CURRENT_TIMESTAMP
        WHERE id = :notification_id 
        AND notify_for = 'admin'
    ";
    
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
    $updateStmt->execute();
    
    if ($updateStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark notification as read'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Mark Notification Read API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>
