<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('Unauthorized access');
    }

    if ($_SESSION['user_role'] !== 'admin') {
        throw new Exception('Admin access required');
    }

    $pdo = connectDatabase();

    // Get job ID from URL parameter
    $jobId = $_GET['job_id'] ?? null;

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Get job details with assigned user
    $jobSql = "SELECT j.*, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
               FROM jobs j 
               LEFT JOIN users u ON j.assigned_to = u.id
               WHERE j.id = :job_id";

    $jobStmt = $pdo->prepare($jobSql);
    $jobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $jobStmt->execute();
    $job = $jobStmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        throw new Exception('Job not found');
    }

    // Get job pictures
    $picturesSql = "SELECT * FROM job_pictures WHERE job_id = :job_id ORDER BY created_at ASC";
    $picturesStmt = $pdo->prepare($picturesSql);
    $picturesStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $picturesStmt->execute();
    $pictures = $picturesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get assigned vendors
    $vendorsSql = "
        SELECT 
            v.*,
            v.vendor_name as first_name,
            '' as last_name,
            '' as email,
            v.phone as phone_number,
            '' as profile_image,
            COALESCE(fra.estimated_amount, 0) as estimated_amount,
            COALESCE(
                CASE 
                    WHEN v.status = 'vendor_payment_accepted' THEN fra.estimated_amount
                    ELSE (
                        SELECT SUM(pp.requested_amount) 
                        FROM partial_payments pp 
                        WHERE pp.vendor_id = v.id AND pp.status = 'approved'
                    )
                END, 
                0
            ) as total_paid
        FROM vendors v
        LEFT JOIN final_request_approvals fra ON fra.job_vendor_id = v.id AND fra.status = 'accepted'
        WHERE v.job_id = :job_id
        ORDER BY v.created_at ASC
    ";

    $vendorsStmt = $pdo->prepare($vendorsSql);
    $vendorsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $vendorsStmt->execute();
    $vendors = $vendorsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get pending notifications for this job that require action OR have forms
    $notificationsSql = "
        SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, v.vendor_name
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        LEFT JOIN vendors v ON n.vendor_id = v.id
        WHERE n.job_id = :job_id 
        AND n.notify_for = 'admin' 
        AND (
            (n.action_required = 1 AND n.is_read = 0) OR 
            (n.type IN ('job_completed', 'final_visit_request', 'request_vendor_payment'))
        )
        ORDER BY n.created_at DESC
        LIMIT 1
    ";

    $notificationsStmt = $pdo->prepare($notificationsSql);
    $notificationsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $notificationsStmt->execute();
    $pendingNotification = $notificationsStmt->fetch(PDO::FETCH_ASSOC);

    // Check if job has any vendors assigned
    $vendorCheckSql = "SELECT COUNT(*) as vendor_count FROM vendors WHERE job_id = :job_id";
    $vendorCheckStmt = $pdo->prepare($vendorCheckSql);
    $vendorCheckStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $vendorCheckStmt->execute();
    $vendorCount = $vendorCheckStmt->fetch(PDO::FETCH_ASSOC)['vendor_count'];

    // Get job timeline from job_timeline table
    $timelineSql = "
        SELECT 
            id,
            event_type,
            title,
            description,
            event_time,
            status,
            icon,
            created_by,
            metadata,
            created_at
        FROM job_timeline
        WHERE job_id = :job_id
        ORDER BY event_time ASC, id ASC
    ";

    $timelineStmt = $pdo->prepare($timelineSql);
    $timelineStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $timelineStmt->execute();
    $timeline = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

    // Determine current workflow status
    $workflowStatus = 'Job Created';
    if (!empty($vendors)) {
        $latestVendor = end($vendors);
        switch ($latestVendor['status']) {
            case 'visit_requested':
                $workflowStatus = 'Visit Requested';
                break;
            case 'final_visit_requested':
                $workflowStatus = 'Final Visit Requested';
                break;
            case 'in_progress':
                $workflowStatus = 'In Progress';
                break;
            case 'job_completed':
                $workflowStatus = 'Job Completed';
                break;
            case 'vendor_payment_accepted':
                $workflowStatus = 'Payment Accepted';
                break;
            case 'requested_vendor_payment':
                $workflowStatus = 'Payment Requested';
                break;
            default:
                $workflowStatus = 'Vendor Assigned';
        }
    }

    // Format response
    $response = [
        'success' => true,
        'data' => [
            'job' => [
                'id' => $job['id'],
                'store_name' => $job['store_name'],
                'address' => $job['address'],
                'job_type' => $job['job_type'],
                'job_detail' => $job['job_detail'],
                'additional_notes' => $job['additional_notes'],
                'job_sla' => $job['job_sla'],
                'status' => $job['status'],
                'assigned_to' => $job['assigned_to'],
                'assigned_to_name' => $job['assigned_to_name'],
                'created_at' => $job['created_at'],
                'updated_at' => $job['updated_at'],
                'job_owner_name' => 'Admin',
                'job_owner_email' => 'admin@system.com'
            ],
            'pictures' => $pictures,
            'vendors' => $vendors,
            'timeline' => $timeline,
            'workflow_status' => $workflowStatus,
            'pending_notification' => $pendingNotification
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>