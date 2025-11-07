<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    // Start session
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('Unauthorized access');
    }

    $pdo = getDB();
    $userId = $_SESSION['user_id'];

    // Get parameters
    $type = $_GET['type'] ?? '';
    $notificationId = $_GET['notification_id'] ?? '';

    if (empty($type) || empty($notificationId)) {
        throw new Exception('Missing required parameters');
    }

    // Get notification details first
    $notificationQuery = "
        SELECT n.*, j.store_name as job_name, j.id as job_number, v.vendor_name, v.phone as vendor_phone
        FROM notifications n
        LEFT JOIN jobs j ON n.job_id = j.id
        LEFT JOIN vendors v ON n.vendor_id = v.id
        WHERE n.id = :notification_id AND n.user_id = :user_id AND n.notify_for = 'manager'
    ";

    $notificationStmt = $pdo->prepare($notificationQuery);
    $notificationStmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
    $notificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $notificationStmt->execute();
    $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);

    if (!$notification) {
        throw new Exception('Notification not found');
    }

    $data = [];

    switch ($type) {
        case 'payment':
            // Get payment request data
            $paymentQuery = "
                SELECT rp.*, j.store_name as job_name, j.id as job_number, v.vendor_name, v.phone as vendor_phone
                FROM request_payments rp
                LEFT JOIN jobs j ON rp.job_id = j.id
                LEFT JOIN vendors v ON rp.job_id = v.job_id
                WHERE rp.job_id = :job_id AND rp.user_id = :user_id
                ORDER BY rp.created_at DESC
                LIMIT 1
            ";

            $paymentStmt = $pdo->prepare($paymentQuery);
            $paymentStmt->bindParam(':job_id', $notification['job_id'], PDO::PARAM_INT);
            $paymentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $paymentStmt->execute();
            $paymentData = $paymentStmt->fetch(PDO::FETCH_ASSOC);

            if ($paymentData) {
                $data = [
                    'amount' => $paymentData['payment_platform'] === 'payment_link_invoice' ?
                        'Payment Link/Invoice' : 'Zelle Payment',
                    'payment_platform' => ucfirst(str_replace('_', ' ', $paymentData['payment_platform'])),
                    'job_name' => $paymentData['job_name'],
                    'job_number' => $paymentData['job_number'],
                    'vendor_name' => $paymentData['vendor_name'],
                    'vendor_phone' => $paymentData['vendor_phone'],
                    'created_at' => $paymentData['created_at']
                ];
            }
            break;

        case 'finalVisit':
            // Get final visit request data
            $finalVisitQuery = "
                SELECT fra.*, j.store_name as job_name, j.id as job_number, v.vendor_name, v.phone as vendor_phone
                FROM final_request_approvals fra
                LEFT JOIN jobs j ON fra.job_id = j.id
                LEFT JOIN vendors v ON fra.job_vendor_id = v.id
                WHERE fra.job_id = :job_id AND fra.requested_user_id = :user_id
                ORDER BY fra.created_at DESC
                LIMIT 1
            ";

            $finalVisitStmt = $pdo->prepare($finalVisitQuery);
            $finalVisitStmt->bindParam(':job_id', $notification['job_id'], PDO::PARAM_INT);
            $finalVisitStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $finalVisitStmt->execute();
            $finalVisitData = $finalVisitStmt->fetch(PDO::FETCH_ASSOC);

            if ($finalVisitData) {
                $data = [
                    'visit_date_time' => $finalVisitData['visit_date_time'],
                    'estimated_amount' => $finalVisitData['estimated_amount'],
                    'payment_mode' => $finalVisitData['payment_mode'],
                    'additional_notes' => $finalVisitData['additional_notes'],
                    'status' => $finalVisitData['status'],
                    'job_name' => $finalVisitData['job_name'],
                    'job_number' => $finalVisitData['job_number'],
                    'vendor_name' => $finalVisitData['vendor_name'],
                    'vendor_phone' => $finalVisitData['vendor_phone'],
                    'created_at' => $finalVisitData['created_at']
                ];
            }
            break;

        case 'jobCompleted':
            // Get job completion data
            $jobCompletedQuery = "
                SELECT cjf.*, j.store_name as job_name, j.id as job_number, v.vendor_name, v.phone as vendor_phone
                FROM complete_job_forms cjf
                LEFT JOIN jobs j ON cjf.job_id = j.id
                LEFT JOIN vendors v ON cjf.job_id = v.job_id
                WHERE cjf.job_id = :job_id AND cjf.user_id = :user_id
                ORDER BY cjf.created_at DESC
                LIMIT 1
            ";

            $jobCompletedStmt = $pdo->prepare($jobCompletedQuery);
            $jobCompletedStmt->bindParam(':job_id', $notification['job_id'], PDO::PARAM_INT);
            $jobCompletedStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $jobCompletedStmt->execute();
            $jobCompletedData = $jobCompletedStmt->fetch(PDO::FETCH_ASSOC);

            if ($jobCompletedData) {
                $data = [
                    'w9_vendor_business_name' => $jobCompletedData['w9_vendor_business_name'],
                    'w9_address' => $jobCompletedData['w9_address'],
                    'w9_ein_ssn' => $jobCompletedData['w9_ein_ssn'],
                    'w9_entity_type' => $jobCompletedData['w9_entity_type'],
                    'job_name' => $jobCompletedData['job_name'],
                    'job_number' => $jobCompletedData['job_number'],
                    'vendor_name' => $jobCompletedData['vendor_name'],
                    'vendor_phone' => $jobCompletedData['vendor_phone'],
                    'created_at' => $jobCompletedData['created_at']
                ];

                // Get attachments
                $attachmentsQuery = "
                    SELECT * FROM job_completion_attachments 
                    WHERE job_id = :job_id
                    ORDER BY created_at ASC
                ";

                $attachmentsStmt = $pdo->prepare($attachmentsQuery);
                $attachmentsStmt->bindParam(':job_id', $notification['job_id'], PDO::PARAM_INT);
                $attachmentsStmt->execute();
                $data['attachments'] = $attachmentsStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        case 'partialPayment':
            // Get partial payment data
            $partialPaymentQuery = "
                SELECT pp.*, j.store_name as job_title, j.address as job_address, v.vendor_name, v.phone as vendor_phone,
                       fra.estimated_amount
                FROM partial_payments pp
                LEFT JOIN jobs j ON pp.job_id = j.id
                LEFT JOIN vendors v ON pp.vendor_id = v.id
                LEFT JOIN final_request_approvals fra ON pp.final_request_id = fra.id
                WHERE pp.vendor_id = :vendor_id AND pp.user_id = :user_id
                ORDER BY pp.created_at DESC
                LIMIT 1
            ";

            $partialPaymentStmt = $pdo->prepare($partialPaymentQuery);
            $partialPaymentStmt->bindParam(':vendor_id', $notification['vendor_id'], PDO::PARAM_INT);
            $partialPaymentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $partialPaymentStmt->execute();
            $partialPaymentData = $partialPaymentStmt->fetch(PDO::FETCH_ASSOC);

            if ($partialPaymentData) {
                $data = [
                    'requested_amount' => $partialPaymentData['requested_amount'],
                    'estimated_amount' => $partialPaymentData['estimated_amount'],
                    'status' => $partialPaymentData['status'],
                    'screenshot_path' => $partialPaymentData['screenshot_path'],
                    'job_title' => $partialPaymentData['job_title'],
                    'job_address' => $partialPaymentData['job_address'],
                    'vendor_name' => $partialPaymentData['vendor_name'],
                    'vendor_phone' => $partialPaymentData['vendor_phone'],
                    'created_at' => $partialPaymentData['created_at']
                ];
            }
            break;

        default:
            throw new Exception('Invalid type parameter');
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>