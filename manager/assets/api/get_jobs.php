<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if user role is 'manager'
if ($_SESSION['user_role'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get query parameters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $sort = $_GET['sort'] ?? 'created_at';
    $order = $_GET['order'] ?? 'DESC';
    
    try {
        // Build the base query - Show all jobs (like admin)
        $sql = "SELECT j.id, j.store_name, j.address, j.job_type, j.job_detail, 
                   j.additional_notes, j.job_sla, j.status, j.created_at, j.updated_at, j.assigned_to,
                   CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                   (SELECT COUNT(*) FROM job_pictures jp WHERE jp.job_id = j.id) as picture_count,
                   (SELECT COUNT(*) FROM vendors v WHERE v.job_id = j.id) as vendor_count,
                   CASE 
                       WHEN j.status = 'added' THEN 'added'
                       WHEN EXISTS (
                           SELECT 1 FROM vendors v2 WHERE v2.job_id = j.id 
                           AND v2.status IN ('job_completed', 'vendor_payment_accepted', 'requested_vendor_payment')
                       ) THEN 'completed'
                       WHEN EXISTS (
                           SELECT 1 FROM vendors v3 WHERE v3.job_id = j.id
                       ) THEN 'in_progress'
                       ELSE 'added'
                   END as display_status
            FROM jobs j 
            LEFT JOIN users u ON j.assigned_to = u.id
            WHERE 1=1";
        $params = [];
        
        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (j.store_name LIKE :search OR j.job_type LIKE :search OR j.address LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Add status filter
        if (!empty($status)) {
            if ($status === 'completed') {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM vendors v2 WHERE v2.job_id = j.id 
                    AND v2.status IN ('job_completed', 'vendor_payment_accepted', 'requested_vendor_payment')
                )";
            } elseif ($status === 'in_progress') {
                $sql .= " AND j.status != 'added' 
                    AND EXISTS (SELECT 1 FROM vendors v3 WHERE v3.job_id = j.id)
                    AND NOT EXISTS (
                        SELECT 1 FROM vendors v4 WHERE v4.job_id = j.id 
                        AND v4.status IN ('job_completed', 'vendor_payment_accepted', 'requested_vendor_payment')
                    )";
            } else {
                $sql .= " AND j.status = :status";
                $params[':status'] = $status;
            }
        }
        
        // Add sorting
        switch ($sort) {
            case 'store_name':
                $sql .= " ORDER BY j.store_name ASC";
                break;
            case 'job_type':
                $sql .= " ORDER BY j.job_type ASC";
                break;
            case 'job_sla':
                $sql .= " ORDER BY j.job_sla ASC";
                break;
            case 'status':
                $sql .= " ORDER BY j.status ASC";
                break;
            case 'created_at':
            default:
                $sql .= " ORDER BY j.created_at DESC";
                break;
        }
        
        // Execute query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Prepare job data for display
        $formattedJobs = array_map(function($job) {
            $displayStatus = $job['display_status'] ?? 'added';
            return [
                'id' => $job['id'],
                'store_name' => $job['store_name'],
                'address' => $job['address'],
                'job_type' => $job['job_type'],
                'job_detail' => $job['job_detail'],
                'additional_notes' => $job['additional_notes'],
                'job_sla' => $job['job_sla'],
                'status' => $displayStatus, // Use display_status instead of original status
                'status_display' => getStatusDisplayText($displayStatus),
                'status_class' => getStatusClass($displayStatus),
                'assigned_to' => $job['assigned_to'],
                'assigned_to_name' => $job['assigned_to_name'],
                'picture_count' => (int)$job['picture_count'],
                'vendor_count' => (int)$job['vendor_count'],
                'created_at' => $job['created_at'],
                'updated_at' => $job['updated_at'],
                'created_ago' => getTimeAgo($job['created_at']),
                'sla_formatted' => formatSLA($job['job_sla'])
            ];
        }, $jobs);
        
        // Get job statistics based on vendor statuses (all jobs, not just assigned)
        $totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
        
        // Added jobs: Jobs with status 'added' (new jobs)
        $pendingJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'added'")->fetchColumn();
        
        // Completed jobs: Jobs where at least one vendor has status 'job_completed', 'vendor_payment_accepted', or 'requested_vendor_payment'
        $completedJobs = $pdo->query("
            SELECT COUNT(DISTINCT j.id) 
            FROM jobs j 
            INNER JOIN vendors v ON j.id = v.job_id 
            WHERE v.status IN ('job_completed', 'vendor_payment_accepted', 'requested_vendor_payment')
        ")->fetchColumn();
        
        // In Progress jobs: Jobs that have vendors but none of them have completed statuses
        $activeJobs = $pdo->query("
            SELECT COUNT(DISTINCT j.id) 
            FROM jobs j 
            INNER JOIN vendors v ON j.id = v.job_id 
            WHERE j.status != 'added' 
            AND j.id NOT IN (
                SELECT DISTINCT j2.id 
                FROM jobs j2 
                INNER JOIN vendors v2 ON j2.id = v2.job_id 
                WHERE v2.status IN ('job_completed', 'vendor_payment_accepted', 'requested_vendor_payment')
            )
        ")->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'jobs' => $formattedJobs,
            'stats' => [
                'total_jobs' => (int)$totalJobs,
                'pending_jobs' => (int)$pendingJobs,
                'active_jobs' => (int)$activeJobs,
                'completed_jobs' => (int)$completedJobs
            ],
            'pagination' => [
                'total' => count($formattedJobs),
                'page' => 1,
                'per_page' => 50
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in get_jobs.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Helper functions
function getStatusClass($status) {
    $statusClasses = [
        'added' => 'job-created',
        'in_progress' => 'job-active',
        'completed' => 'job-completed'
    ];
    return $statusClasses[$status] ?? 'job-created';
}

function getStatusDisplayText($status) {
    $statusTexts = [
        'added' => 'JOB CREATED',
        'in_progress' => 'IN PROGRESS',
        'completed' => 'COMPLETED'
    ];
    return $statusTexts[$status] ?? 'JOB CREATED';
}

function getTimeAgo($datetime) {
    if (empty($datetime)) return 'Unknown';
    
    $time = time() - strtotime($datetime);
    
    // Handle future time (negative difference)
    if ($time < 0) {
        $time = abs($time);
        if ($time < 60) return 'just now';
        if ($time < 3600) {
            $minutes = floor($time/60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        }
        if ($time < 86400) {
            $hours = floor($time/3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }
        if ($time < 2592000) {
            $days = floor($time/86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
        if ($time < 31536000) {
            $months = floor($time/2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        }
        $years = floor($time/31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
    
    // Handle past time (positive difference)
    if ($time < 60) return 'just now';
    if ($time < 3600) {
        $minutes = floor($time/60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    }
    if ($time < 86400) {
        $hours = floor($time/3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    }
    if ($time < 2592000) {
        $days = floor($time/86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
    if ($time < 31536000) {
        $months = floor($time/2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    }
    $years = floor($time/31536000);
    return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
}

function formatSLA($sla) {
    if (empty($sla)) return 'Not set';
    
    $date = new DateTime($sla);
    return [
        'date' => $date->format('M j, Y'),
        'time' => $date->format('g:i A'),
        'datetime' => $date->format('Y-m-d H:i:s')
    ];
}
?>
