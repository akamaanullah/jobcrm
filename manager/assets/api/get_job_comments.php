<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('Unauthorized access');
    }

    // Check if user role is 'manager'
    if ($_SESSION['user_role'] !== 'manager') {
        throw new Exception('Access denied');
    }

    $pdo = connectDatabase();

    $jobId = $_GET['job_id'] ?? null;
    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Verify job is assigned to current user
    $jobCheckQuery = "SELECT id FROM jobs WHERE id = :job_id AND assigned_to = :user_id";
    $jobStmt = $pdo->prepare($jobCheckQuery);
    $jobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $jobStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $jobStmt->execute();
    
    if (!$jobStmt->fetch()) {
        throw new Exception('Job not found or not assigned to you');
    }

    // Get comments with user details
    $commentsQuery = "
        SELECT 
            jc.*,
            u.first_name,
            u.last_name,
            u.profile_image
        FROM job_comments jc
        JOIN users u ON jc.user_id = u.id
        WHERE jc.job_id = :job_id
        ORDER BY jc.created_at DESC
    ";

    $stmt = $pdo->prepare($commentsQuery);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format comments for frontend
    $formattedComments = array_map(function($comment) {
        return [
            'id' => $comment['id'],
            'comment' => $comment['comment'],
            'user_name' => trim($comment['first_name'] . ' ' . $comment['last_name']),
            'user_role' => $comment['user_role'],
            'profile_image' => $comment['profile_image'],
            'created_at' => $comment['created_at'],
            'time_ago' => getTimeAgo($comment['created_at'])
        ];
    }, $comments);

    echo json_encode([
        'success' => true,
        'comments' => $formattedComments,
        'count' => count($formattedComments)
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
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>
