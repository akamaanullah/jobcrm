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

// Check if user role is 'user'
if ($_SESSION['user_role'] !== 'user') {
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
        // Build the base query - Only show jobs assigned to current user
        $query = "SELECT 
            j.id,
            j.store_name,
            j.address,
            j.job_type,
            j.job_detail,
            j.job_sla,
            j.status,
            j.created_at,
            j.updated_at,
            (SELECT COUNT(*) FROM job_pictures jp WHERE jp.job_id = j.id) as picture_count,
            (SELECT COUNT(*) FROM vendors v WHERE v.job_id = j.id) as vendor_count
        FROM jobs j";
        
        $whereConditions = [];
        $params = [];
        
        // IMPORTANT: Filter by assigned_to - only show jobs assigned to current user
        $whereConditions[] = "j.assigned_to = :user_id";
        $params[':user_id'] = $_SESSION['user_id'];
        
        // Add search filter
        if (!empty($search)) {
            $whereConditions[] = "(j.store_name LIKE :search OR j.job_type LIKE :search OR j.address LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Add status filter
        if (!empty($status)) {
            $whereConditions[] = "j.status = :status";
            $params[':status'] = $status;
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // Add sorting
        $allowedSorts = ['created_at', 'store_name', 'job_type', 'job_sla', 'status'];
        $allowedOrders = ['ASC', 'DESC'];
        
        if (in_array($sort, $allowedSorts)) {
            $query .= " ORDER BY j." . $sort;
            if (in_array(strtoupper($order), $allowedOrders)) {
                $query .= " " . strtoupper($order);
            } else {
                $query .= " DESC";
            }
        } else {
            $query .= " ORDER BY j.created_at DESC";
        }
        
        // Execute query
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate statistics - Only for jobs assigned to current user
        $statsQuery = "SELECT 
            COUNT(*) as total_jobs,
            SUM(CASE WHEN status = 'added' THEN 1 ELSE 0 END) as pending_jobs,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active_jobs,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_jobs
        FROM jobs
        WHERE assigned_to = :user_id";
        
        $statsStmt = $pdo->prepare($statsQuery);
        $statsStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Format jobs data
        $formattedJobs = [];
        foreach ($jobs as $job) {
            $formattedJobs[] = [
                'id' => $job['id'],
                'store_name' => $job['store_name'],
                'address' => $job['address'],
                'job_type' => $job['job_type'],
                'job_detail' => $job['job_detail'],
                'job_sla' => $job['job_sla'],
                'status' => $job['status'],
                'status_display' => getStatusDisplayText($job['status']),
                'status_class' => getStatusClass($job['status']),
                'picture_count' => (int)$job['picture_count'],
                'vendor_count' => (int)$job['vendor_count'],
                'created_at' => $job['created_at'],
                'updated_at' => $job['updated_at'],
                'created_ago' => getTimeAgo($job['created_at']),
                'sla_formatted' => formatSLA($job['job_sla'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'jobs' => $formattedJobs,
            'stats' => [
                'total_jobs' => (int)$stats['total_jobs'],
                'pending_jobs' => (int)$stats['pending_jobs'],
                'active_jobs' => (int)$stats['active_jobs'],
                'completed_jobs' => (int)$stats['completed_jobs']
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
