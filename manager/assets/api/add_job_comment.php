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

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $jobId = $input['job_id'] ?? null;
    $comment = trim($input['comment'] ?? '');

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    if (empty($comment)) {
        throw new Exception('Comment cannot be empty');
    }

    if (strlen($comment) > 1000) {
        throw new Exception('Comment is too long (max 1000 characters)');
    }

    // Verify job exists and is assigned to current user
    $jobCheckQuery = "SELECT id FROM jobs WHERE id = :job_id AND assigned_to = :user_id";
    $jobStmt = $pdo->prepare($jobCheckQuery);
    $jobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $jobStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $jobStmt->execute();
    
    if (!$jobStmt->fetch()) {
        throw new Exception('Job not found or not assigned to you');
    }

    // Insert comment
    $insertQuery = "
        INSERT INTO job_comments (job_id, user_id, user_role, comment, created_at) 
        VALUES (:job_id, :user_id, :user_role, :comment, NOW())
    ";

    $stmt = $pdo->prepare($insertQuery);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':user_role', $_SESSION['user_role']);
    $stmt->bindParam(':comment', $comment);
    $stmt->execute();

    $commentId = $pdo->lastInsertId();

    // Get the created comment with user details
    $commentQuery = "
        SELECT 
            jc.*,
            u.first_name,
            u.last_name,
            u.profile_image
        FROM job_comments jc
        JOIN users u ON jc.user_id = u.id
        WHERE jc.id = :comment_id
    ";

    $stmt = $pdo->prepare($commentQuery);
    $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
    $stmt->execute();
    $newComment = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully',
        'comment' => [
            'id' => $newComment['id'],
            'comment' => $newComment['comment'],
            'user_name' => trim($newComment['first_name'] . ' ' . $newComment['last_name']),
            'user_role' => $newComment['user_role'],
            'profile_image' => $newComment['profile_image'],
            'created_at' => $newComment['created_at'],
            'time_ago' => 'just now'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
