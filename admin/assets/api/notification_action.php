<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['notification_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$notificationId = $input['notification_id'];
$action = $input['action']; // 'accept' or 'reject'

$pdo = getDB();

try {
    $pdo->beginTransaction();
    
    // Get notification details
    $notificationQuery = "
        SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, j.store_name as job_title, v.vendor_name
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
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
    
    // Debug: Log notification data
    error_log("Notification Data: " . json_encode($notification));
    
    // Update notification as resolved
    $updateNotificationQuery = "
        UPDATE notifications 
        SET action_required = 0, is_read = 1, updated_at = CURRENT_TIMESTAMP
        WHERE id = :notification_id
    ";
    
    $stmt = $pdo->prepare($updateNotificationQuery);
    $stmt->bindParam(':notification_id', $notificationId);
    $stmt->execute();
    
    // Handle different notification types
    $newStatus = '';
    $userMessage = '';
    $adminMessage = '';
    $userNotificationType = '';
    
    switch ($notification['type']) {
        case 'visit_request':
            if ($action === 'accept') {
                $newStatus = 'request_visit_accepted';
                $userNotificationType = 'request_visit_accepted';
                $userMessage = "Your visit request for Job #{$notification['job_title']} has been accepted.";
                $adminMessage = "Visit request accepted for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
            } else {
                // For reject, set to rejected status
                $newStatus = 'visit_request_rejected';
                $userNotificationType = 'visit_request_rejected';
                $userMessage = "Your visit request for Job #{$notification['job_title']} has been rejected.";
                $adminMessage = "Visit request rejected for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
            }
            
            // Update vendor status
            if ($notification['vendor_id']) {
                $updateVendorQuery = "UPDATE vendors SET status = :status WHERE id = :vendor_id";
                $stmt = $pdo->prepare($updateVendorQuery);
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->execute();
            }
            
            // Insert timeline event for visit request action
            $timelineStmt = $pdo->prepare("
                INSERT INTO job_timeline (
                    job_id, event_type, title, description, event_time, 
                    status, icon, created_by, metadata
                ) VALUES (
                    :job_id, :event_type, :title, :description, NOW(),
                    'completed', :icon, :created_by, :metadata
                )
            ");
            
            $eventType = ($action === 'accept') ? 'visit_accepted' : 'visit_rejected';
            $title = ($action === 'accept') ? 'Visit Accepted' : 'Visit Rejected';
            $icon = ($action === 'accept') ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            $description = ($action === 'accept') 
                ? "Visit request was accepted for vendor: {$notification['vendor_name']}"
                : "Visit request was rejected for vendor: {$notification['vendor_name']}";
            
            $metadata = json_encode([
                'vendor_id' => $notification['vendor_id'],
                'vendor_name' => $notification['vendor_name'],
                'action' => $action,
                'action_by' => $_SESSION['user_id']
            ]);
            
            $timelineStmt->execute([
                ':job_id' => $notification['job_id'],
                ':event_type' => $eventType,
                ':title' => $title,
                ':description' => $description,
                ':icon' => $icon,
                ':created_by' => $_SESSION['user_id'],
                ':metadata' => $metadata
            ]);
            break;
            
        case 'final_visit_request':
            if ($action === 'accept') {
                $newStatus = 'final_visit_request_accepted';
                $userNotificationType = 'final_visit_request_accepted';
                $userMessage = "Your final visit request for Job #{$notification['job_title']} has been accepted.";
                $adminMessage = "Final visit request accepted for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
            } else {
                $newStatus = 'final_visit_request_rejected';
                $userNotificationType = 'final_visit_request_rejected';
                $userMessage = "Your final visit request for Job #{$notification['job_title']} has been rejected.";
                $adminMessage = "Final visit request rejected for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
            }
            
            // Update vendor status
            if ($notification['vendor_id']) {
                $updateVendorQuery = "UPDATE vendors SET status = :status WHERE id = :vendor_id";
                $stmt = $pdo->prepare($updateVendorQuery);
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->execute();
            }
            
            // Update final_request_approvals table status
            if ($notification['vendor_id'] && $notification['job_id']) {
                $finalRequestStatus = ($action === 'accept') ? 'accepted' : 'rejected';
                $updateFinalRequestQuery = "UPDATE final_request_approvals SET status = :status WHERE job_vendor_id = :vendor_id AND requested_user_id = :user_id";
                $stmt = $pdo->prepare($updateFinalRequestQuery);
                $stmt->bindParam(':status', $finalRequestStatus);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->bindParam(':user_id', $notification['user_id']);
                $stmt->execute();
            }
            
            // Insert timeline event for final visit request action
            $timelineStmt = $pdo->prepare("
                INSERT INTO job_timeline (
                    job_id, event_type, title, description, event_time, 
                    status, icon, created_by, metadata
                ) VALUES (
                    :job_id, :event_type, :title, :description, NOW(),
                    'completed', :icon, :created_by, :metadata
                )
            ");
            
            $eventType = ($action === 'accept') ? 'final_visit_accepted' : 'final_visit_rejected';
            $title = ($action === 'accept') ? 'Final Visit Approved' : 'Final Visit Rejected';
            $icon = ($action === 'accept') ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            $description = ($action === 'accept') 
                ? "Final visit request was approved for vendor: {$notification['vendor_name']}"
                : "Final visit request was rejected for vendor: {$notification['vendor_name']}";
            
            $metadata = json_encode([
                'vendor_id' => $notification['vendor_id'],
                'vendor_name' => $notification['vendor_name'],
                'action' => $action,
                'action_by' => $_SESSION['user_id']
            ]);
            
            $timelineStmt->execute([
                ':job_id' => $notification['job_id'],
                ':event_type' => $eventType,
                ':title' => $title,
                ':description' => $description,
                ':icon' => $icon,
                ':created_by' => $_SESSION['user_id'],
                ':metadata' => $metadata
            ]);
            break;
            
        case 'request_vendor_payment':
            if ($action === 'accept') {
                $newStatus = 'vendor_payment_accepted';
                $userNotificationType = 'vendor_payment_accepted';
                $userMessage = "Your payment request for Job #{$notification['job_title']} has been accepted.";
                $adminMessage = "Payment request accepted for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
                
                // Create invoice reminder for admin when payment is accepted
                createInvoiceReminder($pdo, $notification);
                
            } else {
                // For reject, set to rejected status
                $newStatus = 'payment_request_rejected';
                $userNotificationType = 'vendor_payment_rejected';
                $userMessage = "Your payment request for Job #{$notification['job_title']} has been rejected.";
                $adminMessage = "Payment request rejected for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
            }
            
            // Update vendor status
            if ($notification['vendor_id']) {
                $updateVendorQuery = "UPDATE vendors SET status = :status WHERE id = :vendor_id";
                $stmt = $pdo->prepare($updateVendorQuery);
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->execute();
            }
            
            // Insert timeline event for payment request action
            $timelineStmt = $pdo->prepare("
                INSERT INTO job_timeline (
                    job_id, event_type, title, description, event_time, 
                    status, icon, created_by, metadata
                ) VALUES (
                    :job_id, :event_type, :title, :description, NOW(),
                    'completed', :icon, :created_by, :metadata
                )
            ");
            
            $eventType = ($action === 'accept') ? 'payment_accepted' : 'payment_rejected';
            $title = ($action === 'accept') ? 'Payment Approved' : 'Payment Rejected';
            $icon = ($action === 'accept') ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            $description = ($action === 'accept') 
                ? "Payment request was approved for vendor: {$notification['vendor_name']}"
                : "Payment request was rejected for vendor: {$notification['vendor_name']}";
            
            $metadata = json_encode([
                'vendor_id' => $notification['vendor_id'],
                'vendor_name' => $notification['vendor_name'],
                'action' => $action,
                'action_by' => $_SESSION['user_id']
            ]);
            
            $timelineStmt->execute([
                ':job_id' => $notification['job_id'],
                ':event_type' => $eventType,
                ':title' => $title,
                ':description' => $description,
                ':icon' => $icon,
                ':created_by' => $_SESSION['user_id'],
                ':metadata' => $metadata
            ]);
            break;
            
        case 'partial_payment_requested':
            error_log("Processing partial_payment_request action: $action");
            
            // Get partial payment amount
            $partialPaymentQuery = "SELECT requested_amount FROM partial_payments WHERE vendor_id = :vendor_id AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
            $partialPaymentStmt = $pdo->prepare($partialPaymentQuery);
            $partialPaymentStmt->bindParam(':vendor_id', $notification['vendor_id']);
            $partialPaymentStmt->execute();
            $partialPaymentData = $partialPaymentStmt->fetch(PDO::FETCH_ASSOC);
            $requestedAmount = $partialPaymentData ? $partialPaymentData['requested_amount'] : 0;
            
            if ($action === 'accept') {
                $newStatus = 'partial_payment_accepted';
                $userNotificationType = 'partial_payment_accepted';
                $userMessage = "Your partial payment request of $" . number_format($requestedAmount, 2) . " for Job #{$notification['job_title']} has been accepted.";
                $adminMessage = "Partial payment request of $" . number_format($requestedAmount, 2) . " accepted for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
            } else {
                // For reject, set back to final_visit_request_accepted so they can try again or complete job
                $newStatus = 'final_visit_request_accepted';
                $userNotificationType = 'partial_payment_rejected';
                $userMessage = "Your partial payment request of $" . number_format($requestedAmount, 2) . " for Job #{$notification['job_title']} has been rejected.";
                $adminMessage = "Partial payment request of $" . number_format($requestedAmount, 2) . " rejected for Job #{$notification['job_title']} by vendor {$notification['vendor_name']}.";
            }
            error_log("New status to be set: $newStatus");
            
            // Update vendor status
            if ($notification['vendor_id']) {
                $updateVendorQuery = "UPDATE vendors SET status = :status WHERE id = :vendor_id";
                $stmt = $pdo->prepare($updateVendorQuery);
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->execute();
                
                // Check if update was successful
                if ($stmt->rowCount() === 0) {
                    error_log("Failed to update vendor status. Vendor ID: {$notification['vendor_id']}, New Status: $newStatus");
                } else {
                    error_log("Successfully updated vendor status. Vendor ID: {$notification['vendor_id']}, New Status: $newStatus");
                }
            } else {
                error_log("No vendor_id found in notification data");
            }
            
            // Update partial_payments table status
            if ($notification['vendor_id'] && $notification['job_id']) {
                $partialPaymentStatus = ($action === 'accept') ? 'approved' : 'rejected';
                $updatePartialPaymentQuery = "UPDATE partial_payments SET status = :status WHERE vendor_id = :vendor_id AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
                $stmt = $pdo->prepare($updatePartialPaymentQuery);
                $stmt->bindParam(':status', $partialPaymentStatus);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->execute();
            }
            
            // Insert timeline event for partial payment action
            $timelineStmt = $pdo->prepare("
                INSERT INTO job_timeline (
                    job_id, event_type, title, description, event_time, 
                    status, icon, created_by, metadata
                ) VALUES (
                    :job_id, :event_type, :title, :description, NOW(),
                    'completed', :icon, :created_by, :metadata
                )
            ");
            
            $eventType = ($action === 'accept') ? 'partial_payment_accepted' : 'partial_payment_rejected';
            $title = ($action === 'accept') ? 'Partial Payment Approved' : 'Partial Payment Rejected';
            $icon = ($action === 'accept') ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            $description = ($action === 'accept') 
                ? "Partial payment request of $" . number_format($requestedAmount, 2) . " was approved for vendor: {$notification['vendor_name']}"
                : "Partial payment request of $" . number_format($requestedAmount, 2) . " was rejected for vendor: {$notification['vendor_name']}";
            
            $metadata = json_encode([
                'vendor_id' => $notification['vendor_id'],
                'vendor_name' => $notification['vendor_name'],
                'action' => $action,
                'action_by' => $_SESSION['user_id'],
                'requested_amount' => $requestedAmount
            ]);
            
            $timelineStmt->execute([
                ':job_id' => $notification['job_id'],
                ':event_type' => $eventType,
                ':title' => $title,
                ':description' => $description,
                ':icon' => $icon,
                ':created_by' => $_SESSION['user_id'],
                ':metadata' => $metadata
            ]);
            break;
    }
    
    // Create notification for user only
    if ($notification['user_id'] && !empty($userMessage) && !empty($userNotificationType)) {
        $userNotificationQuery = "
            INSERT INTO notifications (user_id, notify_for, type, job_id, vendor_id, message, is_read, action_required, created_at, updated_at)
            VALUES (:user_id, 'user', :type, :job_id, :vendor_id, :message, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ";
        
        $stmt = $pdo->prepare($userNotificationQuery);
        $stmt->bindParam(':user_id', $notification['user_id']);
        $stmt->bindParam(':type', $userNotificationType);
        $stmt->bindParam(':job_id', $notification['job_id']);
        $stmt->bindParam(':vendor_id', $notification['vendor_id']);
        $stmt->bindParam(':message', $userMessage);
        $stmt->execute();
    }
    
    $pdo->commit();
    
    $actionText = $action === 'accept' ? 'accepted' : 'rejected';
    echo json_encode([
        'success' => true,
        'message' => "Request {$actionText} successfully"
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Notification Action API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}

// Function to create invoice reminder when payment is accepted
function createInvoiceReminder($pdo, $notification) {
    try {
        // Get payment request ID from the notification
        $paymentRequestQuery = "
            SELECT rp.id as payment_request_id
            FROM request_payments rp
            WHERE rp.job_id = :job_id AND rp.user_id = :user_id
            ORDER BY rp.created_at DESC
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($paymentRequestQuery);
        $stmt->bindParam(':job_id', $notification['job_id']);
        $stmt->bindParam(':user_id', $notification['user_id']);
        $stmt->execute();
        $paymentRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($paymentRequest) {
            // Create invoice reminder record
            $invoiceReminderQuery = "
                INSERT INTO invoice_reminders (job_id, payment_request_id, vendor_id, reminder_type, notification_sent, created_at, updated_at)
                VALUES (:job_id, :payment_request_id, :vendor_id, 'pending', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                reminder_type = VALUES(reminder_type),
                notification_sent = 0,
                updated_at = CURRENT_TIMESTAMP
            ";
            
            $stmt = $pdo->prepare($invoiceReminderQuery);
            $stmt->bindParam(':job_id', $notification['job_id']);
            $stmt->bindParam(':payment_request_id', $paymentRequest['payment_request_id']);
            $stmt->bindParam(':vendor_id', $notification['vendor_id']);
            $stmt->execute();
            
            // Create notification for admin about invoice reminder
            $adminNotificationQuery = "
                INSERT INTO notifications (user_id, notify_for, type, job_id, vendor_id, message, is_read, action_required, created_at, updated_at)
                VALUES (:admin_id, 'admin', 'invoice_reminder', :job_id, :vendor_id, :message, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ";
            
            $adminMessage = "Invoice reminder: Generate invoice for Job #{$notification['job_title']} - Payment accepted for vendor {$notification['vendor_name']}";
            
            // Get admin user ID (assuming first admin user)
            $adminQuery = "SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1";
            $adminStmt = $pdo->prepare($adminQuery);
            $adminStmt->execute();
            $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                $stmt = $pdo->prepare($adminNotificationQuery);
                $stmt->bindParam(':admin_id', $admin['id']);
                $stmt->bindParam(':job_id', $notification['job_id']);
                $stmt->bindParam(':vendor_id', $notification['vendor_id']);
                $stmt->bindParam(':message', $adminMessage);
                $stmt->execute();
            }
        }
    } catch (Exception $e) {
        error_log("Invoice Reminder Creation Error: " . $e->getMessage());
    }
}
?>
