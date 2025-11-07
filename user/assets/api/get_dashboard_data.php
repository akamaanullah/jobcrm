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

    // Get job statistics - Only for jobs assigned to current user
    $jobStatsQuery = "
        SELECT 
            COUNT(*) as total_jobs,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active_jobs,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
            SUM(CASE WHEN status = 'added' THEN 1 ELSE 0 END) as pending_jobs
        FROM jobs
        WHERE assigned_to = :user_id
    ";
    
    $jobStatsStmt = $pdo->prepare($jobStatsQuery);
    $jobStatsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $jobStatsStmt->execute();
    $jobStats = $jobStatsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get total vendors count - Show all vendors (overall system)
    $totalVendorsQuery = "SELECT COUNT(*) as total_vendors FROM vendors";
    $totalVendorsStmt = $pdo->prepare($totalVendorsQuery);
    $totalVendorsStmt->execute();
    $totalVendors = $totalVendorsStmt->fetch(PDO::FETCH_ASSOC)['total_vendors'];

    // Get SLA reminders count (jobs with SLA deadline within 2 days) - Only for assigned jobs
    $slaRemindersCountQuery = "
        SELECT COUNT(*) as sla_reminders 
        FROM jobs 
        WHERE assigned_to = :user_id
        AND job_sla > NOW() 
        AND TIMESTAMPDIFF(HOUR, NOW(), job_sla) <= 48
        AND status IN ('added', 'in_progress')
    ";
    $slaRemindersCountStmt = $pdo->prepare($slaRemindersCountQuery);
    $slaRemindersCountStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $slaRemindersCountStmt->execute();
    $slaRemindersCount = $slaRemindersCountStmt->fetch(PDO::FETCH_ASSOC)['sla_reminders'];
    
    // Get SLA reminders details (only jobs with SLA within 48 hours) - Only for assigned jobs
    $slaRemindersQuery = "
        SELECT 
            j.id,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            j.job_sla as sla_deadline,
            j.status,
            TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) as hours_remaining,
            TIMESTAMPDIFF(DAY, NOW(), j.job_sla) as days_remaining,
            CASE 
                WHEN TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 24 THEN 'urgent'
                WHEN TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 48 THEN 'warning'
                ELSE 'normal'
            END as reminder_type
        FROM jobs j
        WHERE j.assigned_to = :user_id
        AND j.status IN ('in_progress', 'added')
        AND j.job_sla > NOW()
        AND TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 48
        ORDER BY j.job_sla ASC
        LIMIT 5
    ";
    
    $slaStmt = $pdo->prepare($slaRemindersQuery);
    $slaStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $slaStmt->execute();
    $slaReminders = $slaStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent jobs - Only for jobs assigned to current user
    $recentJobsQuery = "
        SELECT 
            j.id,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            j.status,
            j.created_at,
            j.job_sla as sla_deadline,
            TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) as hours_remaining
        FROM jobs j
        WHERE j.assigned_to = :user_id
        ORDER BY j.created_at DESC
        LIMIT 5
    ";

    $recentJobsStmt = $pdo->prepare($recentJobsQuery);
    $recentJobsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $recentJobsStmt->execute();
    $recentJobs = $recentJobsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent notifications - Only for jobs assigned to current user
    $recentNotificationsQuery = "
        SELECT 
            n.id,
            n.type,
            n.message,
            n.created_at,
            n.is_read,
            n.action_required
        FROM notifications n
        LEFT JOIN jobs j ON n.job_id = j.id
        WHERE n.notify_for = 'user' 
        AND n.user_id = :user_id
        AND (n.job_id IS NULL OR j.assigned_to = :user_id)
        ORDER BY n.created_at DESC
        LIMIT 5
    ";

    $notificationsStmt = $pdo->prepare($recentNotificationsQuery);
    $notificationsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $notificationsStmt->execute();
    $recentNotifications = $notificationsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format SLA reminders
    $formattedSlaReminders = [];
    foreach ($slaReminders as $reminder) {
        $timeRemaining = '';
        if ($reminder['hours_remaining'] <= 24) {
            $timeRemaining = $reminder['hours_remaining'] . ' hours remaining';
        } else {
            $timeRemaining = $reminder['days_remaining'] . ' days remaining';
        }

        $formattedSlaReminders[] = [
            'id' => $reminder['id'],
            'job_name' => $reminder['job_name'],
            'job_number' => $reminder['job_number'],
            'time_remaining' => $timeRemaining,
            'reminder_type' => $reminder['reminder_type'],
            'sla_deadline' => $reminder['sla_deadline']
        ];
    }

    // Format recent jobs
    $formattedRecentJobs = [];
    foreach ($recentJobs as $job) {
        $formattedRecentJobs[] = [
            'id' => $job['id'],
            'job_name' => $job['job_name'],
            'job_number' => $job['job_number'],
            'status' => $job['status'],
            'created_at' => $job['created_at'],
            'hours_remaining' => $job['hours_remaining']
        ];
    }

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

    // Format recent notifications
    $formattedNotifications = [];
    foreach ($recentNotifications as $notification) {
        $formattedNotifications[] = [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'message' => $notification['message'],
            'created_at' => $notification['created_at'],
            'is_read' => $notification['is_read'],
            'action_required' => $notification['action_required']
        ];
    }

    // Get unread messages count
    $unreadMessagesQuery = "
        SELECT COUNT(*) as unread_count
        FROM messages 
        WHERE receiver_id = :user_id AND is_read = FALSE
    ";
    $unreadMessagesStmt = $pdo->prepare($unreadMessagesQuery);
    $unreadMessagesStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $unreadMessagesStmt->execute();
    $unreadMessagesCount = $unreadMessagesStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Get unread notifications count
    $unreadNotificationsQuery = "
        SELECT COUNT(*) as unread_count
        FROM notifications 
        WHERE notify_for = 'user' AND user_id = :user_id AND is_read = FALSE
    ";
    $unreadNotificationsStmt = $pdo->prepare($unreadNotificationsQuery);
    $unreadNotificationsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $unreadNotificationsStmt->execute();
    $unreadNotificationsCount = $unreadNotificationsStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    echo json_encode([
        'success' => true,
        'data' => [
            'total_jobs' => (int) $jobStats['total_jobs'],
            'total_vendors' => (int) $totalVendors,
            'sla_reminders' => (int) $slaRemindersCount,
            'completed_jobs' => (int) $completedJobs,
            'sla_reminders_details' => $formattedSlaReminders,
            'recent_jobs' => $formattedRecentJobs,
            'recent_notifications' => $formattedNotifications,
            'total_unread_messages' => (int) $unreadMessagesCount,
            'total_unread_notifications' => (int) $unreadNotificationsCount
        ]
    ]);

} catch (PDOException $e) {
    error_log("Dashboard Data Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Dashboard Data Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>