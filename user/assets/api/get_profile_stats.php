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
    
    // Get total jobs count - Only for jobs assigned to current user
    $totalJobsQuery = "SELECT COUNT(*) as total_jobs FROM jobs WHERE assigned_to = :user_id";
    $totalJobsStmt = $pdo->prepare($totalJobsQuery);
    $totalJobsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $totalJobsStmt->execute();
    $totalJobs = $totalJobsStmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];
    
    // Get total vendors added by this user for assigned jobs only
    $totalVendorsQuery = "
        SELECT COUNT(*) as total_vendors 
        FROM vendors v
        INNER JOIN jobs j ON v.job_id = j.id
        WHERE v.added_by = :user_id
        AND j.assigned_to = :user_id
    ";
    $totalVendorsStmt = $pdo->prepare($totalVendorsQuery);
    $totalVendorsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $totalVendorsStmt->execute();
    $totalVendors = $totalVendorsStmt->fetch(PDO::FETCH_ASSOC)['total_vendors'];
    
    // Get completed jobs count (jobs where any vendor added by this user has completion status) - Only for assigned jobs
    $completedJobsQuery = "
        SELECT COUNT(DISTINCT j.id) as completed_jobs
        FROM jobs j
        INNER JOIN vendors v ON j.id = v.job_id
        WHERE j.assigned_to = :user_id
        AND v.added_by = :user_id
        AND v.status IN ('job_completed', 'requested_vendor_payment', 'vendor_payment_accepted')
    ";
    $completedJobsStmt = $pdo->prepare($completedJobsQuery);
    $completedJobsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $completedJobsStmt->execute();
    $completedJobs = $completedJobsStmt->fetch(PDO::FETCH_ASSOC)['completed_jobs'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_jobs' => (int)$totalJobs,
            'total_vendors' => (int)$totalVendors,
            'completed_jobs' => (int)$completedJobs
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Profile Stats Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Profile Stats Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
