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

    $pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $sort = $_GET['sort'] ?? 'created_at'; // Default sort by creation date
    
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
    
    if (!empty($search)) {
        $sql .= " AND (j.store_name LIKE :search OR j.job_type LIKE :search OR j.address LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
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
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Prepare job data for display
        $formattedJobs = array_map(function($job) {
            return [
                'id' => $job['id'],
                'store_name' => $job['store_name'],
                'address' => $job['address'],
                'job_type' => $job['job_type'],
                'job_detail' => $job['job_detail'],
                'additional_notes' => $job['additional_notes'],
                'job_sla' => $job['job_sla'],
                'status' => $job['display_status'], // Use display_status instead of original status
                'assigned_to' => $job['assigned_to'],
                'assigned_to_name' => $job['assigned_to_name'],
                'picture_count' => (int)$job['picture_count'],
                'vendor_count' => (int)$job['vendor_count'],
                'created_at' => $job['created_at'],
                'updated_at' => $job['updated_at']
            ];
        }, $jobs);
        
        // Get job statistics based on vendor statuses
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
                'total_jobs' => $totalJobs,
                'pending_jobs' => $pendingJobs,
                'active_jobs' => $activeJobs,
                'completed_jobs' => $completedJobs
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in get_jobs.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
