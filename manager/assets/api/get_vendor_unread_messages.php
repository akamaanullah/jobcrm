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
    $jobId = $_GET['job_id'] ?? null;

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Get unread message count for each vendor in the specific job
    $sql = "
        SELECT 
            v.id as vendor_id,
            v.vendor_name,
            COUNT(m.id) as unread_count
        FROM vendors v
        LEFT JOIN messages m ON v.id = m.vendor_id 
            AND m.job_id = :job_id 
            AND m.receiver_id = :user_id 
            AND m.is_read = FALSE
        WHERE v.job_id = :job_id
        GROUP BY v.id, v.vendor_name
        ORDER BY v.id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array with vendor_id as key
    $unreadCounts = [];
    foreach ($results as $result) {
        $unreadCounts[$result['vendor_id']] = (int)$result['unread_count'];
    }

    echo json_encode([
        'success' => true,
        'data' => $unreadCounts
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
