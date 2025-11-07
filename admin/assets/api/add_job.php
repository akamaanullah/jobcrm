<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

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
    
    // Basic validation
    if (empty($storeName) || empty($address) || empty($jobType) || empty($jobSLA) || empty($jobDetails)) {
        echo json_encode(['success' => false, 'message' => 'Store Name, Address, Job Type, Job SLA, and Job Details are required.']);
        exit;
    }
    
    // Date restrictions removed for historical data entry
    // Allow any date/time including past dates
    
    try {
        // Insert job into database
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
                'completed', 'bi-briefcase-fill', NULL, :metadata
            )
        ");
        
        $description = "Job '{$storeName}' was created";
        $metadata = json_encode([
            'store_name' => $storeName,
            'address' => $address,
            'job_type' => $jobType,
            'job_sla' => $jobSLA
        ]);
        
        $timelineStmt->execute([
            ':job_id' => $jobId,
            ':description' => $description,
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
        
        // Create notification for all users about new job
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, notify_for, type, job_id, message, is_read, action_required, created_at) 
                               SELECT id, 'user', 'new_job_added', :jobId, :message, 0, 0, NOW() 
                               FROM users WHERE role = 'user'");
        $message = "New job '{$storeName}' has been added by admin. Job ID: JOB-{$jobId}";
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
