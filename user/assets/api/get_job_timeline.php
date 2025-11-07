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

    // Get job ID from URL parameter
    $jobId = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    $pdo = connectDatabase();

    // Verify job exists
    $jobQuery = "SELECT id FROM jobs WHERE id = :job_id";
    $jobStmt = $pdo->prepare($jobQuery);
    $jobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $jobStmt->execute();
    $job = $jobStmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        throw new Exception('Job not found');
    }

    // Get timeline events from job_timeline table
    $timelineQuery = "
        SELECT 
            id,
            event_type as type,
            title,
            description,
            event_time as timestamp,
            status,
            icon,
            created_by,
            metadata
        FROM job_timeline
        WHERE job_id = :job_id
        ORDER BY event_time ASC, id ASC
    ";

    $timelineStmt = $pdo->prepare($timelineQuery);
    $timelineStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $timelineStmt->execute();
    $timelineEvents = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

    // Parse metadata JSON for each event (if needed by frontend)
    foreach ($timelineEvents as &$event) {
        if (!empty($event['metadata'])) {
            $event['metadata'] = json_decode($event['metadata'], true);
        } else {
            $event['metadata'] = null;
        }
    }
    unset($event); // Break reference

    // Old dynamic code removed - now using job_timeline table
    
    // Keep the old switch statement for backward compatibility (can be removed later)
    // The notifications are now stored in job_timeline table directly
    
    // Dummy switch to maintain code structure (will not execute as we fetch from table)
    if (false) {
        switch ('dummy') {
            case 'visit_request':
                $event = [
                    'type' => 'visit_requested',
                    'title' => 'Visit Requested',
                    'description' => "Visit request sent for vendor",
                    'timestamp' => null,
                    'status' => 'completed',
                    'icon' => 'bi-eye-fill'
                ];
                break;

            case 'request_visit_accepted':
                $event = [
                    'type' => 'visit_accepted',
                    'title' => 'Visit Accepted',
                    'description' => "Visit request was accepted",
                    'timestamp' => null,
                    'status' => 'completed',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;

            case 'final_visit_request':
                $event = [
                    'type' => 'final_visit_requested',
                    'title' => 'Final Visit Requested',
                    'description' => "Final visit approval requested",
                    'timestamp' => $notification['created_at'],
                    'status' => 'completed',
                    'icon' => 'bi-calendar-check-fill'
                ];
                break;

            case 'final_visit_request_accepted':
                $event = [
                    'type' => 'final_visit_accepted',
                    'title' => 'Final Visit Approved',
                    'description' => "Final visit request was approved",
                    'timestamp' => $notification['created_at'],
                    'status' => 'completed',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;

            case 'job_completed':
                $event = [
                    'type' => 'job_completed',
                    'title' => 'Job Completed',
                    'description' => "Job has been completed",
                    'timestamp' => $notification['created_at'],
                    'status' => 'completed',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;

            case 'request_vendor_payment':
                $event = [
                    'type' => 'payment_requested',
                    'title' => 'Payment Requested',
                    'description' => "Payment request submitted",
                    'timestamp' => $notification['created_at'],
                    'status' => 'completed',
                    'icon' => 'bi-credit-card-fill'
                ];
                break;

            case 'vendor_payment_accepted':
                $event = [
                    'type' => 'payment_accepted',
                    'title' => 'Payment Approved',
                    'description' => "Payment request was approved",
                    'timestamp' => $notification['created_at'],
                    'status' => 'completed',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;
        }

        if ($event) {
            $timelineEvents[] = $event;
        }
    }

    // Sort timeline events by timestamp
    usort($timelineEvents, function ($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });

    // Add current status based on latest vendor status
    if (!empty($vendors)) {
        $latestVendor = end($vendors);
        $currentStatus = null;

        switch ($latestVendor['status']) {
            case 'added':
                $currentStatus = [
                    'type' => 'in_progress',
                    'title' => 'In Progress',
                    'description' => 'Vendor is ready to start work',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'active',
                    'icon' => 'bi-clock-fill'
                ];
                break;

            case 'visit_requested':
                $currentStatus = [
                    'type' => 'visit_pending',
                    'title' => 'Visit Pending',
                    'description' => 'Waiting for visit request approval',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'pending',
                    'icon' => 'bi-hourglass-split'
                ];
                break;

            case 'request_visit_accepted':
                $currentStatus = [
                    'type' => 'visit_accepted',
                    'title' => 'Visit Approved',
                    'description' => 'Visit has been approved, work can begin',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'active',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;

            case 'final_visit_requested':
                $currentStatus = [
                    'type' => 'final_visit_pending',
                    'title' => 'Final Visit Pending',
                    'description' => 'Waiting for final visit approval',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'pending',
                    'icon' => 'bi-hourglass-split'
                ];
                break;

            case 'final_visit_request_accepted':
                $currentStatus = [
                    'type' => 'final_visit_accepted',
                    'title' => 'Final Visit Approved',
                    'description' => 'Final visit approved, job can be completed',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'active',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;

            case 'job_completed':
                $currentStatus = [
                    'type' => 'job_completed',
                    'title' => 'Job Completed',
                    'description' => 'Job has been completed successfully',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'completed',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;

            case 'requested_vendor_payment':
                $currentStatus = [
                    'type' => 'payment_pending',
                    'title' => 'Payment Pending',
                    'description' => 'Waiting for payment approval',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'pending',
                    'icon' => 'bi-hourglass-split'
                ];
                break;

            case 'vendor_payment_accepted':
                $currentStatus = [
                    'type' => 'payment_accepted',
                    'title' => 'Payment Approved',
                    'description' => 'Payment has been approved',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'completed',
                    'icon' => 'bi-check-circle-fill'
                ];
                break;
        }

        if ($currentStatus) {
            $timelineEvents[] = $currentStatus;
        }
    }

    // Add time ago formatting
    foreach ($timelineEvents as &$event) {
        if (isset($event['timestamp'])) {
            $event['time_ago'] = getTimeAgo($event['timestamp']);
        }
    }
    unset($event); // Break reference

    echo json_encode([
        'success' => true,
        'data' => $timelineEvents
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Helper function to calculate time ago
function getTimeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    // Handle negative time (future timestamps)
    if ($time < 0) {
        $time = abs($time);
    }

    if ($time < 60) {
        return 'just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time < 31536000) {
        $months = floor($time / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}
?>