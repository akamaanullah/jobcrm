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

    // Get parameters
    $vendorId = $_GET['vendor_id'] ?? null;
    $jobId = $_GET['job_id'] ?? null;
    $lastMessageId = $_GET['last_message_id'] ?? 0;

    if (!$vendorId || !$jobId) {
        throw new Exception('Vendor ID and Job ID are required');
    }

    // Get messages for this vendor and job (only new messages if last_message_id provided)
    $messagesSql = "
        SELECT 
            m.*,
            CONCAT(sender.first_name, ' ', sender.last_name) as sender_name,
            CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name,
            sender.role as sender_role,
            receiver.role as receiver_role
        FROM messages m
        LEFT JOIN users sender ON m.sender_id = sender.id
        LEFT JOIN users receiver ON m.receiver_id = receiver.id
        LEFT JOIN vendors v ON m.vendor_id = v.id
        WHERE m.vendor_id = :vendor_id 
        AND m.job_id = :job_id";
    
    // Add condition for new messages only if last_message_id is provided
    if ($lastMessageId > 0) {
        $messagesSql .= " AND m.id > :last_message_id";
    }
    
    $messagesSql .= " ORDER BY m.created_at DESC";
    
    $messagesStmt = $pdo->prepare($messagesSql);
    $messagesStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $messagesStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    
    // Bind last_message_id if provided
    if ($lastMessageId > 0) {
        $messagesStmt->bindParam(':last_message_id', $lastMessageId, PDO::PARAM_INT);
    }
    
    $messagesStmt->execute();
    $messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get attachments for each message
    $messagesWithAttachments = [];
    foreach ($messages as $message) {
        $attachmentsSql = "
            SELECT 
                id,
                file_name,
                file_path,
                file_type,
                file_size,
                created_at
            FROM message_attachments 
            WHERE message_id = :message_id
            ORDER BY created_at ASC
        ";
        
        $attachmentsStmt = $pdo->prepare($attachmentsSql);
        $attachmentsStmt->bindParam(':message_id', $message['id'], PDO::PARAM_INT);
        $attachmentsStmt->execute();
        $attachments = $attachmentsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $message['attachments'] = $attachments;
        $messagesWithAttachments[] = $message;
    }

    // Mark messages as read for the current admin ONLY if mark_as_read parameter is true
    $markAsRead = $_GET['mark_as_read'] ?? 'false';
    
    if ($markAsRead === 'true') {
        $markReadSql = "
            UPDATE messages 
            SET is_read = 1 
            WHERE vendor_id = :vendor_id 
            AND job_id = :job_id 
            AND receiver_id = :admin_id
            AND is_read = 0
        ";
        
        $markReadStmt = $pdo->prepare($markReadSql);
        $markReadStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $markReadStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $markReadStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $markReadStmt->execute();
    }

    // Reverse messages to show oldest first
    $messagesWithAttachments = array_reverse($messagesWithAttachments);

    echo json_encode([
        'success' => true,
        'data' => $messagesWithAttachments,
        'messages' => $messagesWithAttachments // Also return as 'messages' for polling
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
