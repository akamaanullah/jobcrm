<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$notificationId = $_GET['notification_id'] ?? '';
$type = $_GET['type'] ?? '';

if (empty($notificationId) || empty($type)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$pdo = connectDatabase();

try {
    // Get notification details first
    $notificationQuery = "
        SELECT n.*, j.store_name as job_title, v.vendor_name
        FROM notifications n
        LEFT JOIN jobs j ON n.job_id = j.id
        LEFT JOIN vendors v ON n.vendor_id = v.id
        WHERE n.id = :notification_id AND n.notify_for = 'admin'
    ";
    
    $stmt = $pdo->prepare($notificationQuery);
    $stmt->bindParam(':notification_id', $notificationId);
    $stmt->execute();
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        throw new Exception('Notification not found');
    }
    
    $formData = [];
    
    switch ($type) {
        case 'paymentRequestModal':
            // Get payment request data
            $paymentQuery = "
                SELECT rp.*, v.vendor_name, v.phone as vendor_phone,
                       u.first_name as user_first_name, u.last_name as user_last_name
                FROM request_payments rp
                LEFT JOIN vendors v ON v.job_id = rp.job_id AND v.id = :vendor_id
                LEFT JOIN users u ON rp.user_id = u.id
                WHERE rp.job_id = :job_id AND rp.user_id = :user_id
                ORDER BY rp.created_at DESC
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($paymentQuery);
            $stmt->bindParam(':job_id', $notification['job_id']);
            $stmt->bindParam(':vendor_id', $notification['vendor_id']);
            $stmt->bindParam(':user_id', $notification['user_id']);
            $stmt->execute();
            $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($paymentData) {
                // Smart contact person logic
                $contactPerson = 'N/A';
                if (!empty($paymentData['business_name']) && trim($paymentData['business_name']) !== '') {
                    $contactPerson = $paymentData['business_name'];
                } else if (!empty($paymentData['first_name']) || !empty($paymentData['last_name'])) {
                    $contactPerson = trim(($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? ''));
                } else if (!empty($paymentData['user_first_name']) || !empty($paymentData['user_last_name'])) {
                    $contactPerson = trim(($paymentData['user_first_name'] ?? '') . ' ' . ($paymentData['user_last_name'] ?? ''));
                }
                
                // Smart business name logic
                $businessName = 'N/A';
                if (!empty($paymentData['business_name']) && trim($paymentData['business_name']) !== '') {
                    $businessName = $paymentData['business_name'];
                } else if ($paymentData['payment_platform'] === 'payment_link_invoice') {
                    $businessName = 'Payment Link Request';
                } else if ($paymentData['payment_platform'] === 'zelle') {
                    $businessName = 'Zelle Payment Request';
                }
                
                $formData = [
                    'payment_platform' => $paymentData['payment_platform'],
                    'payment_link_invoice_url' => $paymentData['payment_link_invoice_url'],
                    'zelle_email_phone' => $paymentData['zelle_email_phone'],
                    'business_name' => $businessName,
                    'first_name' => $paymentData['first_name'],
                    'last_name' => $paymentData['last_name'],
                    'contact_person' => $contactPerson,
                    'vendor_name' => $paymentData['vendor_name'],
                    'vendor_phone' => $paymentData['vendor_phone'],
                    'created_at' => $paymentData['created_at']
                ];
            }
            break;
            
        case 'finalVisitRequestModal':
            // Get final visit request data
            $finalVisitQuery = "
                SELECT fra.*, v.vendor_name, v.phone as vendor_phone
                FROM final_request_approvals fra
                LEFT JOIN vendors v ON fra.job_vendor_id = v.id
                WHERE fra.job_vendor_id = :vendor_id AND fra.requested_user_id = :user_id
                ORDER BY fra.created_at DESC
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($finalVisitQuery);
            $stmt->bindParam(':vendor_id', $notification['vendor_id']);
            $stmt->bindParam(':user_id', $notification['user_id']);
            $stmt->execute();
            $finalVisitData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($finalVisitData) {
                $formData = [
                    'estimated_amount' => $finalVisitData['estimated_amount'],
                    'visit_date_time' => $finalVisitData['visit_date_time'],
                    'payment_mode' => $finalVisitData['payment_mode'],
                    'additional_notes' => $finalVisitData['additional_notes'],
                    'status' => $finalVisitData['status'],
                    'vendor_name' => $finalVisitData['vendor_name'],
                    'vendor_phone' => $finalVisitData['vendor_phone'],
                    'created_at' => $finalVisitData['created_at']
                ];
            }
            break;
            
        case 'jobCompletedModal':
            // Get job completion data
            $jobCompletedQuery = "
                SELECT cjf.*, v.vendor_name, v.phone as vendor_phone
                FROM complete_job_forms cjf
                LEFT JOIN vendors v ON cjf.job_id = v.job_id AND v.id = :vendor_id
                WHERE cjf.job_id = :job_id AND cjf.user_id = :user_id
                ORDER BY cjf.created_at DESC
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($jobCompletedQuery);
            $stmt->bindParam(':job_id', $notification['job_id']);
            $stmt->bindParam(':vendor_id', $notification['vendor_id']);
            $stmt->bindParam(':user_id', $notification['user_id']);
            $stmt->execute();
            $jobCompletedData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($jobCompletedData) {
                // Get job completion attachments
                $attachmentsQuery = "
                    SELECT attachment_type, attachment_name, attachment_path
                    FROM job_completion_attachments
                    WHERE job_complete_id = :job_complete_id
                    ORDER BY created_at ASC
                ";
                
                $stmt = $pdo->prepare($attachmentsQuery);
                $stmt->bindParam(':job_complete_id', $jobCompletedData['id']);
                $stmt->execute();
                $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $formData = [
                    'w9_vendor_business_name' => $jobCompletedData['w9_vendor_business_name'],
                    'w9_address' => $jobCompletedData['w9_address'],
                    'w9_ein_ssn' => $jobCompletedData['w9_ein_ssn'],
                    'w9_entity_type' => $jobCompletedData['w9_entity_type'],
                    'vendor_name' => $jobCompletedData['vendor_name'],
                    'vendor_phone' => $jobCompletedData['vendor_phone'],
                    'created_at' => $jobCompletedData['created_at'],
                    'attachments' => $attachments
                ];
            }
            break;
            
        case 'partialPaymentRequestModal':
            // Get partial payment request data
            $partialPaymentQuery = "
                SELECT pp.*, v.vendor_name, v.phone as vendor_phone,
                       u.first_name as user_first_name, u.last_name as user_last_name,
                       fra.estimated_amount, j.store_name as job_title, j.address as job_address
                FROM partial_payments pp
                LEFT JOIN vendors v ON pp.vendor_id = v.id
                LEFT JOIN users u ON pp.user_id = u.id
                LEFT JOIN final_request_approvals fra ON pp.final_request_id = fra.id
                LEFT JOIN jobs j ON pp.job_id = j.id
                WHERE pp.vendor_id = :vendor_id AND pp.user_id = :user_id
                ORDER BY pp.created_at DESC
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($partialPaymentQuery);
            $stmt->bindParam(':vendor_id', $notification['vendor_id']);
            $stmt->bindParam(':user_id', $notification['user_id']);
            $stmt->execute();
            $partialPaymentData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($partialPaymentData) {
                // Calculate total paid amount
                $totalPaidQuery = "
                    SELECT COALESCE(SUM(requested_amount), 0) as total_paid
                    FROM partial_payments
                    WHERE vendor_id = :vendor_id AND status = 'approved'
                ";
                
                $stmt = $pdo->prepare($totalPaidQuery);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->execute();
                $totalPaidData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $estimatedAmount = floatval($partialPaymentData['estimated_amount'] ?? 0);
                $totalPaid = floatval($totalPaidData['total_paid'] ?? 0);
                $requestedAmount = floatval($partialPaymentData['requested_amount'] ?? 0);
                $remainingBalance = $estimatedAmount - $totalPaid;
                
                $formData = [
                    'requested_amount' => $partialPaymentData['requested_amount'],
                    'estimated_amount' => $estimatedAmount,
                    'total_paid' => $totalPaid,
                    'remaining_balance' => $remainingBalance,
                    'status' => $partialPaymentData['status'],
                    'screenshot_path' => $partialPaymentData['screenshot_path'] ?? null,
                    'job_title' => $partialPaymentData['job_title'],
                    'job_address' => $partialPaymentData['job_address'],
                    'job_id' => $notification['job_id'],
                    'vendor_name' => $partialPaymentData['vendor_name'],
                    'vendor_phone' => $partialPaymentData['vendor_phone'],
                    'vendor_id' => $notification['vendor_id'],
                    'user_name' => trim(($partialPaymentData['user_first_name'] ?? '') . ' ' . ($partialPaymentData['user_last_name'] ?? '')),
                    'created_at' => $partialPaymentData['created_at']
                ];
            }
            break;
            
        default:
            throw new Exception('Invalid form type');
    }
    
    if (empty($formData)) {
        echo json_encode([
            'success' => false,
            'message' => 'No form data found for this notification'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formData
    ]);
    
} catch (Exception $e) {
    error_log("Get Form Data API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>
