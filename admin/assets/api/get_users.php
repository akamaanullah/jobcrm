<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../../../config/database.php';

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get database connection
    $pdo = getDB();
    
    // Get query parameters
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'created_at';
    $order = $_GET['order'] ?? 'DESC';
    
    // Build query - exclude admin users from the list
    $sql = "SELECT id, first_name, last_name, username, email, bio, profile_image, role, created_at FROM users WHERE role != 'admin'";
    $params = [];
    
    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR username LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    
    // Add sorting
    $allowedSorts = ['first_name', 'last_name', 'username', 'created_at', 'role'];
    if (in_array($sort, $allowedSorts)) {
        $sql .= " ORDER BY $sort";
        if (strtoupper($order) === 'ASC' || strtoupper($order) === 'DESC') {
            $sql .= " $order";
        }
    } else {
        $sql .= " ORDER BY created_at DESC";
    }
    
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user statistics
    $stats = [
        'total_users' => 0,
        'active_users' => 0,
        'regular_users' => 0,
        'admin_users' => 0
    ];
    
    // Count total users (excluding admin)
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
    $countStmt->execute();
    $stats['total_users'] = $countStmt->fetch()['total'];
    
    // Count users by role (excluding admin from list)
    $roleStmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users WHERE role != 'admin' GROUP BY role");
    $roleStmt->execute();
    $roleCounts = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($roleCounts as $roleCount) {
        if ($roleCount['role'] === 'user') {
            $stats['regular_users'] = $roleCount['count'];
        }
    }
    
    // Admin users count (separate query for stats display only)
    $adminStmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $adminStmt->execute();
    $adminCount = $adminStmt->fetch()['count'];
    $stats['admin_users'] = $adminCount;
    
    // For now, all users are considered active (you can add status field later)
    $stats['active_users'] = $stats['total_users'];
    
    // Format user data
    foreach ($users as &$user) {
        // Generate avatar initials
        $firstName = $user['first_name'] ?? '';
        $lastName = $user['last_name'] ?? '';
        $user['avatar_initials'] = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        
        // Format full name
        $user['full_name'] = trim($firstName . ' ' . $lastName);
        
        // Format created date
        $user['formatted_date'] = date('M d, Y', strtotime($user['created_at']));
        
        // Add status (for now all are active)
        $user['status'] = 'active';
        
        // Get actual jobs completed count (distinct job_id to avoid counting multiple forms for same job)
        $jobsCompletedStmt = $pdo->prepare("SELECT COUNT(DISTINCT job_id) as completed_count FROM complete_job_forms WHERE user_id = ?");
        $jobsCompletedStmt->execute([$user['id']]);
        $jobsCompletedResult = $jobsCompletedStmt->fetch(PDO::FETCH_ASSOC);
        $user['jobs_completed'] = $jobsCompletedResult['completed_count'] ?? 0;
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'stats' => $stats,
        'total' => count($users)
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
