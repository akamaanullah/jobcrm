<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$pdo = getDB();

try {
    $notificationId = $_POST['notification_id'] ?? '';
    $action = $_POST['action'] ?? '';

    if (empty($notificationId) || empty($action)) {
        throw new Exception('Missing required parameters');
    }

    // Get notification details
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

    // Handle file upload for screenshot
    $screenshotPath = null;
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../uploads/partial_payment_screenshots/';

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
        $fileName = 'partial_payment_' . $notificationId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['screenshot']['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only images are allowed.');
        }

        // Validate file size (max 5MB)
        if ($_FILES['screenshot']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size must be less than 5MB');
        }

        if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $filePath)) {
            $screenshotPath = 'uploads/partial_payment_screenshots/' . $fileName;
        } else {
            throw new Exception('Failed to upload screenshot');
        }
    }

    // For approve action, screenshot is required
    if ($action === 'accept' && !$screenshotPath) {
        throw new Exception('Screenshot is required for approval');
    }

    $pdo->beginTransaction();

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

    // Update vendor status
    if ($notification['vendor_id']) {
        $updateVendorQuery = "UPDATE vendors SET status = :status WHERE id = :vendor_id";
        $stmt = $pdo->prepare($updateVendorQuery);
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':vendor_id', $notification['vendor_id']);
        $stmt->execute();
    }

    // Update partial_payments table status
    if ($notification['vendor_id'] && $notification['job_id']) {
        $partialPaymentStatus = ($action === 'accept') ? 'approved' : 'rejected';
        $updatePartialPaymentQuery = "UPDATE partial_payments SET status = :status";

        // Add screenshot path if provided
        if ($screenshotPath) {
            $updatePartialPaymentQuery .= ", screenshot_path = :screenshot_path";
        }

        $updatePartialPaymentQuery .= " WHERE vendor_id = :vendor_id AND status = 'pending' ORDER BY created_at DESC LIMIT 1";

        $stmt = $pdo->prepare($updatePartialPaymentQuery);
        $stmt->bindParam(':status', $partialPaymentStatus);
        if ($screenshotPath) {
            $stmt->bindParam(':screenshot_path', $screenshotPath);
        }
        $stmt->bindParam(':vendor_id', $notification['vendor_id']);
        $stmt->execute();
    }

    // Create user notification
    $userNotificationQuery = "
        INSERT INTO notifications (
            user_id, notify_for, type, message, job_id, vendor_id, 
            is_read, action_required, created_at
        ) VALUES (
            :user_id, 'user', :type, :message, :job_id, :vendor_id,
            0, 0, NOW()
        )
    ";

    $stmt = $pdo->prepare($userNotificationQuery);
    $stmt->bindParam(':user_id', $notification['user_id']);
    $stmt->bindParam(':type', $userNotificationType);
    $stmt->bindParam(':message', $userMessage);
    $stmt->bindParam(':job_id', $notification['job_id']);
    $stmt->bindParam(':vendor_id', $notification['vendor_id']);
    $stmt->execute();

    // Update original notification as resolved
    $updateNotificationQuery = "UPDATE notifications SET action_required = 0, is_read = 1 WHERE id = :notification_id";
    $stmt = $pdo->prepare($updateNotificationQuery);
    $stmt->bindParam(':notification_id', $notificationId);
    $stmt->execute();

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
        'requested_amount' => $requestedAmount,
        'screenshot_path' => $screenshotPath
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

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $adminMessage
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Partial Payment Action API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>