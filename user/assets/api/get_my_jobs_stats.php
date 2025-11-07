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

    // Get SLA reminders count (jobs with SLA deadline within 2 days) - Only for assigned jobs
    $slaRemindersQuery = "
        SELECT COUNT(*) as sla_reminders 
        FROM jobs 
        WHERE assigned_to = :user_id
        AND job_sla > NOW() 
        AND TIMESTAMPDIFF(HOUR, NOW(), job_sla) <= 48
        AND status IN ('added', 'in_progress')
    ";
    $slaRemindersStmt = $pdo->prepare($slaRemindersQuery);
    $slaRemindersStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $slaRemindersStmt->execute();
    $slaReminders = $slaRemindersStmt->fetch(PDO::FETCH_ASSOC)['sla_reminders'];

    // Get completed jobs count (jobs where any vendor has job_completed, requested_vendor_payment, or vendor_payment_accepted status) - Only for assigned jobs
    $completedJobsQuery = "
        SELECT COUNT(DISTINCT j.id) as completed_jobs
        FROM jobs j
        INNER JOIN vendors v ON j.id = v.job_id
        WHERE j.assigned_to = :user_id
        AND v.status IN ('job_completed', 'requested_vendor_payment', 'vendor_payment_accepted')
    ";
    $completedJobsStmt = $pdo->prepare($completedJobsQuery);
    $completedJobsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $completedJobsStmt->execute();
    $completedJobs = $completedJobsStmt->fetch(PDO::FETCH_ASSOC)['completed_jobs'];

    // Get in progress jobs count (jobs with in_progress status) - Only for assigned jobs
    $inProgressJobsQuery = "SELECT COUNT(*) as in_progress_jobs FROM jobs WHERE assigned_to = :user_id AND status = 'in_progress'";
    $inProgressJobsStmt = $pdo->prepare($inProgressJobsQuery);
    $inProgressJobsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $inProgressJobsStmt->execute();
    $inProgressJobs = $inProgressJobsStmt->fetch(PDO::FETCH_ASSOC)['in_progress_jobs'];

    echo json_encode([
        'success' => true,
        'data' => [
            'total_jobs' => (int) $totalJobs,
            'sla_reminders' => (int) $slaReminders,
            'completed_jobs' => (int) $completedJobs,
            'in_progress_jobs' => (int) $inProgressJobs
        ]
    ]);

} catch (PDOException $e) {
    error_log("My Jobs Stats Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("My Jobs Stats Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>