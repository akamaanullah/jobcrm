<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in and has 'manager' role
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $jobId = $input['job_id'] ?? null;
    $vendorIds = $input['vendor_ids'] ?? [];

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    if (empty($vendorIds) || !is_array($vendorIds)) {
        throw new Exception('Vendor IDs are required');
    }

    $pdo = connectDatabase();

    // Verify that the job is assigned to the current user
    $jobAssignmentCheck = "SELECT id FROM jobs WHERE id = :job_id AND assigned_to = :user_id";
    $assignmentStmt = $pdo->prepare($jobAssignmentCheck);
    $assignmentStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $assignmentStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $assignmentStmt->execute();

    if (!$assignmentStmt->fetch()) {
        throw new Exception('Access denied: Job not assigned to this user.');
    }

    // Start transaction
    $pdo->beginTransaction();

    $addedVendors = [];
    $errors = [];

    foreach ($vendorIds as $vendorId) {
        try {
            // Check if vendor exists
            $vendorCheckQuery = "SELECT * FROM vendors WHERE id = :vendor_id";
            $vendorStmt = $pdo->prepare($vendorCheckQuery);
            $vendorStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
            $vendorStmt->execute();
            $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);

            if (!$vendor) {
                $errors[] = "Vendor not found";
                continue;
            }

            // Check if this exact vendor is the one we're trying to add (same id and already on this job)
            if ($vendor['job_id'] == $jobId) {
                $errors[] = "Already assigned";
                continue;
            }

            // Check if a vendor with the same name and phone already exists on this job
            $duplicateCheckQuery = "SELECT id FROM vendors WHERE vendor_name = :vendor_name AND phone = :phone AND job_id = :job_id";
            $duplicateStmt = $pdo->prepare($duplicateCheckQuery);
            $duplicateStmt->bindParam(':vendor_name', $vendor['vendor_name']);
            $duplicateStmt->bindParam(':phone', $vendor['phone']);
            $duplicateStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
            $duplicateStmt->execute();

            if ($duplicateStmt->fetch()) {
                $errors[] = "Already assigned";
                continue;
            }

            // Create new vendor entry for this job
            $insertQuery = "
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
            ";

            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->execute([
                ':job_id' => $jobId,
                ':vendor_name' => $vendor['vendor_name'],
                ':phone' => $vendor['phone'],
                ':quote_type' => $vendor['quote_type'],
                ':quote_amount' => $vendor['quote_amount'],
                ':vendor_platform' => $vendor['vendor_platform'],
                ':location' => $vendor['location'],
                ':appointment_date_time' => $vendor['appointment_date_time']
            ]);

            $newVendorId = $pdo->lastInsertId();
            $addedVendors[] = [
                'id' => $newVendorId,
                'vendor_name' => $vendor['vendor_name'],
                'original_id' => $vendorId
            ];

            // Create notification for admin
            $notificationQuery = "
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
            ";

            // Get user information for notification message
            $userQuery = "SELECT CONCAT(first_name, ' ', last_name) as full_name, username FROM users WHERE id = :user_id";
            $userStmt = $pdo->prepare($userQuery);
            $userStmt->bindParam(':user_id', $_SESSION['user_id']);
            $userStmt->execute();
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            $userName = $user ? ($user['full_name'] ?: $user['username'] ?: 'User') : 'User';
            $message = "Vendor '{$vendor['vendor_name']}' has been added to job #{$jobId} by {$userName}";

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
            
            $description = "New vendor added by {$userName}, vendor: {$vendor['vendor_name']}";
            $metadata = json_encode([
                'vendor_id' => $newVendorId,
                'vendor_name' => $vendor['vendor_name'],
                'vendor_phone' => $vendor['phone'],
                'quote_type' => $vendor['quote_type'],
                'quote_amount' => $vendor['quote_type'] === 'paid_quote' ? $vendor['quote_amount'] : 0
            ]);
            
            $timelineStmt->execute([
                ':job_id' => $jobId,
                ':description' => $description,
                ':created_by' => $_SESSION['user_id'],
                ':metadata' => $metadata
            ]);

            $notificationStmt = $pdo->prepare($notificationQuery);
            $notificationStmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':job_id' => $jobId,
                ':vendor_id' => $newVendorId,
                ':message' => $message
            ]);

        } catch (Exception $e) {
            $errors[] = "Error adding vendor ID {$vendorId}: " . $e->getMessage();
        }
    }

    if (!empty($addedVendors)) {
        $pdo->commit();

        $successCount = count($addedVendors);
        $message = $successCount === 1 ? 'Vendor added successfully!' : 'Vendors added successfully!';

        echo json_encode([
            'success' => true,
            'message' => $message,
            'added_vendors' => $addedVendors,
            'errors' => $errors
        ]);
    } else {
        $pdo->rollBack();
        // Count how many vendors are already assigned
        $alreadyAssignedCount = count($errors);
        throw new Exception("Selected vendors are already assigned to this job. Please select different vendors.");
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>