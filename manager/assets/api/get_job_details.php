<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

// Start session to get user info
session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    // Get job ID from URL parameter
    $job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
    
    if (!$job_id) {
        throw new Exception('Job ID is required');
    }

    $pdo = getDB();

    // Get job details
    $stmt = $pdo->prepare("
        SELECT 
            j.id,
            j.store_name,
            j.address,
            j.job_type,
            j.job_detail,
            j.additional_notes,
            j.job_sla,
            j.status,
            j.created_at,
            j.updated_at
        FROM jobs j 
        WHERE j.id = :job_id
    ");
    
    $stmt->execute([':job_id' => $job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job) {
        throw new Exception('Job not found');
    }

    // Get job pictures/attachments
    $stmt = $pdo->prepare("
        SELECT 
            id,
            picture_name,
            picture_path,
            created_at
        FROM job_pictures 
        WHERE job_id = :job_id
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([':job_id' => $job_id]);
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format job data and convert relative paths to absolute URLs
    foreach ($attachments as &$attachment) {
        // Convert relative path to absolute URL
        if (!empty($attachment['picture_path']) && !filter_var($attachment['picture_path'], FILTER_VALIDATE_URL)) {
            // Clean the path by removing ../ prefixes
            $cleanPath = $attachment['picture_path'];
            $cleanPath = str_replace('../../../', '', $cleanPath);
            $cleanPath = ltrim($cleanPath, './');
            
            // Use relative path from manager folder (../uploads/...)
            // This will work correctly from manager/view-job.php
            $attachment['picture_path'] = '../' . $cleanPath;
        }
    }
    
    $job['attachments'] = $attachments;
    $job['attachment_count'] = count($attachments);
    $job['created_ago'] = getTimeAgo($job['created_at']);
    $job['updated_ago'] = getTimeAgo($job['updated_at']);
    $job['sla_formatted'] = formatSLA($job['job_sla']);
    $job['status_display'] = getStatusDisplayText($job['status']);
    $job['status_class'] = getStatusClass($job['status']);

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $job
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
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
    if (empty($sla)) return ['date' => 'Not set', 'time' => '', 'datetime' => ''];
    
    $date = new DateTime($sla);
    return [
        'date' => $date->format('M j, Y'),
        'time' => $date->format('g:i A'),
        'datetime' => $sla
    ];
}
?>
