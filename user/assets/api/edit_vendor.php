<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
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
    $requiredFields = ['vendor_id', 'vendor_name', 'phone', 'quote_type', 'appointment_date_time'];
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

    // Verify vendor exists and belongs to current user's job
    $vendorCheckQuery = "
        SELECT v.id, v.job_id 
        FROM vendors v 
        JOIN jobs j ON v.job_id = j.id 
        WHERE v.id = :vendor_id AND j.assigned_to = :user_id
    ";
    
    $vendorStmt = $pdo->prepare($vendorCheckQuery);
    $vendorStmt->bindParam(':vendor_id', $input['vendor_id'], PDO::PARAM_INT);
    $vendorStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $vendorStmt->execute();
    $vendorData = $vendorStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vendorData) {
        throw new Exception('Vendor not found or not authorized to edit');
    }

    // Update vendor in database
    $stmt = $pdo->prepare("
        UPDATE vendors SET
            vendor_name = :vendor_name, 
            phone = :phone, 
            quote_type = :quote_type, 
            quote_amount = :quote_amount, 
            vendor_platform = :vendor_platform, 
            location = :location,
            appointment_date_time = :appointment_date_time,
            updated_at = NOW()
        WHERE id = :vendor_id
    ");

    $stmt->execute([
        ':vendor_id' => $input['vendor_id'],
        ':vendor_name' => trim($input['vendor_name']),
        ':phone' => trim($input['phone']),
        ':quote_type' => $input['quote_type'],
        ':quote_amount' => $input['quote_type'] === 'paid_quote' ? $input['quote_amount'] : 0,
        ':vendor_platform' => !empty($input['vendor_platform']) ? $input['vendor_platform'] : null,
        ':location' => !empty($input['location']) ? trim($input['location']) : null,
        ':appointment_date_time' => $input['appointment_date_time']
    ]);

    // Create notification for admin about vendor update
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
            'vendor_updated', 
            :job_id, 
            :vendor_id, 
            :message, 
            0, 
            0, 
            NOW(), 
            NOW()
        )
    ");

    $message = "Vendor '{$input['vendor_name']}' has been updated in job #{$vendorData['job_id']}";
    
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':job_id' => $vendorData['job_id'],
        ':vendor_id' => $input['vendor_id'],
        ':message' => $message
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Vendor updated successfully',
        'vendor_id' => $input['vendor_id']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
