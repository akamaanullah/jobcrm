<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDB();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'check_and_create':
            checkAndCreateInvoiceReminders($pdo);
            break;
        case 'mark_sent':
            markInvoiceReminderAsSent($pdo);
            break;
        case 'get_pending':
            getPendingInvoiceReminders($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function checkAndCreateInvoiceReminders($pdo) {
    // Get all accepted payment requests that need invoice reminders
    $query = "
        SELECT 
            rp.id as payment_request_id,
            rp.job_id,
            rp.user_id,
            rp.created_at,
            TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) as hours_since_acceptance,
            CASE 
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 72 THEN 'overdue'
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 48 THEN 'urgent'
                WHEN TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) >= 24 THEN 'urgent'
                ELSE 'pending'
            END as reminder_type
        FROM request_payments rp
        INNER JOIN vendors v ON rp.job_id = v.job_id
        WHERE v.status = 'vendor_payment_accepted'
        AND rp.id NOT IN (
            SELECT payment_request_id FROM invoice_reminders 
            WHERE notification_sent = 1 
            AND reminder_type IN ('urgent', 'overdue', 'pending')
        )
        AND rp.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $createdReminders = [];
    
    foreach ($requests as $request) {
        // Get vendor ID for this payment request
        $vendorQuery = "SELECT id FROM vendors WHERE job_id = :job_id AND status = 'vendor_payment_accepted' LIMIT 1";
        $vendorStmt = $pdo->prepare($vendorQuery);
        $vendorStmt->bindParam(':job_id', $request['job_id']);
        $vendorStmt->execute();
        $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($vendor) {
            // Create invoice reminder
            $insertQuery = "
                INSERT INTO invoice_reminders (job_id, payment_request_id, vendor_id, reminder_type, notification_sent, created_at, updated_at)
                VALUES (:job_id, :payment_request_id, :vendor_id, :reminder_type, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                reminder_type = VALUES(reminder_type),
                notification_sent = 0,
                updated_at = CURRENT_TIMESTAMP
            ";
            
            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->bindParam(':job_id', $request['job_id']);
            $insertStmt->bindParam(':payment_request_id', $request['payment_request_id']);
            $insertStmt->bindParam(':vendor_id', $vendor['id']);
            $insertStmt->bindParam(':reminder_type', $request['reminder_type']);
            $insertStmt->execute();
            
            $createdReminders[] = [
                'job_id' => $request['job_id'],
                'payment_request_id' => $request['payment_request_id'],
                'reminder_type' => $request['reminder_type']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Invoice reminders checked and created',
        'created_count' => count($createdReminders),
        'reminders' => $createdReminders
    ]);
}

function markInvoiceReminderAsSent($pdo) {
    $reminderId = $_GET['reminder_id'] ?? '';
    
    if (!$reminderId) {
        echo json_encode(['success' => false, 'message' => 'Reminder ID required']);
        return;
    }
    
    $updateQuery = "
        UPDATE invoice_reminders 
        SET notification_sent = 1, sent_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
        WHERE id = :reminder_id
    ";
    
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':reminder_id', $reminderId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Invoice reminder marked as sent'
    ]);
}

function getPendingInvoiceReminders($pdo) {
    // Get all pending invoice reminders with job and vendor details
    $query = "
        SELECT 
            ir.id,
            ir.job_id,
            ir.payment_request_id,
            ir.vendor_id,
            ir.reminder_type,
            ir.notification_sent,
            ir.sent_at,
            ir.created_at,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            v.vendor_name,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            rp.payment_platform,
            rp.created_at as payment_accepted_at,
            TIMESTAMPDIFF(HOUR, rp.created_at, NOW()) as hours_since_acceptance,
            TIMESTAMPDIFF(DAY, rp.created_at, NOW()) as days_since_acceptance
        FROM invoice_reminders ir
        JOIN jobs j ON ir.job_id = j.id
        JOIN vendors v ON ir.vendor_id = v.id
        JOIN request_payments rp ON ir.payment_request_id = rp.id
        JOIN users u ON rp.user_id = u.id
        WHERE v.status = 'vendor_payment_accepted'
        AND ir.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY ir.created_at DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formattedReminders = [];
    
    foreach ($reminders as $reminder) {
        // Calculate time remaining/overdue
        $timeRemaining = '';
        if ($reminder['days_since_acceptance'] > 0) {
            $timeRemaining = $reminder['days_since_acceptance'] . ' day' . ($reminder['days_since_acceptance'] > 1 ? 's' : '') . ' since payment accepted';
        } else {
            $timeRemaining = $reminder['hours_since_acceptance'] . ' hour' . ($reminder['hours_since_acceptance'] > 1 ? 's' : '') . ' since payment accepted';
        }
        
        $formattedReminders[] = [
            'id' => $reminder['id'],
            'job_name' => $reminder['job_name'],
            'job_number' => $reminder['job_number'],
            'vendor_name' => $reminder['vendor_name'],
            'user_name' => $reminder['user_name'],
            'payment_platform' => $reminder['payment_platform'],
            'payment_accepted_at' => $reminder['payment_accepted_at'],
            'time_since_acceptance' => $timeRemaining,
            'reminder_type' => $reminder['reminder_type'],
            'notification_sent' => $reminder['notification_sent'],
            'sent_at' => $reminder['sent_at'],
            'created_at' => $reminder['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'invoice_reminders' => $formattedReminders,
        'total_count' => count($formattedReminders)
    ]);
}
?>
