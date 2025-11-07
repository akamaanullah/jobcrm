<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Check if user role is 'manager'
if ($_SESSION['user_role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Manager role required.']);
    exit;
}

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $storeName = trim($input['storeName'] ?? '');
    $address = trim($input['address'] ?? '');
    $jobType = trim($input['jobType'] ?? '');
    $jobSLA = $input['jobSLA'] ?? '';
    $jobDetails = trim($input['jobDetails'] ?? '');
    $additionalNotes = trim($input['additionalNotes'] ?? '');
    $jobPictures = $input['jobPictures'] ?? []; // Array of base64 images
    $managerId = $_SESSION['user_id'];
    
    // Basic validation
    if (empty($storeName) || empty($address) || empty($jobType) || empty($jobSLA) || empty($jobDetails)) {
        echo json_encode(['success' => false, 'message' => 'Store Name, Address, Job Type, Job SLA, and Job Details are required.']);
        exit;
    }
    
    try {
        // Insert job into database - No assignment during creation (like admin)
        // Note: jobs table doesn't have created_by column, only assigned_to
        $stmt = $pdo->prepare("INSERT INTO jobs (store_name, address, job_type, job_detail, additional_notes, job_sla, status) VALUES (:storeName, :address, :jobType, :jobDetails, :additionalNotes, :jobSLA, 'added')");
        $stmt->execute([
            ':storeName' => $storeName,
            ':address' => $address,
            ':jobType' => $jobType,
            ':jobDetails' => $jobDetails,
            ':additionalNotes' => $additionalNotes,
            ':jobSLA' => $jobSLA
        ]);
        
        $jobId = $pdo->lastInsertId();
        
        // Insert timeline event for job creation
        $timelineStmt = $pdo->prepare("
            INSERT INTO job_timeline (
                job_id, event_type, title, description, event_time, 
                status, icon, created_by, metadata
            ) VALUES (
                :job_id, 'job_created', 'Job Created', :description, NOW(),
                'completed', 'bi-briefcase-fill', :created_by, :metadata
            )
        ");
        
        $description = "Job '{$storeName}' was created by manager";
        $metadata = json_encode([
            'store_name' => $storeName,
            'address' => $address,
            'job_type' => $jobType,
            'job_sla' => $jobSLA,
            'created_by_role' => 'manager'
        ]);
        
        $timelineStmt->execute([
            ':job_id' => $jobId,
            ':description' => $description,
            ':created_by' => $managerId,
            ':metadata' => $metadata
        ]);
        
        // Handle job pictures if provided
        if (!empty($jobPictures) && is_array($jobPictures)) {
            $uploadDir = '../../../uploads/job_pictures/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            foreach ($jobPictures as $index => $pictureData) {
                if (strpos($pictureData, 'data:image/') === 0) {
                    // Extract image data from base64
                    $imageData = explode(',', $pictureData);
                    $imageInfo = explode(';', $imageData[0]);
                    $imageExtension = explode('/', $imageInfo[0])[1];
                    
                    // Generate unique filename
                    $fileName = 'job_' . $jobId . '_' . ($index + 1) . '_' . time() . '.' . $imageExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    // Save image file
                    if (file_put_contents($filePath, base64_decode($imageData[1]))) {
                        // Insert picture record into database
                        $stmt = $pdo->prepare("INSERT INTO job_pictures (job_id, picture_name, picture_path) VALUES (:jobId, :pictureName, :picturePath)");
                        $stmt->execute([
                            ':jobId' => $jobId,
                            ':pictureName' => $fileName,
                            ':picturePath' => 'uploads/job_pictures/' . $fileName
                        ]);
                    }
                }
            }
        }
        
        // Create notification for admin about new job created by manager
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, notify_for, type, job_id, message, is_read, action_required, created_at) 
                               SELECT id, 'admin', 'new_job_added', :jobId, :message, 0, 0, NOW() 
                               FROM users WHERE role = 'admin'");
        $message = "New job '{$storeName}' has been added by manager. Job ID: JOB-{$jobId}";
        $stmt->execute([
            ':jobId' => $jobId,
            ':message' => $message
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Job created successfully!',
            'jobId' => $jobId
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in add_job.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>


