<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('Unauthorized access');
    }

    $pdo = connectDatabase();
    $userId = $_SESSION['user_id'];

    // Get count of unread messages for the current user
    $sql = "
        SELECT COUNT(*) as unread_count
        FROM messages m
        WHERE m.receiver_id = :user_id 
        AND m.is_read = FALSE
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unreadCount = (int)$result['unread_count'];

    echo json_encode([
        'success' => true,
        'data' => [
            'unread_count' => $unreadCount
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
