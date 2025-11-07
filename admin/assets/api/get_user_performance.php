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
    // Get user performance data
    $performanceQuery = "
        SELECT 
            u.id as user_id,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            u.email,
            u.username,
            
            -- Total jobs assigned to user
            COUNT(DISTINCT j.id) as total_jobs,
            
            -- Completed jobs count
            SUM(CASE WHEN j.status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
            
            -- In progress jobs count
            SUM(CASE WHEN j.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_jobs,
            
            -- Pending jobs count
            SUM(CASE WHEN j.status = 'added' THEN 1 ELSE 0 END) as pending_jobs,
            
            -- Total vendors assigned across all jobs
            (SELECT COUNT(DISTINCT v.id) 
             FROM vendors v 
             WHERE v.job_id IN (SELECT id FROM jobs WHERE assigned_to = u.id)) as total_vendors,
            
            -- Total invoice amount for completed jobs
            COALESCE((SELECT SUM(i.total_amount) 
                      FROM invoices i 
                      INNER JOIN jobs j2 ON i.job_id = j2.id 
                      WHERE j2.assigned_to = u.id), 0) as total_invoice_amount,
            
            -- Number of invoices generated
            (SELECT COUNT(*) 
             FROM invoices i 
             INNER JOIN jobs j3 ON i.job_id = j3.id 
             WHERE j3.assigned_to = u.id) as total_invoices,
            
            -- Average completion time (in days)
            AVG(CASE 
                WHEN j.status = 'completed' AND j.created_at IS NOT NULL AND j.updated_at IS NOT NULL
                THEN DATEDIFF(j.updated_at, j.created_at)
                ELSE NULL 
            END) as avg_completion_days,
            
            -- SLA compliance rate
            SUM(CASE 
                WHEN j.status = 'completed' AND j.updated_at <= j.job_sla 
                THEN 1 
                ELSE 0 
            END) as sla_met_count,
            
            -- Jobs with SLA breach
            SUM(CASE 
                WHEN j.status = 'completed' AND j.updated_at > j.job_sla 
                THEN 1 
                ELSE 0 
            END) as sla_breached_count,
            
            -- User's first job date
            MIN(j.created_at) as first_job_date,
            
            -- User's latest job date
            MAX(j.created_at) as latest_job_date,
            
            -- User creation date
            u.created_at as user_joined_date
            
        FROM users u
        LEFT JOIN jobs j ON u.id = j.assigned_to
        WHERE u.role = 'user'
        GROUP BY u.id, u.first_name, u.last_name, u.email, u.username, u.created_at
        ORDER BY completed_jobs DESC, total_jobs DESC
    ";
    
    $stmt = $pdo->prepare($performanceQuery);
    $stmt->execute();
    $userPerformances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate additional metrics for each user
    foreach ($userPerformances as &$user) {
        // Completion rate
        $user['completion_rate'] = $user['total_jobs'] > 0 
            ? round(($user['completed_jobs'] / $user['total_jobs']) * 100, 2) 
            : 0;
        
        // SLA compliance rate
        $completedCount = $user['completed_jobs'];
        $user['sla_compliance_rate'] = $completedCount > 0 
            ? round(($user['sla_met_count'] / $completedCount) * 100, 2) 
            : 0;
        
        // Average invoice amount
        $user['avg_invoice_amount'] = $user['total_invoices'] > 0 
            ? round($user['total_invoice_amount'] / $user['total_invoices'], 2) 
            : 0;
        
        // Format amounts
        $user['formatted_total_amount'] = '$' . number_format($user['total_invoice_amount'], 2);
        $user['formatted_avg_amount'] = '$' . number_format($user['avg_invoice_amount'], 2);
        
        // Format average completion time
        $user['avg_completion_days'] = $user['avg_completion_days'] 
            ? round($user['avg_completion_days'], 1) 
            : 0;
        
        // Format dates
        $user['first_job_formatted'] = $user['first_job_date'] 
            ? date('M d, Y', strtotime($user['first_job_date'])) 
            : 'N/A';
        $user['latest_job_formatted'] = $user['latest_job_date'] 
            ? date('M d, Y', strtotime($user['latest_job_date'])) 
            : 'N/A';
        $user['joined_formatted'] = date('M d, Y', strtotime($user['user_joined_date']));
        
        // Performance grade
        $completionRate = $user['completion_rate'];
        $slaRate = $user['sla_compliance_rate'];
        
        if ($completionRate >= 90 && $slaRate >= 90) {
            $user['performance_grade'] = 'A+';
            $user['grade_color'] = 'success';
        } elseif ($completionRate >= 80 && $slaRate >= 80) {
            $user['performance_grade'] = 'A';
            $user['grade_color'] = 'success';
        } elseif ($completionRate >= 70 && $slaRate >= 70) {
            $user['performance_grade'] = 'B';
            $user['grade_color'] = 'info';
        } elseif ($completionRate >= 60 && $slaRate >= 60) {
            $user['performance_grade'] = 'C';
            $user['grade_color'] = 'warning';
        } else {
            $user['performance_grade'] = 'D';
            $user['grade_color'] = 'danger';
        }
    }
    
    // Get overall summary statistics
    $summaryQuery = "
        SELECT 
            COUNT(DISTINCT u.id) as total_users,
            SUM(CASE WHEN j.status = 'completed' THEN 1 ELSE 0 END) as total_completed_jobs,
            COALESCE(SUM(i.total_amount), 0) as total_revenue,
            AVG(CASE 
                WHEN j.status = 'completed' AND j.created_at IS NOT NULL AND j.updated_at IS NOT NULL
                THEN DATEDIFF(j.updated_at, j.created_at)
                ELSE NULL 
            END) as overall_avg_completion_days
        FROM users u
        LEFT JOIN jobs j ON u.id = j.assigned_to
        LEFT JOIN invoices i ON j.id = i.job_id
        WHERE u.role = 'user'
    ";
    
    $summaryStmt = $pdo->prepare($summaryQuery);
    $summaryStmt->execute();
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);
    
    // Format summary
    $summary['formatted_total_revenue'] = '$' . number_format($summary['total_revenue'], 2);
    $summary['overall_avg_completion_days'] = $summary['overall_avg_completion_days'] 
        ? round($summary['overall_avg_completion_days'], 1) 
        : 0;
    
    echo json_encode([
        'success' => true,
        'data' => $userPerformances,
        'summary' => $summary
    ]);
    
} catch (PDOException $e) {
    error_log("User Performance API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>