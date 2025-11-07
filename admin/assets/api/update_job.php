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
    $storeName = $input['store_name'] ?? null;
    $address = $input['address'] ?? null;
    $jobType = $input['job_type'] ?? null;
    $jobSLA = $input['job_sla'] ?? null;
    $jobDetails = $input['job_details'] ?? null;
    $additionalNotes = $input['additional_notes'] ?? null;
    $newPictures = $input['new_pictures'] ?? [];

    // Validate required fields
    if (!$jobId) {
        throw new Exception('Job ID is required');
    }
    if (!$storeName) {
        throw new Exception('Store name is required');
    }
    if (!$address) {
        throw new Exception('Address is required');
    }
    if (!$jobType) {
        throw new Exception('Job type is required');
    }
    if (!$jobSLA) {
        throw new Exception('Job SLA is required');
    }
    if (!$jobDetails) {
        throw new Exception('Job details are required');
    }

    // Date restrictions removed for historical data entry
    // Allow any date/time including past dates

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Update job details
        $updateSql = "UPDATE jobs SET 
                        store_name = :store_name,
                        address = :address,
                        job_type = :job_type,
                        job_detail = :job_detail,
                        additional_notes = :additional_notes,
                        job_sla = :job_sla,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = :job_id";

        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':store_name', $storeName, PDO::PARAM_STR);
        $updateStmt->bindParam(':address', $address, PDO::PARAM_STR);
        $updateStmt->bindParam(':job_type', $jobType, PDO::PARAM_STR);
        $updateStmt->bindParam(':job_detail', $jobDetails, PDO::PARAM_STR);
        $updateStmt->bindParam(':additional_notes', $additionalNotes, PDO::PARAM_STR);
        $updateStmt->bindParam(':job_sla', $jobSLA, PDO::PARAM_STR);
        $updateStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $updateStmt->execute();

        // Handle new pictures if any
        if (!empty($newPictures)) {
            $picturesDir = '../../../uploads/job_pictures/';
            if (!is_dir($picturesDir)) {
                mkdir($picturesDir, 0755, true);
            }

            foreach ($newPictures as $picture) {
                if (isset($picture['data']) && isset($picture['name'])) {
                    // Decode base64 image
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $picture['data']));
                    
                    // Generate unique filename
                    $extension = pathinfo($picture['name'], PATHINFO_EXTENSION);
                    $filename = 'job_' . $jobId . '_' . uniqid() . '.' . $extension;
                    $filepath = $picturesDir . $filename;

                    // Save file
                    if (file_put_contents($filepath, $imageData)) {
                        // Insert picture record
                        $pictureSql = "INSERT INTO job_pictures (job_id, picture_name, picture_path) VALUES (:job_id, :picture_name, :picture_path)";
                        $pictureStmt = $pdo->prepare($pictureSql);
                        $pictureStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
                        $pictureStmt->bindParam(':picture_name', $picture['name'], PDO::PARAM_STR);
                        $pictureStmt->bindParam(':picture_path', $filepath, PDO::PARAM_STR);
                        $pictureStmt->execute();
                    }
                }
            }
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Job updated successfully'
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
