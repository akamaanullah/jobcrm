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

    // Get total users count
    $usersSql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
    $usersStmt = $pdo->prepare($usersSql);
    $usersStmt->execute();
    $totalUsers = $usersStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Get total jobs count (all jobs in the system)
    $jobsSql = "SELECT COUNT(*) as total_jobs FROM jobs";
    $jobsStmt = $pdo->prepare($jobsSql);
    $jobsStmt->execute();
    $totalJobs = $jobsStmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];

    // Get pending jobs count (jobs with status 'added' - not started yet)
    $pendingJobsSql = "SELECT COUNT(*) as pending_jobs FROM jobs WHERE status = 'added'";
    $pendingJobsStmt = $pdo->prepare($pendingJobsSql);
    $pendingJobsStmt->execute();
    $pendingJobs = $pendingJobsStmt->fetch(PDO::FETCH_ASSOC)['pending_jobs'];

    // Get pending approvals count (notifications that require action)
    $approvalsSql = "
        SELECT COUNT(*) as pending_approvals 
        FROM notifications 
        WHERE action_required = 1 AND is_read = 0
    ";
    $approvalsStmt = $pdo->prepare($approvalsSql);
    $approvalsStmt->execute();
    $pendingApprovals = $approvalsStmt->fetch(PDO::FETCH_ASSOC)['pending_approvals'];

    // Get SLA reminders count from sla_reminders table (all reminders within 48 hours)
    $slaRemindersCountQuery = "
        SELECT COUNT(*) as sla_reminders 
        FROM sla_reminders sr
        JOIN jobs j ON sr.job_id = j.id
        WHERE j.status IN ('added', 'in_progress')
        AND j.job_sla > NOW()
        AND TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 48
    ";
    $slaRemindersCountStmt = $pdo->prepare($slaRemindersCountQuery);
    $slaRemindersCountStmt->execute();
    $slaRemindersCount = $slaRemindersCountStmt->fetch(PDO::FETCH_ASSOC)['sla_reminders'];

    // Get SLA reminders details from sla_reminders table (all reminders within 48 hours)
    $slaRemindersQuery = "
        SELECT 
            sr.job_id as id,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            j.job_sla as sla_deadline,
            j.status,
            TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) as hours_remaining,
            TIMESTAMPDIFF(DAY, NOW(), j.job_sla) as days_remaining,
            sr.reminder_type,
            sr.notification_sent,
            sr.sent_at
        FROM sla_reminders sr
        JOIN jobs j ON sr.job_id = j.id
        WHERE j.status IN ('in_progress', 'added')
        AND j.job_sla > NOW()
        AND TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 48
        ORDER BY j.job_sla ASC
        LIMIT 5
    ";

    $slaStmt = $pdo->prepare($slaRemindersQuery);
    $slaStmt->execute();
    $slaReminders = $slaStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent jobs (last 5 jobs)
    $recentJobsSql = "
        SELECT 
            j.id,
            j.store_name,
            j.job_type,
            j.status,
            j.created_at,
            COUNT(v.id) as vendor_count
        FROM jobs j
        LEFT JOIN vendors v ON j.id = v.job_id
        GROUP BY j.id
        ORDER BY j.created_at DESC
        LIMIT 5
    ";
    $recentJobsStmt = $pdo->prepare($recentJobsSql);
    $recentJobsStmt->execute();
    $recentJobs = $recentJobsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format recent jobs
    foreach ($recentJobs as &$job) {
        $job['client_name'] = 'System User'; // Since we don't have created_by in jobs table
        $job['time_ago'] = getTimeAgo($job['created_at']);
        $job['status_badge'] = getStatusBadge($job['status']);
    }

    // Get recent notifications (last 5 notifications)
    $recentNotificationsSql = "
        SELECT 
            n.id,
            n.type,
            n.message,
            n.created_at,
            n.is_read
        FROM notifications n
        ORDER BY n.created_at DESC
        LIMIT 5
    ";
    $recentNotificationsStmt = $pdo->prepare($recentNotificationsSql);
    $recentNotificationsStmt->execute();
    $recentNotifications = $recentNotificationsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format recent notifications
    foreach ($recentNotifications as &$notification) {
        $notification['time_ago'] = getTimeAgo($notification['created_at']);
        $notification['icon'] = getNotificationIcon($notification['type']);
        $notification['title'] = getNotificationTitle($notification['type']);
    }

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
            'sla_deadline' => $reminder['sla_deadline'],
            'notification_sent' => $reminder['notification_sent'],
            'sent_at' => $reminder['sent_at']
        ];
    }

    // Get total unread messages count for admin
    $unreadMessagesSql = "
        SELECT COUNT(*) as total_unread_messages 
        FROM messages m 
        INNER JOIN vendors v ON m.vendor_id = v.id 
        INNER JOIN jobs j ON v.job_id = j.id 
        WHERE m.is_read = 0 
        AND m.receiver_id = :admin_id
    ";
    $unreadMessagesStmt = $pdo->prepare($unreadMessagesSql);
    $unreadMessagesStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $unreadMessagesStmt->execute();
    $totalUnreadMessages = $unreadMessagesStmt->fetch(PDO::FETCH_ASSOC)['total_unread_messages'];

    // Get invoice reminders count
    // Only count reminders where invoice has NOT been generated yet
    $invoiceRemindersSql = "
        SELECT COUNT(*) as invoice_reminders_count
        FROM invoice_reminders ir
        JOIN vendors v ON ir.vendor_id = v.id
        LEFT JOIN invoices i ON ir.job_id = i.job_id
        WHERE v.status = 'vendor_payment_accepted'
        AND ir.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND i.id IS NULL
        AND ir.notification_sent = 0
    ";
    $invoiceRemindersStmt = $pdo->prepare($invoiceRemindersSql);
    $invoiceRemindersStmt->execute();
    $invoiceRemindersCount = $invoiceRemindersStmt->fetch(PDO::FETCH_ASSOC)['invoice_reminders_count'];

    // Get invoice reminders details from invoice_reminders table
    // Only show reminders where invoice has NOT been generated yet
    $invoiceRemindersQuery = "
        SELECT 
            ir.id,
            ir.job_id,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            v.vendor_name,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            rp.payment_platform,
            rp.created_at as payment_accepted_at,
            TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) as hours_since_acceptance,
            TIMESTAMPDIFF(DAY, rp.created_at, NOW()) as days_since_acceptance,
            ir.reminder_type,
            ir.notification_sent,
            ir.sent_at
        FROM invoice_reminders ir
        JOIN jobs j ON ir.job_id = j.id
        JOIN vendors v ON ir.vendor_id = v.id
        JOIN request_payments rp ON ir.payment_request_id = rp.id
        JOIN users u ON rp.user_id = u.id
        LEFT JOIN invoices i ON ir.job_id = i.job_id
        WHERE v.status = 'vendor_payment_accepted'
        AND ir.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND i.id IS NULL
        AND ir.notification_sent = 0
        ORDER BY ir.created_at DESC
        LIMIT 5
    ";

    $invoiceRemindersStmt = $pdo->prepare($invoiceRemindersQuery);
    $invoiceRemindersStmt->execute();
    $invoiceReminders = $invoiceRemindersStmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedInvoiceReminders = [];
    foreach ($invoiceReminders as $reminder) {
        // Calculate time since payment acceptance
        $timeSinceAcceptance = '';
        if ($reminder['days_since_acceptance'] > 0) {
            $timeSinceAcceptance = $reminder['days_since_acceptance'] . ' day' . ($reminder['days_since_acceptance'] > 1 ? 's' : '') . ' ago';
        } else {
            $timeSinceAcceptance = $reminder['hours_since_acceptance'] . ' hour' . ($reminder['hours_since_acceptance'] > 1 ? 's' : '') . ' ago';
        }

        $formattedInvoiceReminders[] = [
            'id' => $reminder['id'],
            'job_id' => $reminder['job_id'],
            'job_name' => $reminder['job_name'],
            'job_number' => $reminder['job_number'],
            'vendor_name' => $reminder['vendor_name'],
            'user_name' => $reminder['user_name'],
            'payment_platform' => $reminder['payment_platform'],
            'payment_accepted_at' => $reminder['payment_accepted_at'],
            'time_since_acceptance' => $timeSinceAcceptance,
            'reminder_type' => $reminder['reminder_type'],
            'notification_sent' => $reminder['notification_sent'],
            'sent_at' => $reminder['sent_at']
        ];
    }

    // Get total unread notifications count for admin
    $unreadNotificationsSql = "
        SELECT COUNT(*) as total_unread_notifications 
        FROM notifications 
        WHERE is_read = 0 
        AND notify_for = 'admin'
    ";
    $unreadNotificationsStmt = $pdo->prepare($unreadNotificationsSql);
    $unreadNotificationsStmt->execute();
    $totalUnreadNotifications = $unreadNotificationsStmt->fetch(PDO::FETCH_ASSOC)['total_unread_notifications'];

    // Get payment reminders count from payment_reminders table (all reminders within 3 days)
    $paymentRemindersCountQuery = "
        SELECT COUNT(*) as payment_reminders 
        FROM payment_reminders pr
        JOIN request_payments rp ON pr.request_payment_id = rp.id
        JOIN vendors v ON rp.job_id = v.job_id
        WHERE v.status = 'requested_vendor_payment'
        AND rp.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
    ";
    $paymentRemindersCountStmt = $pdo->prepare($paymentRemindersCountQuery);
    $paymentRemindersCountStmt->execute();
    $paymentRemindersCount = $paymentRemindersCountStmt->fetch(PDO::FETCH_ASSOC)['payment_reminders'];

    // Get payment reminders details from payment_reminders table (all reminders within 3 days)
    $paymentRemindersQuery = "
        SELECT 
            pr.request_payment_id as id,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            u.first_name as user_first_name,
            u.last_name as user_last_name,
            v.vendor_name,
            rp.payment_platform,
            rp.payment_link_invoice_url,
            rp.zelle_email_phone,
            rp.business_name,
            rp.first_name as vendor_first_name,
            rp.last_name as vendor_last_name,
            rp.created_at as request_created_at,
            TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) as hours_since_request,
            TIMESTAMPDIFF(DAY, rp.created_at, NOW()) as days_since_request,
            pr.reminder_type,
            pr.notification_sent,
            pr.sent_at
        FROM payment_reminders pr
        JOIN request_payments rp ON pr.request_payment_id = rp.id
        JOIN jobs j ON rp.job_id = j.id
        JOIN users u ON rp.user_id = u.id
        JOIN vendors v ON j.id = v.job_id
        WHERE v.status = 'requested_vendor_payment'
        AND rp.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        ORDER BY rp.created_at ASC
        LIMIT 5
    ";

    $paymentRemindersStmt = $pdo->prepare($paymentRemindersQuery);
    $paymentRemindersStmt->execute();
    $paymentReminders = $paymentRemindersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format payment reminders
    $formattedPaymentReminders = [];
    foreach ($paymentReminders as $reminder) {
        $hoursRemaining = 72 - $reminder['hours_since_request'];
        $timeRemaining = '';

        if ($hoursRemaining > 24) {
            $days = floor($hoursRemaining / 24);
            $hours = $hoursRemaining % 24;
            $timeRemaining = $days . ' day' . ($days > 1 ? 's' : '') . ' ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' remaining';
        } elseif ($hoursRemaining > 0) {
            $timeRemaining = $hoursRemaining . ' hour' . ($hoursRemaining > 1 ? 's' : '') . ' remaining';
        } else {
            $timeRemaining = 'OVERDUE - ' . abs($hoursRemaining) . ' hour' . (abs($hoursRemaining) > 1 ? 's' : '') . ' past deadline';
        }

        // Payment details removed - keeping display simple

        $formattedPaymentReminders[] = [
            'id' => $reminder['id'],
            'job_name' => $reminder['job_name'],
            'job_number' => $reminder['job_number'],
            'user_name' => $reminder['user_first_name'] . ' ' . $reminder['user_last_name'],
            'vendor_name' => $reminder['vendor_name'],
            'request_created_at' => $reminder['request_created_at'],
            'time_remaining' => $timeRemaining,
            'reminder_type' => $reminder['reminder_type'],
            'notification_sent' => $reminder['notification_sent'],
            'sent_at' => $reminder['sent_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => [
                'total_users' => (int) $totalUsers,
                'total_jobs' => (int) $totalJobs,
                'pending_jobs' => (int) $pendingJobs,
                'pending_approvals' => (int) $pendingApprovals,
                'sla_reminders' => (int) $slaRemindersCount,
                'payment_reminders' => (int) $paymentRemindersCount,
                'invoice_reminders' => (int) $invoiceRemindersCount
            ],
            'recent_jobs' => $recentJobs,
            'recent_notifications' => $recentNotifications,
            'sla_reminders_details' => $formattedSlaReminders,
            'payment_reminders_details' => $formattedPaymentReminders,
            'invoice_reminders_details' => $formattedInvoiceReminders,
            'total_unread_messages' => (int) $totalUnreadMessages,
            'total_unread_notifications' => (int) $totalUnreadNotifications
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Helper function to get time ago
function getTimeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) {
        return 'just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time < 31536000) {
        $months = floor($time / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

// Helper function to get status badge
function getStatusBadge($status)
{
    $badges = [
        'added' => ['class' => 'bg-info', 'text' => 'ADDED'],
        'in_progress' => ['class' => 'bg-warning', 'text' => 'IN PROGRESS'],
        'completed' => ['class' => 'bg-success', 'text' => 'COMPLETED']
    ];

    $badge = $badges[$status] ?? ['class' => 'bg-secondary', 'text' => strtoupper($status)];
    return $badge;
}

// Helper function to get notification icon
function getNotificationIcon($type)
{
    $icons = [
        'visit_request' => 'bi bi-eye',
        'final_visit_request' => 'bi bi-check-circle',
        'request_vendor_payment' => 'bi bi-credit-card',
        'vendor_added' => 'bi bi-person-plus',
        'job_completed' => 'bi bi-check-circle',
        'new_job_added' => 'bi bi-briefcase'
    ];
    return $icons[$type] ?? 'bi bi-bell';
}

// Helper function to get notification title
function getNotificationTitle($type)
{
    $titles = [
        'visit_request' => 'Visit Request',
        'final_visit_request' => 'Final Visit Request',
        'request_vendor_payment' => 'Payment Request',
        'vendor_added' => 'New Vendor',
        'job_completed' => 'Job Completed',
        'new_job_added' => 'New Job'
    ];
    return $titles[$type] ?? 'Notification';
}
?>