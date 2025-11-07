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

    if ($_SESSION['user_role'] !== 'admin') {
        throw new Exception('Admin access required');
    }

    $pdo = connectDatabase();

    // Get parameters
    $jobId = $_GET['job_id'] ?? null;

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Get unread message counts for each vendor in this job
    $unreadCountsSql = "
        SELECT 
            v.id as vendor_id,
            v.vendor_name,
            COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = :admin_id THEN 1 END) as unread_count
        FROM vendors v
        LEFT JOIN messages m ON v.id = m.vendor_id AND m.job_id = :job_id
        WHERE v.job_id = :job_id
        GROUP BY v.id, v.vendor_name
        ORDER BY v.vendor_name
    ";
    
    $unreadCountsStmt = $pdo->prepare($unreadCountsSql);
    $unreadCountsStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $unreadCountsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $unreadCountsStmt->execute();
    $unreadCounts = $unreadCountsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response
    $response = [];
    foreach ($unreadCounts as $count) {
        $response[$count['vendor_id']] = [
            'vendor_id' => (int)$count['vendor_id'],
            'vendor_name' => $count['vendor_name'],
            'unread_count' => (int)$count['unread_count']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $response
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
