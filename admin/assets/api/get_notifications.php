<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$pdo = getDB();

try {
    // Get filter parameters
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $statsOnly = isset($_GET['stats_only']) && $_GET['stats_only'] === 'true';

    // If only stats are requested, return early
    if ($statsOnly) {
        $statsQuery = "
            SELECT 
                SUM(CASE WHEN action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as total_notifications,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
                SUM(CASE WHEN action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as pending_notifications
            FROM notifications 
            WHERE notify_for = 'admin'
        ";

        $statsStmt = $pdo->prepare($statsQuery);
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stats' => [
                'total_notifications' => (int) $stats['total_notifications'],
                'unread_notifications' => (int) $stats['unread_notifications'],
                'pending_notifications' => (int) $stats['pending_notifications']
            ]
        ]);
        exit;
    }

    // Base query for notifications
    $baseQuery = "
        SELECT 
            n.id,
            n.user_id,
            n.notify_for,
            n.type,
            n.job_id,
            n.vendor_id,
            n.message,
            n.is_read,
            n.action_required,
            n.created_at,
            n.updated_at,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            u.email as user_email,
            j.store_name as job_title,
            j.id as job_number,
            v.vendor_name,
            v.phone as vendor_phone,
            i.invoice_number
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        LEFT JOIN jobs j ON n.job_id = j.id
        LEFT JOIN vendors v ON n.vendor_id = v.id
        LEFT JOIN invoices i ON n.job_id = i.job_id AND n.type = 'invoice_reminder'
        WHERE n.notify_for = 'admin'
    ";

    $params = [];

    // Apply filters
    switch ($filter) {
        case 'visit':
            $baseQuery .= " AND n.type = 'visit_request'";
            break;
        case 'approval':
            $baseQuery .= " AND n.type = 'final_visit_request'";
            break;
        case 'payment':
            $baseQuery .= " AND n.type = 'request_vendor_payment'";
            break;
        case 'vendor':
            $baseQuery .= " AND n.type = 'vendor_added'";
            break;
        case 'completed':
            $baseQuery .= " AND n.type = 'job_completed'";
            break;
        case 'invoice':
            $baseQuery .= " AND n.type = 'invoice_reminder'";
            break;
        case 'unread':
            $baseQuery .= " AND n.is_read = 0";
            break;
        case 'pending':
            $baseQuery .= " AND n.action_required = 1 AND n.is_read = 0";
            break;
        case 'resolved':
            $baseQuery .= " AND n.action_required = 0";
            break;
    }

    // Apply search
    if (!empty($search)) {
        $baseQuery .= " AND (n.message LIKE :search OR j.title LIKE :search OR v.name LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Order by created_at DESC
    $baseQuery .= " ORDER BY n.created_at DESC";

    // Execute query
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $statsQuery = "
        SELECT 
            SUM(CASE WHEN action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN action_required = 0 THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN type = 'visit_request' AND action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as visit_requests,
            SUM(CASE WHEN type = 'final_visit_request' AND action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as final_approvals,
            SUM(CASE WHEN type = 'request_vendor_payment' AND action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as payment_requests,
            SUM(CASE WHEN type = 'vendor_added' AND is_read = 0 THEN 1 ELSE 0 END) as vendor_added,
            SUM(CASE WHEN type = 'job_completed' AND is_read = 0 THEN 1 ELSE 0 END) as job_completed,
            SUM(CASE WHEN type = 'invoice_reminder' AND is_read = 0 THEN 1 ELSE 0 END) as invoice_reminders
        FROM notifications 
        WHERE notify_for = 'admin'
    ";

    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Process notifications for display
    foreach ($notifications as &$notification) {
        $notification['time_ago'] = getTimeAgo($notification['created_at']);
        $notification['status_badge'] = getNotificationStatusBadge($notification);
        $notification['action_buttons'] = getNotificationActionButtons($notification);
    }

    echo json_encode([
        'success' => true,
        'data' => $notifications,
        'count' => count($notifications),
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("Get Notifications API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}

function getTimeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    // Handle negative time (future timestamps)
    if ($time < 0) {
        $time = abs($time);
    }

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

function getNotificationStatusBadge($notification)
{
    if ($notification['action_required'] == 1 && $notification['is_read'] == 0) {
        return '<span class="status-badge status-pending">PENDING</span>';
    } elseif ($notification['action_required'] == 0) {
        return '<span class="status-badge status-completed">RESOLVED</span>';
    } else {
        return '<span class="status-badge status-read">READ</span>';
    }
}

function getNotificationActionButtons($notification)
{
    $buttons = [];

    // Action required notifications get accept/reject buttons
    if ($notification['action_required'] == 1) {
        $buttons[] = [
            'type' => 'accept',
            'text' => 'Accept',
            'class' => 'btn-action btn-accept',
            'icon' => 'bi-check-circle'
        ];
        $buttons[] = [
            'type' => 'reject',
            'text' => 'Reject',
            'class' => 'btn-action btn-reject',
            'icon' => 'bi-x-circle'
        ];
    }

    // View form buttons for specific notification types
    switch ($notification['type']) {
        case 'final_visit_request':
            $buttons[] = [
                'type' => 'view_form',
                'text' => 'View Form',
                'class' => 'btn-action btn-view-form',
                'icon' => 'bi-eye',
                'modal' => 'finalVisitRequestModal'
            ];
            break;
        case 'job_completed':
            $buttons[] = [
                'type' => 'view_form',
                'text' => 'View Form',
                'class' => 'btn-action btn-view-form',
                'icon' => 'bi-eye',
                'modal' => 'jobCompletedModal'
            ];
            break;
        case 'request_vendor_payment':
            $buttons[] = [
                'type' => 'view_form',
                'text' => 'View Form',
                'class' => 'btn-action btn-view-form',
                'icon' => 'bi-eye',
                'modal' => 'paymentRequestModal'
            ];
            break;
    }

    // Job and vendor chat buttons for all notifications
    if ($notification['job_id']) {
        $buttons[] = [
            'type' => 'view_job',
            'text' => 'Job',
            'class' => 'btn-action btn-job',
            'icon' => 'bi-eye',
            'url' => 'view-job.php?id=' . $notification['job_id']
        ];
    }

    if ($notification['vendor_id']) {
        $buttons[] = [
            'type' => 'vendor_chat',
            'text' => 'Vendor Chat',
            'class' => 'btn-action btn-vendor',
            'icon' => 'bi-person',
            'modal' => 'chatModal'
        ];
    }

    return $buttons;
}
?>