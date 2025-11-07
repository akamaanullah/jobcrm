<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    // Start session
    session_start();

    // Check if admin is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    $pdo = connectDatabase();

    // Get pending payment requests that are within 3 days and need reminders
    // Check for requests created in last 3 days that haven't been accepted/rejected
    $paymentRemindersQuery = "
        SELECT 
            rp.id as request_id,
            rp.job_id,
            rp.user_id,
            rp.payment_platform,
            rp.payment_link_invoice_url,
            rp.zelle_email_phone,
            rp.business_name,
            rp.first_name,
            rp.last_name,
            rp.created_at as request_created_at,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            u.first_name as user_first_name,
            u.last_name as user_last_name,
            v.vendor_name,
            TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) as hours_since_request,
            TIMESTAMPDIFF(MINUTE, rp.created_at, NOW()) as minutes_since_request,
            CASE 
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 72 THEN 'overdue'
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 48 THEN 'urgent'
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 24 THEN 'warning'
                ELSE 'normal'
            END as reminder_priority
        FROM request_payments rp
        INNER JOIN jobs j ON rp.job_id = j.id
        INNER JOIN users u ON rp.user_id = u.id
        INNER JOIN vendors v ON j.id = v.job_id AND v.status = 'requested_vendor_payment'
        WHERE rp.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        AND v.status = 'requested_vendor_payment'
        ORDER BY rp.created_at ASC
    ";

    $paymentRemindersStmt = $pdo->prepare($paymentRemindersQuery);
    $paymentRemindersStmt->execute();
    $paymentReminders = $paymentRemindersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the reminders with time remaining info
    $formattedReminders = [];
    foreach ($paymentReminders as $reminder) {
        $hoursRemaining = 72 - $reminder['hours_since_request'];
        $minutesRemaining = (72 * 60) - $reminder['minutes_since_request'];
        
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

        // Format payment details
        $paymentDetails = '';
        if ($reminder['payment_platform'] === 'payment_link_invoice') {
            $paymentDetails = 'Payment Link: ' . substr($reminder['payment_link_invoice_url'], 0, 50) . '...';
        } elseif ($reminder['payment_platform'] === 'zelle') {
            $paymentDetails = 'Zelle: ' . $reminder['zelle_email_phone'];
            if ($reminder['business_name']) {
                $paymentDetails .= ' (' . $reminder['business_name'] . ')';
            } elseif ($reminder['first_name'] && $reminder['last_name']) {
                $paymentDetails .= ' (' . $reminder['first_name'] . ' ' . $reminder['last_name'] . ')';
            }
        }

        $formattedReminders[] = [
            'id' => $reminder['request_id'],
            'job_id' => $reminder['job_id'],
            'job_name' => $reminder['job_name'],
            'job_number' => $reminder['job_number'],
            'user_name' => $reminder['user_first_name'] . ' ' . $reminder['user_last_name'],
            'vendor_name' => $reminder['vendor_name'],
            'payment_platform' => $reminder['payment_platform'],
            'payment_details' => $paymentDetails,
            'request_created_at' => $reminder['request_created_at'],
            'hours_since_request' => $reminder['hours_since_request'],
            'minutes_since_request' => $reminder['minutes_since_request'],
            'time_remaining' => $timeRemaining,
            'reminder_priority' => $reminder['reminder_priority']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'payment_reminders' => $formattedReminders,
            'total_pending' => count($formattedReminders),
            'urgent_count' => count(array_filter($formattedReminders, function($r) { return $r['reminder_priority'] === 'urgent'; })),
            'overdue_count' => count(array_filter($formattedReminders, function($r) { return $r['reminder_priority'] === 'overdue'; }))
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>