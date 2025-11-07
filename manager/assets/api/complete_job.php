<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if user role is correct
if ($_SESSION['user_role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Include database connection
require_once '../../../config/database.php';

try {
    // Get database connection
    $pdo = connectDatabase();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $requiredFields = ['job_id', 'vendor_id'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $jobId = (int)$input['job_id'];
    $vendorId = (int)$input['vendor_id'];
    $userId = $_SESSION['user_id'];
    
    // Validate W9 information - it's required for job completion
    if (!isset($input['w9_info']) || !is_array($input['w9_info'])) {
        throw new Exception('W9 information is required for job completion');
    }
    
    $w9Info = $input['w9_info'];
    $w9RequiredFields = ['vendor_business_name', 'address', 'ein_ssn', 'entity_type'];
    
    foreach ($w9RequiredFields as $field) {
        if (!isset($w9Info[$field]) || empty(trim($w9Info[$field]))) {
            throw new Exception("W9 information incomplete: $field is required");
        }
    }
    
    $w9Data = [
        'vendor_business_name' => trim($w9Info['vendor_business_name']),
        'address' => trim($w9Info['address']),
        'ein_ssn' => trim($w9Info['ein_ssn']),
        'entity_type' => trim($w9Info['entity_type'])
    ];
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if job exists and user has access
    $jobCheckQuery = "SELECT id, store_name FROM jobs WHERE id = :job_id";
    $jobStmt = $pdo->prepare($jobCheckQuery);
    $jobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $jobStmt->execute();
    $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job) {
        throw new Exception('Job not found');
    }
    
    // Check if vendor exists and is assigned to this job
    $vendorCheckQuery = "SELECT id, vendor_name FROM vendors WHERE id = :vendor_id AND job_id = :job_id";
    $vendorStmt = $pdo->prepare($vendorCheckQuery);
    $vendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $vendorStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $vendorStmt->execute();
    $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vendor) {
        throw new Exception('Vendor not found or not assigned to this job');
    }
    
    // Insert into complete_job_forms table
    $completeJobQuery = "INSERT INTO complete_job_forms (
        job_id, 
        user_id, 
        w9_vendor_business_name, 
        w9_address, 
        w9_ein_ssn, 
        w9_entity_type,
        created_at
    ) VALUES (
        :job_id, 
        :user_id, 
        :w9_vendor_business_name, 
        :w9_address, 
        :w9_ein_ssn, 
        :w9_entity_type,
        NOW()
    )";
    
    $completeJobStmt = $pdo->prepare($completeJobQuery);
    $completeJobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $completeJobStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $completeJobStmt->bindParam(':w9_vendor_business_name', $w9Data['vendor_business_name']);
    $completeJobStmt->bindParam(':w9_address', $w9Data['address']);
    $completeJobStmt->bindParam(':w9_ein_ssn', $w9Data['ein_ssn']);
    $completeJobStmt->bindParam(':w9_entity_type', $w9Data['entity_type']);
    
    $completeJobStmt->execute();
    $completeJobId = $pdo->lastInsertId();
    
    // Handle attachments if provided
    if (isset($input['attachments']) && is_array($input['attachments'])) {
        foreach ($input['attachments'] as $attachment) {
            if (isset($attachment['type']) && isset($attachment['name']) && isset($attachment['data'])) {
                $attachmentType = $attachment['type']; // 'picture' or 'invoice'
                
                // Convert frontend type to database type
                if ($attachmentType === 'picture') {
                    $attachmentType = 'pictures';
                } elseif ($attachmentType === 'invoice') {
                    $attachmentType = 'invoices';
                }
                $attachmentName = $attachment['name'];
                $attachmentData = $attachment['data']; // Base64 encoded data
                
                // Validate attachment type
                if (!in_array($attachmentType, ['pictures', 'invoices'])) {
                    continue; // Skip invalid attachment types
                }
                
                // Decode base64 data
                $decodedData = base64_decode($attachmentData);
                if ($decodedData === false) {
                    continue; // Skip invalid base64 data
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($attachmentName, PATHINFO_EXTENSION);
                $uniqueFileName = 'job_completion_' . $completeJobId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
                
                // Create directory if it doesn't exist
                $uploadDir = '../../../uploads/job_completion/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Save file
                $filePath = $uploadDir . $uniqueFileName;
                if (file_put_contents($filePath, $decodedData)) {
                    // Insert attachment record - save only relative path in database
                    $relativePath = 'uploads/job_completion/' . $uniqueFileName;
                    $attachmentQuery = "INSERT INTO job_completion_attachments (
                        job_complete_id, 
                        attachment_type, 
                        attachment_name, 
                        attachment_path,
                        created_at
                    ) VALUES (
                        :job_complete_id, 
                        :attachment_type, 
                        :attachment_name, 
                        :attachment_path,
                        NOW()
                    )";
                    
                    $attachmentStmt = $pdo->prepare($attachmentQuery);
                    $attachmentStmt->bindParam(':job_complete_id', $completeJobId, PDO::PARAM_INT);
                    $attachmentStmt->bindParam(':attachment_type', $attachmentType);
                    $attachmentStmt->bindParam(':attachment_name', $attachmentName);
                    $attachmentStmt->bindParam(':attachment_path', $relativePath);
                    $attachmentStmt->execute();
                }
            }
        }
    }
    
    // Update vendor status to 'job_completed'
    $updateVendorQuery = "UPDATE vendors SET status = 'job_completed', updated_at = NOW() WHERE id = :vendor_id";
    $updateVendorStmt = $pdo->prepare($updateVendorQuery);
    $updateVendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $updateVendorStmt->execute();
    
    // Update job status to 'completed' when job is completed
    $updateJobQuery = "UPDATE jobs SET status = 'completed', updated_at = NOW() WHERE id = :job_id";
    $updateJobStmt = $pdo->prepare($updateJobQuery);
    $updateJobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $updateJobStmt->execute();
    
    // Insert timeline event for job completion
    $timelineStmt = $pdo->prepare("
        INSERT INTO job_timeline (
            job_id, event_type, title, description, event_time, 
            status, icon, created_by, metadata
        ) VALUES (
            :job_id, 'job_completed', 'Job Completed', :description, NOW(),
            'completed', 'bi-check-circle-fill', :created_by, :metadata
        )
    ");
    
    // Get user and vendor names for description
    $userQuery = "SELECT CONCAT(first_name, ' ', last_name) as full_name, username FROM users WHERE id = :user_id";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(':user_id', $userId);
    $userStmt->execute();
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $userData ? ($userData['full_name'] ?: $userData['username'] ?: 'User') : 'User';
    
    $vendorQuery = "SELECT vendor_name FROM vendors WHERE id = :vendor_id";
    $vendorStmt = $pdo->prepare($vendorQuery);
    $vendorStmt->bindParam(':vendor_id', $vendorId);
    $vendorStmt->execute();
    $vendorData = $vendorStmt->fetch(PDO::FETCH_ASSOC);
    $vendorName = $vendorData ? $vendorData['vendor_name'] : 'Unknown Vendor';
    
    $description = "Job has been completed by vendor: {$vendorName}";
    $metadata = json_encode([
        'vendor_id' => $vendorId,
        'vendor_name' => $vendorName,
        'completed_by' => $userName,
        'w9_vendor_business_name' => $w9Data['vendor_business_name'],
        'w9_ein_ssn' => $w9Data['ein_ssn'],
        'w9_address' => $w9Data['address'],
        'w9_entity_type' => $w9Data['entity_type'],
        'has_attachments' => !empty($input['pictures']) || !empty($input['invoices'])
    ]);
    
    $timelineStmt->execute([
        ':job_id' => $jobId,
        ':description' => $description,
        ':created_by' => $userId,
        ':metadata' => $metadata
    ]);
    
    // Create notification for admin
    $adminNotificationQuery = "INSERT INTO notifications (
        user_id, 
        notify_for,
        type, 
        message, 
        job_id, 
        vendor_id, 
        action_required,
        created_at
    ) VALUES (
        :user_id, 
        'admin',
        'job_completed', 
        :message, 
        :job_id, 
        :vendor_id, 
        0,
        NOW()
    )";
    
    $adminNotificationStmt = $pdo->prepare($adminNotificationQuery);
    $adminNotificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $adminNotificationStmt->bindValue(':message', "Job '{$job['store_name']}' has been completed by vendor '{$vendor['vendor_name']}'");
    $adminNotificationStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $adminNotificationStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $adminNotificationStmt->execute();
    
    // Create notification for user (confirmation)
    $userNotificationQuery = "INSERT INTO notifications (
        user_id, 
        notify_for,
        type, 
        message, 
        job_id, 
        vendor_id, 
        action_required,
        created_at
    ) VALUES (
        :user_id, 
        'user',
        'job_completed', 
        :message, 
        :job_id, 
        :vendor_id, 
        0,
        NOW()
    )";
    
    $userNotificationStmt = $pdo->prepare($userNotificationQuery);
    $userNotificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $userNotificationStmt->bindValue(':message', "You have successfully completed job '{$job['store_name']}' for vendor '{$vendor['vendor_name']}'");
    $userNotificationStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $userNotificationStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $userNotificationStmt->execute();
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Job completed successfully',
        'data' => [
            'complete_job_id' => $completeJobId,
            'job_name' => $job['store_name'],
            'vendor_name' => $vendor['vendor_name']
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Complete Job API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.',
        'error' => $e->getMessage()
    ]);
}
?>
