<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    // Start session
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('Unauthorized access');
    }

    $pdo = connectDatabase();

    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? 'all';
    $sortBy = $_GET['sort_by'] ?? 'created_at_desc';
    $limit = $_GET['limit'] ?? 50;
    $statsOnly = isset($_GET['stats_only']) && $_GET['stats_only'] === 'true';

    $userId = $_SESSION['user_id'];

    // If only stats are requested, return early
    if ($statsOnly) {
        $statsQuery = "
            SELECT 
                COUNT(*) as total_notifications,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
                SUM(CASE WHEN action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as pending_notifications,
                SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as resolved_notifications,
                SUM(CASE WHEN type IN ('request_visit_accepted', 'final_visit_request_accepted') THEN 1 ELSE 0 END) as visit_requests,
                SUM(CASE WHEN type IN ('request_vendor_payment', 'vendor_payment_accepted', 'vendor_payment_rejected', 'partial_payment_requested', 'partial_payment_accepted', 'partial_payment_rejected') THEN 1 ELSE 0 END) as payment_requests,
                SUM(CASE WHEN type IN ('vendor_payment_accepted', 'partial_payment_accepted') THEN 1 ELSE 0 END) as payment_ready,
                SUM(CASE WHEN type IN ('final_visit_request', 'final_visit_request_accepted', 'final_visit_request_rejected') THEN 1 ELSE 0 END) as final_approvals,
                SUM(CASE WHEN type = 'job_completed' THEN 1 ELSE 0 END) as job_completed,
                SUM(CASE WHEN type IN ('visit_request_rejected', 'final_visit_request_rejected', 'vendor_payment_rejected', 'partial_payment_rejected') THEN 1 ELSE 0 END) as rejected_requests
            FROM notifications n
            LEFT JOIN jobs j ON n.job_id = j.id
            WHERE n.notify_for = 'user' AND n.user_id = :user_id
            AND (n.job_id IS NULL OR j.assigned_to = :user_id)
        ";

        $statsStmt = $pdo->prepare($statsQuery);
        $statsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stats' => [
                'total_notifications' => (int) $stats['total_notifications'],
                'unread_notifications' => (int) $stats['unread_notifications'],
                'pending_notifications' => (int) $stats['pending_notifications'],
                'resolved_notifications' => (int) $stats['resolved_notifications'],
                'visit_requests' => (int) $stats['visit_requests'],
                'payment_requests' => (int) $stats['payment_requests'],
                'payment_ready' => (int) $stats['payment_ready'],
                'final_approvals' => (int) $stats['final_approvals'],
                'job_completed' => (int) $stats['job_completed'],
                'rejected_requests' => (int) $stats['rejected_requests']
            ]
        ]);
        exit;
    }

    // Build base query - Only show notifications for jobs assigned to current user
    $baseQuery = "
        SELECT 
            n.id,
            n.type,
            n.message,
            n.is_read,
            n.action_required,
            n.created_at,
            n.job_id,
            n.vendor_id,
            j.store_name as job_name,
            j.id as job_number,
            v.vendor_name,
            v.phone as vendor_phone
        FROM notifications n
        LEFT JOIN jobs j ON n.job_id = j.id
        LEFT JOIN vendors v ON n.vendor_id = v.id
        WHERE n.notify_for = 'user' AND n.user_id = :user_id
        AND (n.job_id IS NULL OR j.assigned_to = :user_id)
    ";

    $params = [':user_id' => $userId];

    // Apply search filter
    if (!empty($search)) {
        $baseQuery .= " AND (
            n.message LIKE :search OR 
            j.store_name LIKE :search OR 
            v.vendor_name LIKE :search
        )";
        $params[':search'] = "%$search%";
    }

    // Apply type filter
    if ($filter !== 'all') {
        switch ($filter) {
            case 'accepted':
                $baseQuery .= " AND n.type IN ('request_visit_accepted', 'final_visit_request_accepted', 'vendor_payment_accepted')";
                break;
            case 'visit':
                $baseQuery .= " AND n.type IN ('request_visit_accepted', 'final_visit_request_accepted')";
                break;
            case 'payment':
                $baseQuery .= " AND n.type IN ('request_vendor_payment', 'vendor_payment_accepted', 'vendor_payment_rejected', 'partial_payment_requested', 'partial_payment_accepted', 'partial_payment_rejected')";
                break;
            case 'approval':
                $baseQuery .= " AND n.type IN ('final_visit_request', 'final_visit_request_accepted', 'final_visit_request_rejected')";
                break;
            case 'completed':
                $baseQuery .= " AND n.type = 'job_completed'";
                break;
            case 'rejected':
                $baseQuery .= " AND n.type IN ('visit_request_rejected', 'final_visit_request_rejected', 'vendor_payment_rejected', 'partial_payment_rejected')";
                break;
        }
    }

    // Apply sorting
    switch ($sortBy) {
        case 'created_at_asc':
            $baseQuery .= " ORDER BY n.created_at ASC";
            break;
        case 'type_asc':
            $baseQuery .= " ORDER BY n.type ASC";
            break;
        case 'is_read_asc':
            $baseQuery .= " ORDER BY n.is_read ASC, n.created_at DESC";
            break;
        default: // created_at_desc
            $baseQuery .= " ORDER BY n.created_at DESC";
            break;
    }

    // Add limit
    $baseQuery .= " LIMIT :limit";
    $params[':limit'] = (int) $limit;

    // Execute main query
    $stmt = $pdo->prepare($baseQuery);
    foreach ($params as $key => $value) {
        if ($key === ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total_notifications,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
            SUM(CASE WHEN action_required = 1 AND is_read = 0 THEN 1 ELSE 0 END) as pending_notifications,
            SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as resolved_notifications
        FROM notifications 
        WHERE notify_for = 'user' AND user_id = :user_id
    ";

    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Format notifications data
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'message' => $notification['message'],
            'is_read' => (bool) $notification['is_read'],
            'action_required' => (bool) $notification['action_required'],
            'created_at' => $notification['created_at'],
            'created_ago' => getTimeAgo($notification['created_at']),
            'job_id' => $notification['job_id'],
            'job_name' => $notification['job_name'],
            'job_number' => $notification['job_number'],
            'vendor_id' => $notification['vendor_id'],
            'vendor_name' => $notification['vendor_name'],
            'vendor_phone' => $notification['vendor_phone']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $formattedNotifications,
        'stats' => [
            'total_notifications' => (int) $stats['total_notifications'],
            'unread_notifications' => (int) $stats['unread_notifications'],
            'pending_notifications' => (int) $stats['pending_notifications'],
            'resolved_notifications' => (int) $stats['resolved_notifications']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getTimeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    // Handle negative time (future timestamps)
    if ($time < 0) {
        return 'just now';
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
?>