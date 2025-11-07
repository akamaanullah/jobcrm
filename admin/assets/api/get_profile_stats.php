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

    // Get admin profile statistics
    $stats = [];

    // 1. Total Users Managed (excluding admin)
    $usersSql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
    $usersStmt = $pdo->prepare($usersSql);
    $usersStmt->execute();
    $stats['users_managed'] = (int)$usersStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // 2. Total Jobs Posted
    $jobsSql = "SELECT COUNT(*) as total_jobs FROM jobs";
    $jobsStmt = $pdo->prepare($jobsSql);
    $jobsStmt->execute();
    $stats['jobs_posted'] = (int)$jobsStmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];

    // 3. Total Vendors
    $vendorsSql = "SELECT COUNT(*) as total_vendors FROM vendors";
    $vendorsStmt = $pdo->prepare($vendorsSql);
    $vendorsStmt->execute();
    $stats['vendors'] = (int)$vendorsStmt->fetch(PDO::FETCH_ASSOC)['total_vendors'];

    // 4. Days Active (days since first job created or admin created)
    $daysActiveSql = "
        SELECT DATEDIFF(NOW(), LEAST(
            (SELECT MIN(created_at) FROM jobs),
            (SELECT created_at FROM users WHERE id = :admin_id)
        )) as days_active
    ";
    $daysActiveStmt = $pdo->prepare($daysActiveSql);
    $daysActiveStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $daysActiveStmt->execute();
    $stats['days_active'] = (int)$daysActiveStmt->fetch(PDO::FETCH_ASSOC)['days_active'];

    // 5. Total Messages Handled
    $messagesSql = "
        SELECT COUNT(*) as total_messages 
        FROM messages m
        INNER JOIN vendors v ON m.vendor_id = v.id
        WHERE m.receiver_id = :admin_id
    ";
    $messagesStmt = $pdo->prepare($messagesSql);
    $messagesStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $messagesStmt->execute();
    $stats['messages_handled'] = (int)$messagesStmt->fetch(PDO::FETCH_ASSOC)['total_messages'];

    // 6. Completed Jobs
    $completedJobsSql = "SELECT COUNT(*) as completed_jobs FROM jobs WHERE status = 'completed'";
    $completedJobsStmt = $pdo->prepare($completedJobsSql);
    $completedJobsStmt->execute();
    $stats['completed_jobs'] = (int)$completedJobsStmt->fetch(PDO::FETCH_ASSOC)['completed_jobs'];

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
