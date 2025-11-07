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

    // Get unread message count for each job that belongs to the user
    $sql = "
        SELECT 
            DISTINCT n.job_id,
            (SELECT COUNT(*) 
             FROM messages m 
             WHERE m.job_id = n.job_id 
             AND m.receiver_id = :user_id 
             AND m.is_read = FALSE) as unread_count
        FROM notifications n
        WHERE n.user_id = :user_id
        ORDER BY n.job_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array with job_id as key
    $unreadCounts = [];
    foreach ($results as $result) {
        $unreadCounts[$result['job_id']] = (int)$result['unread_count'];
    }

    echo json_encode([
        'success' => true,
        'data' => $unreadCounts
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
