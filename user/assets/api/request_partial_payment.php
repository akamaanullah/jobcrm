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

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields
    $requiredFields = ['vendor_id', 'job_id', 'requested_amount'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $vendorId = (int) $input['vendor_id'];
    $jobId = (int) $input['job_id'];
    $requestedAmount = (float) $input['requested_amount'];
    $userId = $_SESSION['user_id'];

    // Validate amount is positive
    if ($requestedAmount <= 0) {
        throw new Exception('Requested amount must be greater than zero');
    }

    $pdo = connectDatabase();
    $pdo->beginTransaction();

    try {
        // Get vendor details
        $vendorQuery = "
            SELECT 
                v.id,
                v.vendor_name,
                v.status,
                j.store_name as job_name
            FROM vendors v
            JOIN jobs j ON v.job_id = j.id
            WHERE v.id = :vendor_id AND v.job_id = :job_id
        ";
        $vendorStmt = $pdo->prepare($vendorQuery);
        $vendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $vendorStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $vendorStmt->execute();
        $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);

        if (!$vendor) {
            throw new Exception('Vendor not found for this job');
        }

        // Check vendor status - must be after final visit approval
        if ($vendor['status'] !== 'final_visit_request_accepted') {
            throw new Exception('Partial payment can only be requested after final visit approval');
        }

        // Get final request approval details
        $finalRequestQuery = "
            SELECT 
                id,
                estimated_amount
            FROM final_request_approvals
            WHERE job_vendor_id = :vendor_id 
            AND requested_user_id = :user_id
            AND status = 'accepted'
            ORDER BY created_at DESC
            LIMIT 1
        ";
        $finalRequestStmt = $pdo->prepare($finalRequestQuery);
        $finalRequestStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $finalRequestStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $finalRequestStmt->execute();
        $finalRequest = $finalRequestStmt->fetch(PDO::FETCH_ASSOC);

        if (!$finalRequest) {
            throw new Exception('No approved final visit request found');
        }

        $estimatedAmount = (float) $finalRequest['estimated_amount'];
        $finalRequestId = $finalRequest['id'];

        // Calculate total approved partial payments
        $approvedPaymentsQuery = "
            SELECT COALESCE(SUM(requested_amount), 0) as total_paid
            FROM partial_payments
            WHERE vendor_id = :vendor_id AND status = 'approved'
        ";
        $approvedStmt = $pdo->prepare($approvedPaymentsQuery);
        $approvedStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $approvedStmt->execute();
        $totalPaid = (float) $approvedStmt->fetch(PDO::FETCH_ASSOC)['total_paid'];

        // Calculate remaining balance
        $remainingBalance = $estimatedAmount - $totalPaid;

        // Validate requested amount doesn't exceed remaining balance
        if ($requestedAmount > $remainingBalance) {
            throw new Exception("Requested amount ($" . number_format($requestedAmount, 2) . ") exceeds remaining balance ($" . number_format($remainingBalance, 2) . ")");
        }

        // Insert partial payment request
        $insertQuery = "
            INSERT INTO partial_payments (
                job_id,
                vendor_id,
                user_id,
                requested_amount,
                final_request_id,
                status,
                created_at,
                updated_at
            ) VALUES (
                :job_id,
                :vendor_id,
                :user_id,
                :requested_amount,
                :final_request_id,
                'pending',
                NOW(),
                NOW()
            )
        ";

        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $insertStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $insertStmt->bindParam(':requested_amount', $requestedAmount);
        $insertStmt->bindParam(':final_request_id', $finalRequestId, PDO::PARAM_INT);
        $insertStmt->execute();

        $partialPaymentId = $pdo->lastInsertId();

        // Update vendor status to indicate partial payment requested
        $updateVendorQuery = "UPDATE vendors SET status = 'partial_payment_requested', updated_at = NOW() WHERE id = :vendor_id";
        $updateVendorStmt = $pdo->prepare($updateVendorQuery);
        $updateVendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $updateVendorStmt->execute();

        // Get user name for notifications and timeline
        $userQuery = "SELECT CONCAT(first_name, ' ', last_name) as full_name, username FROM users WHERE id = :user_id";
        $userStmt = $pdo->prepare($userQuery);
        $userStmt->bindParam(':user_id', $userId);
        $userStmt->execute();
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
        $userName = $userData ? ($userData['full_name'] ?: $userData['username'] ?: 'User') : 'User';

        // Create notification for admin
        $adminNotificationQuery = "
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
                :type,
                :job_id,
                :vendor_id,
                :message,
                0,
                1,
                NOW(),
                NOW()
            )
        ";

        $adminMessage = "{$userName} has requested partial payment of $" . number_format($requestedAmount, 2) . " for vendor {$vendor['vendor_name']} on job '{$vendor['job_name']}'";

        $adminNotificationStmt = $pdo->prepare($adminNotificationQuery);
        $notificationType = 'partial_payment_requested';
        $adminNotificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $adminNotificationStmt->bindParam(':type', $notificationType);
        $adminNotificationStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $adminNotificationStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $adminNotificationStmt->bindParam(':message', $adminMessage);
        $adminNotificationStmt->execute();

        // Debug: Check if notification was created properly
        error_log("Created notification for partial payment request. Vendor ID: $vendorId, Job ID: $jobId");

        // Insert timeline event for partial payment request
        $timelineStmt = $pdo->prepare("
            INSERT INTO job_timeline (
                job_id, event_type, title, description, event_time, 
                status, icon, created_by, metadata
            ) VALUES (
                :job_id, 'partial_payment_requested', 'Partial Payment Requested', :description, NOW(),
                'completed', 'bi-cash-coin', :created_by, :metadata
            )
        ");

        $description = "Partial payment request submitted for vendor: {$vendor['vendor_name']} - Amount: $" . number_format($requestedAmount, 2);
        $metadata = json_encode([
            'vendor_id' => $vendorId,
            'vendor_name' => $vendor['vendor_name'],
            'requested_by' => $userName,
            'requested_amount' => $requestedAmount,
            'estimated_amount' => $estimatedAmount,
            'total_paid' => $totalPaid,
            'remaining_balance' => $remainingBalance - $requestedAmount,
            'partial_payment_id' => $partialPaymentId
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
            'message' => 'Partial payment request submitted successfully',
            'data' => [
                'partial_payment_id' => $partialPaymentId,
                'requested_amount' => $requestedAmount,
                'estimated_amount' => $estimatedAmount,
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance - $requestedAmount
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