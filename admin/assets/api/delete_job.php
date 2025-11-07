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

    if ($_SESSION['user_role'] !== 'admin') {
        throw new Exception('Admin access required');
    }

    $pdo = getDB();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $jobId = $input['job_id'] ?? null;

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Get job details for logging
        $getJobSql = "SELECT store_name FROM jobs WHERE id = :job_id";
        $getJobStmt = $pdo->prepare($getJobSql);
        $getJobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $getJobStmt->execute();
        $job = $getJobStmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            throw new Exception('Job not found');
        }

        $jobName = $job['store_name'];

        // 1. Delete job pictures and their files
        $getPicturesSql = "SELECT picture_path FROM job_pictures WHERE job_id = :job_id";
        $getPicturesStmt = $pdo->prepare($getPicturesSql);
        $getPicturesStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $getPicturesStmt->execute();
        $pictures = $getPicturesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Delete physical picture files
        foreach ($pictures as $picture) {
            $filePath = $picture['picture_path'];

            // Fix file path
            if (strpos($filePath, '../../../') === 0) {
                $filePath = str_replace('../../../', '', $filePath);
            }

            if (!str_starts_with($filePath, '../') && !str_starts_with($filePath, 'http')) {
                $filePath = '../' . $filePath;
            }

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete job pictures from database
        $deletePicturesSql = "DELETE FROM job_pictures WHERE job_id = :job_id";
        $deletePicturesStmt = $pdo->prepare($deletePicturesSql);
        $deletePicturesStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deletePicturesStmt->execute();

        // 2. Delete message attachments and their files
        $getAttachmentsSql = "SELECT file_path FROM message_attachments WHERE message_id IN (SELECT id FROM messages WHERE job_id = :job_id)";
        $getAttachmentsStmt = $pdo->prepare($getAttachmentsSql);
        $getAttachmentsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $getAttachmentsStmt->execute();
        $attachments = $getAttachmentsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Delete physical attachment files
        foreach ($attachments as $attachment) {
            $filePath = $attachment['file_path'];

            // Fix file path
            if (strpos($filePath, '../../../') === 0) {
                $filePath = str_replace('../../../', '', $filePath);
            }

            if (!str_starts_with($filePath, '../') && !str_starts_with($filePath, 'http')) {
                $filePath = '../' . $filePath;
            }

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete message attachments from database
        $deleteAttachmentsSql = "DELETE FROM message_attachments WHERE message_id IN (SELECT id FROM messages WHERE job_id = :job_id)";
        $deleteAttachmentsStmt = $pdo->prepare($deleteAttachmentsSql);
        $deleteAttachmentsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deleteAttachmentsStmt->execute();

        // 3. Delete messages (chats) related to this job
        $deleteMessagesSql = "DELETE FROM messages WHERE job_id = :job_id";
        $deleteMessagesStmt = $pdo->prepare($deleteMessagesSql);
        $deleteMessagesStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deleteMessagesStmt->execute();

        // 4. Delete vendors related to this job
        $deleteVendorsSql = "DELETE FROM vendors WHERE job_id = :job_id";
        $deleteVendorsStmt = $pdo->prepare($deleteVendorsSql);
        $deleteVendorsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deleteVendorsStmt->execute();

        // 5. Delete job completion forms and their attachments
        $getCompletionAttachmentsSql = "SELECT attachment_path FROM job_completion_attachments WHERE job_complete_id IN (SELECT id FROM complete_job_forms WHERE job_id = :job_id)";
        $getCompletionAttachmentsStmt = $pdo->prepare($getCompletionAttachmentsSql);
        $getCompletionAttachmentsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $getCompletionAttachmentsStmt->execute();
        $completionAttachments = $getCompletionAttachmentsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Delete physical completion attachment files
        foreach ($completionAttachments as $attachment) {
            $filePath = $attachment['attachment_path'];

            // Fix file path
            if (strpos($filePath, '../../../') === 0) {
                $filePath = str_replace('../../../', '', $filePath);
            }

            if (!str_starts_with($filePath, '../') && !str_starts_with($filePath, 'http')) {
                $filePath = '../' . $filePath;
            }

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete job completion attachments from database
        $deleteCompletionAttachmentsSql = "DELETE FROM job_completion_attachments WHERE job_complete_id IN (SELECT id FROM complete_job_forms WHERE job_id = :job_id)";
        $deleteCompletionAttachmentsStmt = $pdo->prepare($deleteCompletionAttachmentsSql);
        $deleteCompletionAttachmentsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deleteCompletionAttachmentsStmt->execute();

        // Delete job completion forms from database
        $deleteCompletionFormsSql = "DELETE FROM complete_job_forms WHERE job_id = :job_id";
        $deleteCompletionFormsStmt = $pdo->prepare($deleteCompletionFormsSql);
        $deleteCompletionFormsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deleteCompletionFormsStmt->execute();

        // 6. Delete payment requests related to this job
        $deletePaymentsSql = "DELETE FROM request_payments WHERE job_id = :job_id";
        $deletePaymentsStmt = $pdo->prepare($deletePaymentsSql);
        $deletePaymentsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deletePaymentsStmt->execute();

        // 7. Delete notifications related to this job
        $deleteNotificationsSql = "DELETE FROM notifications WHERE job_id = :job_id";
        $deleteNotificationsStmt = $pdo->prepare($deleteNotificationsSql);
        $deleteNotificationsStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deleteNotificationsStmt->execute();

        // 8. Finally, delete the job itself
        $deleteJobSql = "DELETE FROM jobs WHERE id = :job_id";
        $deleteJobStmt = $pdo->prepare($deleteJobSql);
        $deleteJobStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $deleteJobStmt->execute();

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => "Job '{$jobName}' and all related data deleted successfully"
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
