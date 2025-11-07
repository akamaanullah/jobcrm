<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDB();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'check_and_create':
            checkAndCreateNotificationAlerts($pdo);
            break;
        case 'mark_sent':
            markNotificationAlertAsSent($pdo);
            break;
        case 'get_pending':
            getPendingNotificationAlerts($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function checkAndCreateNotificationAlerts($pdo) {
    // Get unread notifications that need alerts (admin notifications only)
    $query = "
        SELECT 
            n.id,
            n.type,
            n.message,
            n.job_id,
            n.vendor_id,
            n.created_at,
            TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) as minutes_ago
        FROM notifications n
        WHERE n.notify_for = 'admin'
        AND n.is_read = 0
        AND n.alert_sent = 0
        AND n.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ORDER BY n.created_at DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $createdAlerts = [];
    
    foreach ($notifications as $notification) {
        // Skip if too old (older than 1 hour for non-urgent, 24 hours for urgent)
        $isUrgent = in_array($notification['type'], ['visit_request', 'final_visit_request', 'request_vendor_payment']);
        $maxAge = $isUrgent ? 1440 : 60; // 24 hours or 1 hour
        
        if ($notification['minutes_ago'] > $maxAge) {
            continue;
        }
        
        $createdAlerts[] = [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'message' => $notification['message'],
            'job_id' => $notification['job_id'],
            'vendor_id' => $notification['vendor_id'],
            'created_at' => $notification['created_at'],
            'minutes_ago' => $notification['minutes_ago']
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'alerts' => $createdAlerts,
        'count' => count($createdAlerts)
    ]);
}

function markNotificationAlertAsSent($pdo) {
    $notificationId = $_POST['notification_id'] ?? '';
    
    if (!$notificationId) {
        echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
        return;
    }
    
    $query = "
        UPDATE notifications 
        SET alert_sent = 1, alert_sent_at = NOW() 
        WHERE id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([$notificationId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Notification alert marked as sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update notification alert']);
    }
}

function getPendingNotificationAlerts($pdo) {
    $query = "
        SELECT 
            n.id,
            n.type,
            n.message,
            n.job_id,
            n.vendor_id,
            n.created_at,
            TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) as minutes_ago
        FROM notifications n
        WHERE n.notify_for = 'admin'
        AND n.is_read = 0
        AND n.alert_sent = 0
        AND n.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ORDER BY n.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'alerts' => $alerts,
        'count' => count($alerts)
    ]);
}
?>
