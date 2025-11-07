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

    $pdo = connectDatabase();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $pictureId = $input['picture_id'] ?? null;

    if (!$pictureId) {
        throw new Exception('Picture ID is required');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Get picture details before deletion
        $getPictureSql = "SELECT * FROM job_pictures WHERE id = :picture_id";
        $getPictureStmt = $pdo->prepare($getPictureSql);
        $getPictureStmt->bindParam(':picture_id', $pictureId, PDO::PARAM_INT);
        $getPictureStmt->execute();
        $picture = $getPictureStmt->fetch(PDO::FETCH_ASSOC);

        if (!$picture) {
            throw new Exception('Picture not found');
        }

        // Delete from database
        $deleteSql = "DELETE FROM job_pictures WHERE id = :picture_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindParam(':picture_id', $pictureId, PDO::PARAM_INT);
        $deleteStmt->execute();

        // Delete physical file
        $filePath = $picture['picture_path'];
        
        // Fix file path - remove ../../../ if present
        if (strpos($filePath, '../../../') === 0) {
            $filePath = str_replace('../../../', '', $filePath);
        }
        
        // If path doesn't start with ../, add it for admin directory context
        if (!str_starts_with($filePath, '../') && !str_starts_with($filePath, 'http')) {
            $filePath = '../' . $filePath;
        }

        // Check if file exists and delete it
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                // Log warning but don't fail the transaction
                error_log("Warning: Could not delete file: " . $filePath);
            }
        } else {
            // Log warning but don't fail the transaction
            error_log("Warning: File not found: " . $filePath);
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Picture deleted successfully'
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
