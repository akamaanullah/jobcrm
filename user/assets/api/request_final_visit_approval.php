<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Validate required fields
$requiredFields = ['vendor_id', 'job_id', 'estimated_amount', 'visit_date_time', 'payment_mode'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: {$field}"]);
        exit;
    }
}

$vendorId = $input['vendor_id'];
$jobId = $input['job_id'];
$estimatedAmount = floatval($input['estimated_amount']);
$visitDateTime = $input['visit_date_time'];
$paymentMode = $input['payment_mode'];
$additionalNotes = $input['additional_notes'] ?? null;
$userId = $_SESSION['user_id'];

$pdo = getDB();

try {
    $pdo->beginTransaction();

    // Validate vendor exists and belongs to this job
    $vendorQuery = "SELECT id, vendor_name FROM vendors WHERE id = :vendor_id AND job_id = :job_id";
    $stmt = $pdo->prepare($vendorQuery);
    $stmt->bindParam(':vendor_id', $vendorId);
    $stmt->bindParam(':job_id', $jobId);
    $stmt->execute();
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        throw new Exception('Vendor not found for this job');
    }

    // Validate job exists
    $jobQuery = "SELECT id, store_name FROM jobs WHERE id = :job_id";
    $stmt = $pdo->prepare($jobQuery);
    $stmt->bindParam(':job_id', $jobId);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        throw new Exception('Job not found');
    }

    // Insert into final_request_approvals table
    $insertQuery = "
        INSERT INTO final_request_approvals 
        (job_id, job_vendor_id, requested_user_id, estimated_amount, visit_date_time, payment_mode, additional_notes, status, created_at, updated_at)
        VALUES (:job_id, :job_vendor_id, :requested_user_id, :estimated_amount, :visit_date_time, :payment_mode, :additional_notes, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ";
    
    $stmt = $pdo->prepare($insertQuery);
    $stmt->bindParam(':job_id', $jobId);
    $stmt->bindParam(':job_vendor_id', $vendorId);
    $stmt->bindParam(':requested_user_id', $userId);
    $stmt->bindParam(':estimated_amount', $estimatedAmount);
    $stmt->bindParam(':visit_date_time', $visitDateTime);
    $stmt->bindParam(':payment_mode', $paymentMode);
    $stmt->bindParam(':additional_notes', $additionalNotes);
    $stmt->execute();

    $finalRequestId = $pdo->lastInsertId();

    // Update vendor status to final_visit_requested
    $updateVendorQuery = "UPDATE vendors SET status = 'final_visit_requested' WHERE id = :vendor_id";
    $stmt = $pdo->prepare($updateVendorQuery);
    $stmt->bindParam(':vendor_id', $vendorId);
    $stmt->execute();

    // Create notification for admin
    $adminMessage = "Final visit approval requested for Job #{$job['store_name']} by vendor {$vendor['vendor_name']}. Estimated amount: $" . number_format($estimatedAmount, 2);
    
    $notificationQuery = "
        INSERT INTO notifications (user_id, notify_for, type, job_id, vendor_id, message, is_read, action_required, created_at, updated_at)
        VALUES (:user_id, 'admin', 'final_visit_request', :job_id, :vendor_id, :message, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ";
    
    $stmt = $pdo->prepare($notificationQuery);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':job_id', $jobId);
    $stmt->bindParam(':vendor_id', $vendorId);
    $stmt->bindParam(':message', $adminMessage);
    $stmt->execute();

    // Create notification for user (confirmation)
    $userMessage = "Your final visit approval request for Job #{$job['store_name']} has been submitted successfully.";
    
    $userNotificationQuery = "
        INSERT INTO notifications (user_id, notify_for, type, job_id, vendor_id, message, is_read, action_required, created_at, updated_at)
        VALUES (:user_id, 'user', 'final_visit_request', :job_id, :vendor_id, :message, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ";
    
    $stmt = $pdo->prepare($userNotificationQuery);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':job_id', $jobId);
    $stmt->bindParam(':vendor_id', $vendorId);
    $stmt->bindParam(':message', $userMessage);
    $stmt->execute();

    // Insert timeline event for final visit request
    $timelineStmt = $pdo->prepare("
        INSERT INTO job_timeline (
            job_id, event_type, title, description, event_time, 
            status, icon, created_by, metadata
        ) VALUES (
            :job_id, 'final_visit_requested', 'Final Visit Requested', :description, NOW(),
            'completed', 'bi-calendar-check-fill', :created_by, :metadata
        )
    ");
    
    // Get user name for description
    $userQuery = "SELECT CONCAT(first_name, ' ', last_name) as full_name, username FROM users WHERE id = :user_id";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(':user_id', $userId);
    $userStmt->execute();
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $userData ? ($userData['full_name'] ?: $userData['username'] ?: 'User') : 'User';
    
    $description = "Final visit approval requested for vendor: {$vendor['vendor_name']}";
    $metadata = json_encode([
        'vendor_id' => $vendorId,
        'vendor_name' => $vendor['vendor_name'],
        'estimated_amount' => $estimatedAmount,
        'visit_date_time' => $visitDateTime,
        'payment_mode' => $paymentMode,
        'requested_by' => $userName,
        'final_request_id' => $finalRequestId
    ]);
    
    $timelineStmt->execute([
        ':job_id' => $jobId,
        ':description' => $description,
        ':created_by' => $userId,
        ':metadata' => $metadata
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Final visit approval request submitted successfully!',
        'data' => [
            'final_request_id' => $finalRequestId,
            'vendor_name' => $vendor['vendor_name'],
            'job_title' => $job['store_name']
        ]
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Request Final Visit Approval API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>
