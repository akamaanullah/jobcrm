<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

// Start session to get user info
session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
        throw new Exception('Unauthorized access');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (!isset($input['vendor_id']) || !isset($input['job_id'])) {
        throw new Exception('Vendor ID and Job ID are required');
    }

    $vendor_id = (int) $input['vendor_id'];
    $job_id = (int) $input['job_id'];
    $user_id = $_SESSION['user_id'];

    if (!$vendor_id || !$job_id) {
        throw new Exception('Invalid vendor ID or job ID');
    }

    $pdo = getDB();

    // Check if vendor exists and belongs to the job
    $stmt = $pdo->prepare("SELECT id, status FROM vendors WHERE id = :vendor_id AND job_id = :job_id");
    $stmt->execute([':vendor_id' => $vendor_id, ':job_id' => $job_id]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        throw new Exception('Vendor not found for this job');
    }

    // Check if vendor status allows visit request (added or rejected statuses)
    if (!in_array($vendor['status'], ['added', 'visit_request_rejected'])) {
        throw new Exception('Visit request can only be sent for vendors with "Added" or "Rejected" status');
    }

    // Update vendor status to 'visit_requested'
    $stmt = $pdo->prepare("UPDATE vendors SET status = 'visit_requested', updated_at = NOW() WHERE id = :vendor_id");
    $stmt->execute([':vendor_id' => $vendor_id]);

    // Update job status from 'added' to 'in_progress' when first visit request is made
    $stmt = $pdo->prepare("UPDATE jobs SET status = 'in_progress', updated_at = NOW() WHERE id = :job_id AND status = 'added'");
    $stmt->execute([':job_id' => $job_id]);

    // Create notification for admin about visit request
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            user_id, 
            notify_for, 
            type, 
            job_id, 
            vendor_id, 
            message, 
            is_read, 
            action_required, 
            created_at, 
            updated_at
        ) VALUES (
            :user_id, 
            'admin', 
            'visit_request', 
            :job_id, 
            :vendor_id, 
            :message, 
            0, 
            1, 
            NOW(), 
            NOW()
        )
    ");

    // Get user information for notification message
    $userQuery = "SELECT CONCAT(first_name, ' ', last_name) as full_name, username FROM users WHERE id = :user_id";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(':user_id', $user_id);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    $userName = $user ? ($user['full_name'] ?: $user['username'] ?: 'User') : 'User';
    $message = "{$userName} has requested a visit from vendor for job #{$job_id}";
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':job_id' => $job_id,
        ':vendor_id' => $vendor_id,
        ':message' => $message
    ]);

    // Insert timeline event for visit request
    $timelineStmt = $pdo->prepare("
        INSERT INTO job_timeline (
            job_id, event_type, title, description, event_time, 
            status, icon, created_by, metadata
        ) VALUES (
            :job_id, 'visit_requested', 'Visit Requested', :description, NOW(),
            'completed', 'bi-eye-fill', :created_by, :metadata
        )
    ");
    
    // Get vendor name for description
    $vendorQuery = "SELECT vendor_name FROM vendors WHERE id = :vendor_id";
    $vendorStmt = $pdo->prepare($vendorQuery);
    $vendorStmt->bindParam(':vendor_id', $vendor_id);
    $vendorStmt->execute();
    $vendorData = $vendorStmt->fetch(PDO::FETCH_ASSOC);
    $vendorName = $vendorData ? $vendorData['vendor_name'] : 'Unknown Vendor';
    
    $description = "Visit request sent for vendor: {$vendorName}";
    $metadata = json_encode([
        'vendor_id' => $vendor_id,
        'vendor_name' => $vendorName,
        'requested_by' => $userName
    ]);
    
    $timelineStmt->execute([
        ':job_id' => $job_id,
        ':description' => $description,
        ':created_by' => $user_id,
        ':metadata' => $metadata
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Visit request sent successfully',
        'vendor_id' => $vendor_id,
        'new_status' => 'visit_requested'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>