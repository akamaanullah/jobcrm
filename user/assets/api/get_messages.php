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
    
    // Get parameters
    $vendorId = $_GET['vendor_id'] ?? null;
    $jobId = $_GET['job_id'] ?? null;
    $lastMessageId = $_GET['last_message_id'] ?? 0;
    $limit = $_GET['limit'] ?? 50;
    $offset = $_GET['offset'] ?? 0;
    
    $userId = $_SESSION['user_id'];
    
    
    // Validate required parameters
    if (!$vendorId || !$jobId) {
        throw new Exception('Vendor ID and Job ID are required');
    }
    
    // Get messages between user and admin for specific vendor and job
    $sql = "
        SELECT 
            m.id,
            m.vendor_id,
            m.job_id,
            m.sender_id,
            m.receiver_id,
            m.message,
            m.attachment,
            m.is_read,
            m.created_at,
            m.updated_at,
            CONCAT(sender.first_name, ' ', sender.last_name) as sender_name,
            CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name,
            sender.role as sender_role,
            receiver.role as receiver_role,
            v.vendor_name
        FROM messages m
        LEFT JOIN users sender ON m.sender_id = sender.id
        LEFT JOIN users receiver ON m.receiver_id = receiver.id
        LEFT JOIN vendors v ON m.vendor_id = v.id
        WHERE m.job_id = :job_id 
        AND m.vendor_id = :vendor_id
        AND (m.sender_id = :user_id OR m.receiver_id = :user_id)";
    
    // Add condition for new messages only if last_message_id is provided
    if ($lastMessageId > 0) {
        $sql .= " AND m.id > :last_message_id";
    }
    
    $sql .= " ORDER BY m.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    
    // Bind last_message_id if provided
    if ($lastMessageId > 0) {
        $stmt->bindParam(':last_message_id', $lastMessageId, PDO::PARAM_INT);
    }
    
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    // Get message attachments for each message
    foreach ($messages as &$message) {
        if ($message['attachment']) {
            $attachmentSql = "
                SELECT 
                    id,
                    file_name,
                    file_path,
                    file_type,
                    file_size,
                    created_at
                FROM message_attachments 
                WHERE message_id = :message_id
            ";
            
            $attachmentStmt = $pdo->prepare($attachmentSql);
            $attachmentStmt->bindParam(':message_id', $message['id'], PDO::PARAM_INT);
            $attachmentStmt->execute();
            
            $message['attachments'] = $attachmentStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $message['attachments'] = [];
        }
        
        // Format message data
        $message['is_sent'] = ($message['sender_id'] == $userId);
        $message['sender_avatar'] = getInitials($message['sender_name']);
        $message['receiver_avatar'] = getInitials($message['receiver_name']);
        $message['formatted_time'] = formatTimeAgo($message['created_at']);
        $message['formatted_date'] = date('M j, Y', strtotime($message['created_at']));
        $message['formatted_time_only'] = date('g:i A', strtotime($message['created_at']));
    }
    
    // Get total count for pagination
    $countSql = "
        SELECT COUNT(*) as total
        FROM messages m
        LEFT JOIN users sender ON m.sender_id = sender.id
        LEFT JOIN users receiver ON m.receiver_id = receiver.id
        LEFT JOIN vendors v ON m.vendor_id = v.id
        WHERE m.job_id = :job_id 
        AND m.vendor_id = :vendor_id
        AND (m.sender_id = :user_id OR m.receiver_id = :user_id)
    ";
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $countStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $countStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $countStmt->execute();
    
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Mark messages as read (only for messages received by current user)
    $markReadSql = "
        UPDATE messages 
        SET is_read = 1 
        WHERE job_id = :job_id 
        AND vendor_id = :vendor_id 
        AND receiver_id = :user_id 
        AND is_read = 0
    ";
    
    $markReadStmt = $pdo->prepare($markReadSql);
    $markReadStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $markReadStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $markReadStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $markReadStmt->execute();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'messages' => $messages,
            'pagination' => [
                'total' => (int)$totalCount,
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'has_more' => ($offset + $limit) < $totalCount
            ],
            'vendor_id' => (int)$vendorId,
            'job_id' => (int)$jobId
        ],
        'message' => 'Messages retrieved successfully',
        'messages' => $messages // Also return as 'messages' for polling
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Helper function to get initials from name
function getInitials($name) {
    if (empty($name)) return 'U';
    
    $words = explode(' ', trim($name));
    $initials = '';
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    
    return substr($initials, 0, 2);
}

// Helper function to format time ago
function formatTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', strtotime($datetime));
    }
}
?>
