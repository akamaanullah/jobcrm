<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDB();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'check_and_create':
            checkAndCreatePaymentReminders($pdo);
            break;
        case 'mark_sent':
            markPaymentReminderAsSent($pdo);
            break;
        case 'get_pending':
            getPendingPaymentReminders($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function checkAndCreatePaymentReminders($pdo) {
    // Get ALL payment requests within 3 days that need reminders (including immediate ones)
    $query = "
        SELECT 
            rp.id as request_id,
            rp.created_at,
            TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) as hours_since_request,
            CASE 
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 72 THEN 'overdue'
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 48 THEN 'urgent'
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 24 THEN 'warning'
                ELSE 'pending'
            END as reminder_type
        FROM request_payments rp
        INNER JOIN vendors v ON rp.job_id = v.job_id
        WHERE rp.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        AND v.status = 'requested_vendor_payment'
        AND rp.id NOT IN (
            SELECT request_payment_id FROM payment_reminders 
            WHERE notification_sent = 1 
            AND reminder_type IN ('urgent', 'warning', 'overdue', 'pending')
        )
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $createdReminders = [];
    
    foreach ($requests as $request) {
        // Create reminder for ALL payment requests (including 'pending' ones)
        // Insert reminder record
        $insertQuery = "
            INSERT INTO payment_reminders (request_payment_id, reminder_type, notification_sent) 
            VALUES (?, ?, 0)
            ON DUPLICATE KEY UPDATE 
            reminder_type = VALUES(reminder_type),
            notification_sent = 0,
            updated_at = CURRENT_TIMESTAMP
        ";
        
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$request['request_id'], $request['reminder_type']]);
        
        $createdReminders[] = [
            'request_id' => $request['request_id'],
            'reminder_type' => $request['reminder_type'],
            'hours_since_request' => $request['hours_since_request']
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'reminders' => $createdReminders,
        'count' => count($createdReminders)
    ]);
}

function markPaymentReminderAsSent($pdo) {
    $requestId = $_POST['request_id'] ?? '';
    $reminderType = $_POST['reminder_type'] ?? '';
    
    if (!$requestId || !$reminderType) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    $query = "
        UPDATE payment_reminders 
        SET notification_sent = 1, sent_at = NOW() 
        WHERE request_payment_id = ? AND reminder_type = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([$requestId, $reminderType]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Payment reminder marked as sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update payment reminder']);
    }
}

function getPendingPaymentReminders($pdo) {
    $query = "
        SELECT 
            pr.request_payment_id,
            rp.job_id,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            u.first_name as user_first_name,
            u.last_name as user_last_name,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            v.vendor_name,
            rp.created_at,
            TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) as hours_since_request,
            pr.reminder_type
        FROM payment_reminders pr
        JOIN request_payments rp ON pr.request_payment_id = rp.id
        JOIN jobs j ON rp.job_id = j.id
        JOIN users u ON rp.user_id = u.id
        JOIN vendors v ON rp.job_id = v.job_id
        WHERE pr.notification_sent = 0
        AND v.status = 'requested_vendor_payment'
        AND rp.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        ORDER BY rp.created_at ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'reminders' => $reminders,
        'count' => count($reminders)
    ]);
}
?>
