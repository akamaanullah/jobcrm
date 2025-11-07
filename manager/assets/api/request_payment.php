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

    // Users can request payments for their vendors

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields
    $requiredFields = ['vendor_id', 'payment_platform'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $vendorId = (int) $input['vendor_id'];
    $paymentPlatform = $input['payment_platform'];
    $userId = $_SESSION['user_id'];

    // Get job_id, vendor name, job name, and user name
    $pdo = connectDatabase();

    $vendorQuery = "
        SELECT 
            v.job_id,
            v.vendor_name,
            j.store_name as job_name,
            u.first_name as user_first_name,
            u.last_name as user_last_name
        FROM vendors v
        JOIN jobs j ON v.job_id = j.id
        JOIN users u ON :user_id = u.id
        WHERE v.id = :vendor_id
    ";
    $vendorStmt = $pdo->prepare($vendorQuery);
    $vendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $vendorStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $vendorStmt->execute();
    $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        throw new Exception('Vendor not found');
    }

    $jobId = $vendor['job_id'];

    // Prepare payment data based on platform
    $paymentData = [];

    if ($paymentPlatform === 'payment_link') {
        if (!isset($input['payment_link_url']) || empty($input['payment_link_url'])) {
            throw new Exception('Payment link URL is required');
        }
        $paymentPlatform = 'payment_link_invoice'; // Update to match database ENUM
        $paymentData = [
            'payment_link_url' => $input['payment_link_url']
        ];
    } elseif ($paymentPlatform === 'zelle') {
        if (!isset($input['zelle_email_phone']) || empty($input['zelle_email_phone'])) {
            throw new Exception('Zelle email/phone is required');
        }
        if (!isset($input['zelle_type']) || empty($input['zelle_type'])) {
            throw new Exception('Zelle type is required');
        }

        $paymentData = [
            'zelle_email_phone' => $input['zelle_email_phone'],
            'zelle_type' => $input['zelle_type']
        ];

        // Add business name or personal names based on type
        if ($input['zelle_type'] === 'business') {
            if (!isset($input['business_name']) || empty($input['business_name'])) {
                throw new Exception('Business name is required for business type');
            }
            $paymentData['business_name'] = $input['business_name'];
        } elseif ($input['zelle_type'] === 'personal') {
            if (!isset($input['first_name']) || empty($input['first_name'])) {
                throw new Exception('First name is required for personal type');
            }
            if (!isset($input['last_name']) || empty($input['last_name'])) {
                throw new Exception('Last name is required for personal type');
            }
            $paymentData['first_name'] = $input['first_name'];
            $paymentData['last_name'] = $input['last_name'];
        }
    } else {
        throw new Exception('Invalid payment platform');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Insert into request_payments table
        $requestPaymentQuery = "INSERT INTO request_payments (
            job_id,
            user_id,
            payment_platform,
            payment_link_invoice_url,
            zelle_email_phone,
            business_name,
            first_name,
            last_name,
            created_at
        ) VALUES (
            :job_id,
            :user_id,
            :payment_platform,
            :payment_link_invoice_url,
            :zelle_email_phone,
            :business_name,
            :first_name,
            :last_name,
            NOW()
        )";

        // Prepare variables for binding
        $paymentLinkInvoiceUrl = $paymentData['payment_link_url'] ?? null;
        $zelleEmailPhone = $paymentData['zelle_email_phone'] ?? null;
        $businessName = $paymentData['business_name'] ?? null;
        $firstName = $paymentData['first_name'] ?? null;
        $lastName = $paymentData['last_name'] ?? null;

        $requestPaymentStmt = $pdo->prepare($requestPaymentQuery);
        $requestPaymentStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $requestPaymentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $requestPaymentStmt->bindParam(':payment_platform', $paymentPlatform);
        $requestPaymentStmt->bindParam(':payment_link_invoice_url', $paymentLinkInvoiceUrl);
        $requestPaymentStmt->bindParam(':zelle_email_phone', $zelleEmailPhone);
        $requestPaymentStmt->bindParam(':business_name', $businessName);
        $requestPaymentStmt->bindParam(':first_name', $firstName);
        $requestPaymentStmt->bindParam(':last_name', $lastName);

        $requestPaymentStmt->execute();
        $requestPaymentId = $pdo->lastInsertId();

        // Update vendor status to 'requested_vendor_payment'
        $updateVendorQuery = "UPDATE vendors SET status = 'requested_vendor_payment', updated_at = NOW() WHERE id = :vendor_id";
        $updateVendorStmt = $pdo->prepare($updateVendorQuery);
        $updateVendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $updateVendorStmt->execute();

        // Insert timeline event for payment request
        $timelineStmt = $pdo->prepare("
            INSERT INTO job_timeline (
                job_id, event_type, title, description, event_time, 
                status, icon, created_by, metadata
            ) VALUES (
                :job_id, 'payment_requested', 'Payment Requested', :description, NOW(),
                'completed', 'bi-credit-card-fill', :created_by, :metadata
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
        
        $description = "Payment request submitted for vendor: {$vendorName}";
        
        // Prepare metadata with available payment data
        $metadataArray = [
            'vendor_id' => $vendorId,
            'vendor_name' => $vendorName,
            'requested_by' => $userName,
            'payment_platform' => $paymentPlatform,
            'request_payment_id' => $requestPaymentId
        ];
        
        // Add payment data to metadata
        foreach ($paymentData as $key => $value) {
            $metadataArray[$key] = $value;
        }
        
        $metadata = json_encode($metadataArray);
        
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
            'request_vendor_payment', 
            :message, 
            :job_id, 
            :vendor_id, 
            1,
            NOW()
        )";

        $userName = $vendor['user_first_name'] . ' ' . $vendor['user_last_name'];
        $vendorName = $vendor['vendor_name'];
        $jobName = $vendor['job_name'];
        $message = "Payment requested by {$userName} for {$vendorName} on job '{$jobName}'";

        $adminNotificationStmt = $pdo->prepare($adminNotificationQuery);
        $adminNotificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $adminNotificationStmt->bindParam(':message', $message);
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
            'request_vendor_payment', 
            :message, 
            :job_id, 
            :vendor_id, 
            0,
            NOW()
        )";

        $userMessage = "Your payment request for {$vendorName} on job '{$jobName}' has been submitted successfully";

        $userNotificationStmt = $pdo->prepare($userNotificationQuery);
        $userNotificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $userNotificationStmt->bindParam(':message', $userMessage);
        $userNotificationStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $userNotificationStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $userNotificationStmt->execute();

        // Create payment reminder immediately
        $paymentReminderQuery = "
            INSERT INTO payment_reminders (request_payment_id, reminder_type, notification_sent) 
            VALUES (?, 'pending', 0)
            ON DUPLICATE KEY UPDATE 
            reminder_type = VALUES(reminder_type),
            notification_sent = 0,
            updated_at = CURRENT_TIMESTAMP
        ";
        $paymentReminderStmt = $pdo->prepare($paymentReminderQuery);
        $paymentReminderStmt->execute([$requestPaymentId]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment request submitted successfully',
            'data' => [
                'request_payment_id' => $requestPaymentId,
                'vendor_id' => $vendorId,
                'job_id' => $jobId
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>