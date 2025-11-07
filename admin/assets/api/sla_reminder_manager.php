<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDB();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'check_and_create':
            checkAndCreateSlaReminders($pdo);
            break;
        case 'mark_sent':
            markReminderAsSent($pdo);
            break;
        case 'get_pending':
            getPendingReminders($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function checkAndCreateSlaReminders($pdo) {
    // Get jobs with SLA within 48 hours that need reminders
    $query = "
        SELECT 
            j.id as job_id,
            j.store_name,
            j.job_sla,
            TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) as hours_remaining,
            CASE 
                WHEN TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 24 THEN 'urgent'
                WHEN TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 48 THEN 'warning'
                ELSE 'normal'
            END as reminder_type
        FROM jobs j
        WHERE j.status IN ('in_progress', 'added')
        AND j.job_sla > NOW()
        AND TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) <= 48
        AND j.id NOT IN (
            SELECT job_id FROM sla_reminders 
            WHERE notification_sent = 1 
            AND reminder_type IN ('urgent', 'warning')
        )
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $createdReminders = [];
    
    foreach ($jobs as $job) {
        if ($job['reminder_type'] !== 'normal') {
            // Insert reminder record
            $insertQuery = "
                INSERT INTO sla_reminders (job_id, reminder_type, notification_sent) 
                VALUES (?, ?, 0)
                ON DUPLICATE KEY UPDATE 
                reminder_type = VALUES(reminder_type),
                notification_sent = 0,
                updated_at = CURRENT_TIMESTAMP
            ";
            
            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->execute([$job['job_id'], $job['reminder_type']]);
            
            $createdReminders[] = [
                'job_id' => $job['job_id'],
                'job_name' => $job['store_name'],
                'job_number' => 'JOB-' . $job['job_id'],
                'reminder_type' => $job['reminder_type'],
                'hours_remaining' => $job['hours_remaining'],
                'sla_deadline' => $job['job_sla']
            ];
        }
    }
    
    echo json_encode([
        'success' => true, 
        'reminders' => $createdReminders,
        'count' => count($createdReminders)
    ]);
}

function markReminderAsSent($pdo) {
    $jobId = $_POST['job_id'] ?? '';
    $reminderType = $_POST['reminder_type'] ?? '';
    
    if (!$jobId || !$reminderType) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    $query = "
        UPDATE sla_reminders 
        SET notification_sent = 1, sent_at = NOW() 
        WHERE job_id = ? AND reminder_type = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([$jobId, $reminderType]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reminder marked as sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update reminder']);
    }
}

function getPendingReminders($pdo) {
    $query = "
        SELECT 
            sr.job_id,
            j.store_name as job_name,
            CONCAT('JOB-', j.id) as job_number,
            sr.reminder_type,
            j.job_sla as sla_deadline,
            TIMESTAMPDIFF(HOUR, NOW(), j.job_sla) as hours_remaining,
            TIMESTAMPDIFF(DAY, NOW(), j.job_sla) as days_remaining
        FROM sla_reminders sr
        JOIN jobs j ON sr.job_id = j.id
        WHERE sr.notification_sent = 0
        AND j.status IN ('in_progress', 'added')
        AND j.job_sla > NOW()
        ORDER BY j.job_sla ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format time remaining
    foreach ($reminders as &$reminder) {
        if ($reminder['hours_remaining'] <= 24) {
            $reminder['time_remaining'] = $reminder['hours_remaining'] . ' hours remaining';
        } else {
            $reminder['time_remaining'] = $reminder['days_remaining'] . ' days remaining';
        }
    }
    
    echo json_encode([
        'success' => true, 
        'reminders' => $reminders,
        'count' => count($reminders)
    ]);
}
?>
