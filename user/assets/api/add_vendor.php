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
    $requiredFields = ['vendor_name', 'phone', 'quote_type', 'appointment_date_time'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }

    // Validate quote amount for paid quotes
    if ($input['quote_type'] === 'paid_quote') {
        if (empty($input['quote_amount']) || $input['quote_amount'] <= 0) {
            throw new Exception('Quote amount is required for paid quotes');
        }
    }

    // Date restrictions removed for historical data entry
    // Allow any date/time including past dates

    $pdo = getDB();

    // Insert vendor into database
    $stmt = $pdo->prepare("
        INSERT INTO vendors (
            job_id,
            vendor_name, 
            phone, 
            quote_type, 
            quote_amount, 
            vendor_platform, 
            location,
            appointment_date_time, 
            status, 
            created_at, 
            updated_at
        ) VALUES (
            :job_id,
            :vendor_name, 
            :phone, 
            :quote_type, 
            :quote_amount, 
            :vendor_platform, 
            :location,
            :appointment_date_time, 
            'added', 
            NOW(), 
            NOW()
        )
    ");

    $stmt->execute([
        ':job_id' => $input['job_id'],
        ':vendor_name' => trim($input['vendor_name']),
        ':phone' => trim($input['phone']),
        ':quote_type' => $input['quote_type'],
        ':quote_amount' => $input['quote_type'] === 'paid_quote' ? $input['quote_amount'] : 0,
        ':vendor_platform' => !empty($input['vendor_platform']) ? $input['vendor_platform'] : null,
        ':location' => !empty($input['location']) ? trim($input['location']) : null,
        ':appointment_date_time' => $input['appointment_date_time']
    ]);

    $vendorId = $pdo->lastInsertId();

    // Get user name for timeline description
    $userQuery = "SELECT CONCAT(first_name, ' ', last_name) as full_name, username FROM users WHERE id = :user_id";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(':user_id', $_SESSION['user_id']);
    $userStmt->execute();
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $userData ? ($userData['full_name'] ?: $userData['username'] ?: 'User') : 'User';

    // Insert timeline event for vendor addition
    $timelineStmt = $pdo->prepare("
        INSERT INTO job_timeline (
            job_id, event_type, title, description, event_time, 
            status, icon, created_by, metadata
        ) VALUES (
            :job_id, 'vendor_added', 'Vendor Added', :description, NOW(),
            'completed', 'bi-person-plus-fill', :created_by, :metadata
        )
    ");

    $description = "New vendor added by {$userName}, vendor: {$input['vendor_name']}";
    $metadata = json_encode([
        'vendor_id' => $vendorId,
        'vendor_name' => $input['vendor_name'],
        'vendor_phone' => $input['phone'],
        'quote_type' => $input['quote_type'],
        'quote_amount' => $input['quote_type'] === 'paid_quote' ? $input['quote_amount'] : 0
    ]);

    $timelineStmt->execute([
        ':job_id' => $input['job_id'],
        ':description' => $description,
        ':created_by' => $_SESSION['user_id'],
        ':metadata' => $metadata
    ]);

    // Create notification for admin about new vendor
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
            'vendor_added', 
            :job_id, 
            :vendor_id, 
            :message, 
            0, 
            0, 
            NOW(), 
            NOW()
        )
    ");

    // Get user information for notification message
    $userQuery = "SELECT CONCAT(first_name, ' ', last_name) as full_name, username FROM users WHERE id = :user_id";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(':user_id', $_SESSION['user_id']);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    $userName = $user ? ($user['full_name'] ?: $user['username'] ?: 'User') : 'User';
    $message = "New vendor '{$input['vendor_name']}' has been added to job #{$input['job_id']} by {$userName}";
    
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':job_id' => $input['job_id'],
        ':vendor_id' => $vendorId,
        ':message' => $message
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Vendor added successfully',
        'vendor_id' => $vendorId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
