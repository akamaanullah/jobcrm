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

    $pdo = getDB();

    // Get unread message counts for all jobs where admin is the receiver
    $unreadMessagesSql = "
        SELECT 
            j.id as job_id,
            j.store_name,
            COUNT(m.id) as unread_count
        FROM jobs j
        INNER JOIN vendors v ON j.id = v.job_id
        LEFT JOIN messages m ON v.id = m.vendor_id AND j.id = m.job_id 
            AND m.receiver_id = :admin_id 
            AND m.is_read = 0
        GROUP BY j.id, j.store_name
        HAVING unread_count > 0
    ";
    
    $unreadStmt = $pdo->prepare($unreadMessagesSql);
    $unreadStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $unreadStmt->execute();
    $results = $unreadStmt->fetchAll(PDO::FETCH_ASSOC);

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
