<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access or Admin access required');
    }

    $pdo = connectDatabase();

    // Get total unread messages count for admin across all jobs and vendors
    $unreadMessagesSql = "
        SELECT COUNT(*) as total_unread_messages 
        FROM messages m
        INNER JOIN vendors v ON m.vendor_id = v.id
        WHERE m.receiver_id = :admin_id 
        AND m.is_read = 0
    ";
    
    $unreadStmt = $pdo->prepare($unreadMessagesSql);
    $unreadStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $unreadStmt->execute();
    $result = $unreadStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'total_unread_messages' => (int)$result['total_unread_messages']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
