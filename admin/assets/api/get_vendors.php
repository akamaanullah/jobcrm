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

    // Get parameters
    $search = $_GET['search'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    // Build WHERE conditions
    $whereConditions = [];
    $params = [];

    if (!empty($search)) {
        $whereConditions[] = "(v.vendor_name LIKE :search OR v.phone LIKE :search OR v.vendor_platform LIKE :search OR v.location LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    // Status and specialty filters removed since columns don't exist in database

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get grouped vendors count for pagination
    $countSql = "
        SELECT COUNT(DISTINCT CONCAT(v.vendor_name, '|', v.phone)) as total 
        FROM vendors v 
        LEFT JOIN jobs j ON v.job_id = j.id
        {$whereClause}
    ";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get grouped vendors with consolidated statistics
    $vendorsSql = "
        SELECT 
            v.vendor_name,
            v.phone,
            v.vendor_platform,
            v.location,
            v.quote_type,
            MIN(v.quote_amount) as min_quote_amount,
            MAX(v.quote_amount) as max_quote_amount,
            AVG(v.quote_amount) as avg_quote_amount,
            COUNT(DISTINCT v.job_id) as total_jobs_assigned,
            COUNT(DISTINCT CASE WHEN j.status = 'completed' THEN v.job_id END) as completed_jobs,
            COUNT(DISTINCT CASE WHEN v.status = 'vendor_payment_accepted' THEN v.job_id END) as paid_jobs,
            MIN(v.created_at) as first_assigned,
            MAX(v.created_at) as last_assigned,
            GROUP_CONCAT(DISTINCT j.store_name SEPARATOR ', ') as job_names,
            GROUP_CONCAT(DISTINCT v.status SEPARATOR ', ') as statuses
        FROM vendors v
        LEFT JOIN jobs j ON v.job_id = j.id
        {$whereClause}
        GROUP BY v.vendor_name, v.phone
        ORDER BY last_assigned DESC
        LIMIT :limit OFFSET :offset
    ";

    $vendorsStmt = $pdo->prepare($vendorsSql);
    foreach ($params as $key => $value) {
        $vendorsStmt->bindValue($key, $value);
    }
    $vendorsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $vendorsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $vendorsStmt->execute();
    $vendors = $vendorsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get grouped statistics
    $statsSql = "
        SELECT 
            COUNT(DISTINCT CONCAT(vendor_name, '|', phone)) as total_vendors,
            COUNT(DISTINCT CASE WHEN status = 'added' THEN CONCAT(vendor_name, '|', phone) END) as active_vendors,
            COUNT(DISTINCT CASE WHEN status = 'visit_requested' OR status = 'final_visit_requested' THEN CONCAT(vendor_name, '|', phone) END) as pending_vendors,
            COUNT(DISTINCT CASE WHEN status = 'vendor_payment_accepted' THEN CONCAT(vendor_name, '|', phone) END) as verified_vendors
        FROM vendors
    ";
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Format grouped vendors data
    $formattedVendors = [];
    foreach ($vendors as $vendor) {
        $successRate = $vendor['total_jobs_assigned'] > 0 ? round(($vendor['completed_jobs'] / $vendor['total_jobs_assigned']) * 100, 1) : 0;
        
        // Calculate total amount earned
        $totalAmount = $vendor['paid_jobs'] * $vendor['avg_quote_amount'];
        
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
            'name' => $vendor['vendor_name'],
            'phone' => $vendor['phone'],
            'location' => $vendor['location'] ?? 'Not specified',
            'status' => $latestStatus,
            'quote_type' => $vendor['quote_type'] ?? 'Mixed',
            'min_quote_amount' => $vendor['min_quote_amount'] ?? 0,
            'max_quote_amount' => $vendor['max_quote_amount'] ?? 0,
            'avg_quote_amount' => round($vendor['avg_quote_amount'], 2) ?? 0,
            'total_amount_earned' => round($totalAmount, 2),
            'vendor_platform' => $vendor['vendor_platform'] ?? 'Not specified',
            'total_jobs_assigned' => (int)$vendor['total_jobs_assigned'],
            'completed_jobs' => (int)$vendor['completed_jobs'],
            'paid_jobs' => (int)$vendor['paid_jobs'],
            'success_rate' => $successRate,
            'job_names' => $displayJobs,
            'all_job_names' => $vendor['job_names'],
            'first_assigned' => $vendor['first_assigned'],
            'last_assigned' => $vendor['last_assigned'],
            'avatar' => strtoupper(substr($vendor['vendor_name'], 0, 2)),
            'is_grouped' => true
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'vendors' => $formattedVendors,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalCount / $limit),
                'total_count' => (int)$totalCount,
                'per_page' => $limit
            ]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
