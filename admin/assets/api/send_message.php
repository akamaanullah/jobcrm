<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

    // Check if request is FormData or JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isFormData = strpos($contentType, 'multipart/form-data') !== false;

    if ($isFormData) {
        // Handle FormData (with file uploads)
        $vendorId = $_POST['vendor_id'] ?? null;
        $jobId = $_POST['job_id'] ?? null;
        $message = $_POST['message'] ?? '';
        $attachments = $_FILES['attachments'] ?? null;
    } else {
        // Handle JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        $vendorId = $input['vendor_id'] ?? null;
        $jobId = $input['job_id'] ?? null;
        $message = $input['message'] ?? '';
        $attachments = null;
    }

    if (!$vendorId || !$jobId) {
        throw new Exception('Vendor ID and Job ID are required');
    }

    // Validate that at least message or attachment is provided
    if (empty($message) && (!$attachments || empty($attachments['name'][0]))) {
        throw new Exception('Either message text or attachment is required');
    }

    // Determine sender and receiver
    $senderId = $_SESSION['user_id']; // Admin
    $senderRole = 'admin';
    
    // For admin messages, we need to find the job owner (user)
    // We can find this through notifications table
    $jobOwnerSql = "
        SELECT DISTINCT u.id, u.first_name, u.last_name
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        WHERE n.job_id = :job_id 
        AND u.role = 'user'
        LIMIT 1
    ";
    
    $jobOwnerStmt = $pdo->prepare($jobOwnerSql);
    $jobOwnerStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $jobOwnerStmt->execute();
    $jobOwner = $jobOwnerStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$jobOwner) {
        // Fallback: find any user if no job owner found
        $userSql = "SELECT id, first_name, last_name FROM users WHERE role = 'user' LIMIT 1";
        $userStmt = $pdo->prepare($userSql);
        $userStmt->execute();
        $jobOwner = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$jobOwner) {
            throw new Exception('No user found to receive the message');
        }
    }
    
    $receiverId = $jobOwner['id']; // Job owner (user)
    $receiverRole = 'user';

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
                created_at
            ) VALUES (
                :vendor_id, 
                :job_id, 
                :sender_id, 
                :receiver_id, 
                :message, 
                :has_attachment, 
                0, 
                NOW()
            )
        ";
        
        $hasAttachment = ($attachments && !empty($attachments['name'][0])) ? 1 : 0;
        
        $messageStmt = $pdo->prepare($messageSql);
        $messageStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $messageStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $messageStmt->bindParam(':sender_id', $senderId, PDO::PARAM_INT);
        $messageStmt->bindParam(':receiver_id', $receiverId, PDO::PARAM_INT);
        $messageStmt->bindParam(':message', $message);
        $messageStmt->bindParam(':has_attachment', $hasAttachment, PDO::PARAM_INT);
        $messageStmt->execute();
        
        $messageId = $pdo->lastInsertId();

        // Handle file attachments
        $attachedFiles = [];
        if ($attachments && !empty($attachments['name'][0])) {
            $uploadDir = '../../../uploads/messages/' . $messageId . '/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileCount = count($attachments['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($attachments['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = $attachments['name'][$i];
                    $fileTmpName = $attachments['tmp_name'][$i];
                    $fileSize = $attachments['size'][$i];
                    $fileType = $attachments['type'][$i];
                    
                    // Generate unique filename
                    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $uniqueFileName = time() . '_' . $i . '.' . $fileExtension;
                    $filePath = $uploadDir . $uniqueFileName;
                    
                    // Move uploaded file
                    if (move_uploaded_file($fileTmpName, $filePath)) {
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
                        $attachmentStmt->bindParam(':file_name', $fileName);
                        $attachmentStmt->bindParam(':file_path', $filePath);
                        $attachmentStmt->bindParam(':file_type', $fileType);
                        $attachmentStmt->bindParam(':file_size', $fileSize, PDO::PARAM_INT);
                        $attachmentStmt->execute();
                        
                        $attachedFiles[] = [
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'file_type' => $fileType,
                            'file_size' => $fileSize
                        ];
                    }
                }
            }
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'message_id' => $messageId,
                'attachments' => $attachedFiles
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
