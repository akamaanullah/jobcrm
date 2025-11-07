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
    
    $pdo = connectDatabase();
    
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $sortBy = $_GET['sort_by'] ?? 'created_at_desc';
    
    // Build grouped vendors query
    $baseQuery = "
        SELECT 
            v.vendor_name,
            v.phone,
            v.vendor_platform as specialty,
            v.quote_type,
            MIN(v.quote_amount) as min_amount,
            MAX(v.quote_amount) as max_amount,
            AVG(v.quote_amount) as avg_amount,
            COUNT(DISTINCT v.job_id) as total_jobs_assigned,
            COUNT(DISTINCT CASE WHEN j.status = 'completed' THEN v.job_id END) as completed_jobs,
            COUNT(DISTINCT CASE WHEN v.status = 'vendor_payment_accepted' THEN v.job_id END) as paid_jobs,
            MIN(v.created_at) as first_assigned,
            MAX(v.created_at) as last_assigned,
            GROUP_CONCAT(DISTINCT j.store_name SEPARATOR ', ') as job_names,
            GROUP_CONCAT(DISTINCT v.status SEPARATOR ', ') as statuses
        FROM vendors v
        LEFT JOIN jobs j ON v.job_id = j.id
        WHERE j.assigned_to = :user_id
    ";
    
    $params = [':user_id' => $_SESSION['user_id']];
    
    // Apply search filter
    if (!empty($search)) {
        $baseQuery .= " AND (
            v.vendor_name LIKE :search OR 
            v.phone LIKE :search OR 
            v.vendor_platform LIKE :search OR
            j.store_name LIKE :search
        )";
        $params[':search'] = "%$search%";
    }
    
    // Group by vendor name and phone
    $baseQuery .= " GROUP BY v.vendor_name, v.phone";
    
    // Apply sorting
    switch ($sortBy) {
        case 'created_at_asc':
            $baseQuery .= " ORDER BY first_assigned ASC";
            break;
        case 'vendor_name_asc':
            $baseQuery .= " ORDER BY v.vendor_name ASC";
            break;
        case 'vendor_name_desc':
            $baseQuery .= " ORDER BY v.vendor_name DESC";
            break;
        default: // created_at_desc
            $baseQuery .= " ORDER BY last_assigned DESC";
            break;
    }
    
    // Execute main query
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    
    // Format grouped vendors data
    $formattedVendors = [];
    foreach ($vendors as $vendor) {
        $successRate = $vendor['total_jobs_assigned'] > 0 ? round(($vendor['completed_jobs'] / $vendor['total_jobs_assigned']) * 100, 1) : 0;
        
        // Calculate total amount earned
        $totalAmount = $vendor['paid_jobs'] * $vendor['avg_amount'];
        
        // Determine vendor status based on latest activity
        $statuses = explode(', ', $vendor['statuses']);
        $latestStatus = end($statuses);
        
        // Format job names (limit to 3 jobs)
        $jobNames = explode(', ', $vendor['job_names']);
        $displayJobs = count($jobNames) > 3 ? 
            implode(', ', array_slice($jobNames, 0, 3)) . '...' : 
            $vendor['job_names'];
        
        $formattedVendors[] = [
            'id' => md5($vendor['vendor_name'] . '|' . $vendor['phone']), // Generate unique ID for grouped vendor
            'vendor_name' => $vendor['vendor_name'],
            'phone' => $vendor['phone'],
            'specialty' => $vendor['specialty'],
            'status' => $latestStatus,
            'quote_type' => $vendor['quote_type'] ?? 'Mixed',
            'min_amount' => $vendor['min_amount'] ?? 0,
            'max_amount' => $vendor['max_amount'] ?? 0,
            'avg_amount' => round($vendor['avg_amount'], 2) ?? 0,
            'total_amount_earned' => round($totalAmount, 2),
            'total_jobs_assigned' => (int)$vendor['total_jobs_assigned'],
            'completed_jobs' => (int)$vendor['completed_jobs'],
            'paid_jobs' => (int)$vendor['paid_jobs'],
            'success_rate' => $successRate,
            'job_names' => $displayJobs,
            'all_job_names' => $vendor['job_names'],
            'first_assigned' => $vendor['first_assigned'],
            'last_assigned' => $vendor['last_assigned'],
            'created_ago' => getTimeAgo($vendor['last_assigned']),
            'avatar' => strtoupper(substr($vendor['vendor_name'], 0, 2)),
            'is_grouped' => true
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formattedVendors,
        'count' => count($formattedVendors),
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}


function getTimeAgo($datetime) {
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
