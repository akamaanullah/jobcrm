<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Get POST data (handle both JSON and FormData)
    $input = [];
    
    // Check if it's FormData (multipart/form-data)
    if (isset($_POST['vendor_id'])) {
        $input = $_POST;
    } else {
        // Try to get JSON data
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        if ($jsonInput) {
            $input = $jsonInput;
        }
    }
    
    // Validate required fields
    $vendorId = $input['vendor_id'] ?? null;
    $jobId = $input['job_id'] ?? null;
    $message = $input['message'] ?? null;
    
    // Debug: Log received data
    error_log('Received data: ' . print_r($input, true));
    error_log('Vendor ID: ' . $vendorId);
    error_log('Job ID: ' . $jobId);
    error_log('Message: ' . $message);
    
    if (!$vendorId || !$jobId) {
        throw new Exception('Vendor ID and Job ID are required. Received: ' . json_encode($input));
    }
    
    // Check if we have either message text or attachment
    $hasMessage = !empty(trim($message));
    $hasAttachment = !empty($_FILES['attachment']['name']);
    
    if (!$hasMessage && !$hasAttachment) {
        throw new Exception('Either message text or attachment is required');
    }
    
    $senderId = $_SESSION['user_id'];
    $senderRole = $_SESSION['user_role'];
    
    // Determine receiver based on sender role
    // If sender is user or manager, receiver is admin
    // If sender is admin, receiver is the job creator (user or manager)
    if ($senderRole === 'user' || $senderRole === 'manager') {
        // Find admin user
        $adminSql = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $adminStmt = $pdo->prepare($adminSql);
        $adminStmt->execute();
        $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            throw new Exception('No admin user found');
        }
        
        $receiverId = $admin['id'];
    } else {
        // If sender is admin, we need to find the user who created the job
        $userSql = "
            SELECT u.id 
            FROM users u 
            JOIN jobs j ON u.id = j.created_by 
            WHERE j.id = :job_id
        ";
        $userStmt = $pdo->prepare($userSql);
        $userStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('Job owner not found');
        }
        
        $receiverId = $user['id'];
    }
    
    error_log('Sender ID: ' . $senderId . ' (Role: ' . $senderRole . ')');
    error_log('Receiver ID: ' . $receiverId);
    
    // Validate message length (only if message is provided)
    if ($hasMessage && strlen(trim($message)) === 0) {
        throw new Exception('Message cannot be empty');
    }
    
    if ($hasMessage && strlen($message) > 1000) {
        throw new Exception('Message is too long (max 1000 characters)');
    }
    
    // Check if vendor exists and belongs to the job
    $vendorCheckSql = "
        SELECT id, vendor_name 
        FROM vendors 
        WHERE id = :vendor_id AND job_id = :job_id
    ";
    
    $vendorStmt = $pdo->prepare($vendorCheckSql);
    $vendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $vendorStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $vendorStmt->execute();
    
    $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);
    if (!$vendor) {
        throw new Exception('Vendor not found for this job');
    }
    
    // Receiver ID is already determined above based on sender role
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Insert message
        $messageSql = "
            INSERT INTO messages (
                vendor_id, 
                job_id, 
                sender_id, 
                receiver_id, 
                message, 
                attachment, 
                is_read, 
                created_at, 
                updated_at
            ) VALUES (
                :vendor_id, 
                :job_id, 
                :sender_id, 
                :receiver_id, 
                :message, 
                :attachment, 
                0, 
                NOW(), 
                NOW()
            )
        ";
        
        $hasAttachment = isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK;
        
        // Handle null message case - set empty string if null
        $messageText = $message ?: '';
        
        $messageStmt = $pdo->prepare($messageSql);
        $messageStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $messageStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $messageStmt->bindParam(':sender_id', $senderId, PDO::PARAM_INT);
        $messageStmt->bindParam(':receiver_id', $receiverId, PDO::PARAM_INT);
        $messageStmt->bindParam(':message', $messageText, PDO::PARAM_STR);
        $messageStmt->bindParam(':attachment', $hasAttachment, PDO::PARAM_BOOL);
        $messageStmt->execute();
        
        $messageId = $pdo->lastInsertId();
        
        $attachments = [];
        
        // Handle file upload if present
        if ($hasAttachment) {
            $uploadDir = '../../../uploads/messages/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $file = $_FILES['attachment'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            $fileType = $file['type'];
            
            // Validate file
            $allowedTypes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain'
            ];
            
            $maxFileSize = 10 * 1024 * 1024; // 10MB
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('File type not allowed');
            }
            
            if ($fileSize > $maxFileSize) {
                throw new Exception('File size too large (max 10MB)');
            }
            
            // Generate unique filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = 'message_' . $messageId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $filePath = $uploadDir . $uniqueFileName;
            
            // Move uploaded file
            if (!move_uploaded_file($fileTmpName, $filePath)) {
                throw new Exception('Failed to upload file');
            }
            
            // Insert attachment record
            $attachmentSql = "
                INSERT INTO message_attachments (
                    message_id, 
                    file_name, 
                    file_path, 
                    file_type, 
                    file_size, 
                    created_at
                ) VALUES (
                    :message_id, 
                    :file_name, 
                    :file_path, 
                    :file_type, 
                    :file_size, 
                    NOW()
                )
            ";
            
            $attachmentStmt = $pdo->prepare($attachmentSql);
            $attachmentStmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
            $attachmentStmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
            $attachmentStmt->bindParam(':file_path', $filePath, PDO::PARAM_STR);
            $attachmentStmt->bindParam(':file_type', $fileType, PDO::PARAM_STR);
            $attachmentStmt->bindParam(':file_size', $fileSize, PDO::PARAM_INT);
            $attachmentStmt->execute();
            
            $attachments[] = [
                'id' => $pdo->lastInsertId(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Get sender information
        $senderSql = "
            SELECT first_name, last_name, role 
            FROM users 
            WHERE id = :sender_id
        ";
        
        $senderStmt = $pdo->prepare($senderSql);
        $senderStmt->bindParam(':sender_id', $senderId, PDO::PARAM_INT);
        $senderStmt->execute();
        $sender = $senderStmt->fetch(PDO::FETCH_ASSOC);
        
        // Get receiver information
        $receiverSql = "
            SELECT first_name, last_name, role 
            FROM users 
            WHERE id = :receiver_id
        ";
        
        $receiverStmt = $pdo->prepare($receiverSql);
        $receiverStmt->bindParam(':receiver_id', $receiverId, PDO::PARAM_INT);
        $receiverStmt->execute();
        $receiver = $receiverStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$receiver) {
            throw new Exception('Receiver not found');
        }
        
        // Prepare response data
        $responseData = [
            'id' => (int)$messageId,
            'vendor_id' => (int)$vendorId,
            'job_id' => (int)$jobId,
            'sender_id' => (int)$senderId,
            'receiver_id' => (int)$receiverId,
            'message' => $messageText,
            'attachment' => $hasAttachment,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'sender_name' => $sender['first_name'] . ' ' . $sender['last_name'],
            'receiver_name' => $receiver['first_name'] . ' ' . $receiver['last_name'],
            'sender_role' => $sender['role'],
            'receiver_role' => $receiver['role'],
            'vendor_name' => $vendor['vendor_name'],
            'is_sent' => true,
            'sender_avatar' => getInitials($sender['first_name'] . ' ' . $sender['last_name']),
            'receiver_avatar' => getInitials($receiver['first_name'] . ' ' . $receiver['last_name']),
            'formatted_time' => 'Just now',
            'formatted_date' => date('M j, Y'),
            'formatted_time_only' => date('g:i A'),
            'attachments' => $attachments
        ];
        
        // Return success response
        echo json_encode([
            'success' => true,
            'data' => $responseData,
            'message' => 'Message sent successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
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
?>
