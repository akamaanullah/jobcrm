<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('Unauthorized access');
    }

    if ($_SESSION['user_role'] !== 'manager') {
        throw new Exception('Manager access required');
    }

    $pdo = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['job_ids']) || !isset($input['user_id'])) {
            throw new Exception('Missing required parameters');
        }

        $jobIds = $input['job_ids'];
        $userId = $input['user_id'];

        if (!is_array($jobIds) || empty($jobIds)) {
            throw new Exception('Invalid job IDs');
        }

        // Validate user exists and get user details
        $userCheckSql = "SELECT id, first_name, last_name FROM users WHERE id = :user_id AND role = 'user'";
        $userStmt = $pdo->prepare($userCheckSql);
        $userStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception('Invalid user selected');
        }

        $userName = trim($user['first_name'] . ' ' . $user['last_name']);

        // Validate all job IDs exist
        $placeholders = str_repeat('?,', count($jobIds) - 1) . '?';
        $jobCheckSql = "SELECT id FROM jobs WHERE id IN ($placeholders)";
        $jobStmt = $pdo->prepare($jobCheckSql);
        $jobStmt->execute($jobIds);
        $existingJobs = $jobStmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($existingJobs) !== count($jobIds)) {
            throw new Exception('One or more jobs not found');
        }

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Update jobs with assigned user
            $updateSql = "UPDATE jobs SET assigned_to = :user_id, updated_at = NOW() WHERE id = :job_id";
            $updateStmt = $pdo->prepare($updateSql);

            $assignedCount = 0;
            foreach ($jobIds as $jobId) {
                $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $updateStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
                $updateStmt->execute();

                // Insert timeline event for job assignment
                $timelineStmt = $pdo->prepare("
                    INSERT INTO job_timeline (
                        job_id, event_type, title, description, event_time, 
                        status, icon, created_by, metadata
                    ) VALUES (
                        :job_id, 'user_assigned', 'Job Assigned to User', :description, NOW(),
                        'completed', 'bi-person-check-fill', :created_by, :metadata
                    )
                ");

                $description = "Job was assigned to user: {$userName} by manager";
                $metadata = json_encode([
                    'assigned_user_id' => $userId,
                    'assigned_user_name' => $userName,
                    'assigned_by' => $_SESSION['user_id'],
                    'assigned_by_role' => 'manager'
                ]);

                $timelineStmt->execute([
                    ':job_id' => $jobId,
                    ':description' => $description,
                    ':created_by' => $_SESSION['user_id'],
                    ':metadata' => $metadata
                ]);

                $assignedCount++;
            }

            // Create notifications for assigned user
            $notificationSql = "INSERT INTO notifications (user_id, type, message, notify_for, created_at) VALUES (:user_id, :type, :message, 'user', NOW())";
            $notificationStmt = $pdo->prepare($notificationSql);

            $notificationType = 'new_job_added'; // Using existing type
            $notificationMessage = "You have been assigned {$assignedCount} new job(s) by manager. Please check your dashboard.";

            $notificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $notificationStmt->bindParam(':type', $notificationType);
            $notificationStmt->bindParam(':message', $notificationMessage);
            $notificationStmt->execute();

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Successfully assigned {$assignedCount} jobs",
                'assigned_count' => $assignedCount
            ]);

        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }

    } else {
        throw new Exception('Invalid request method');
    }

} catch (Exception $e) {
    error_log("Bulk Assign Jobs Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

