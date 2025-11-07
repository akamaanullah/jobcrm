<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

// Start session to get user info
session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
        throw new Exception('Unauthorized access');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (empty($input['note_id'])) {
        throw new Exception('Note ID is required');
    }

    $userId = $_SESSION['user_id'];
    $noteId = (int) $input['note_id'];

    // Validate note ID
    if ($noteId <= 0) {
        throw new Exception('Invalid note ID');
    }

    $pdo = getDB();

    // Check if note exists and belongs to user
    $checkStmt = $pdo->prepare("
        SELECT id, is_pinned FROM notes 
        WHERE id = :note_id AND user_id = :user_id
    ");
    $checkStmt->execute([':note_id' => $noteId, ':user_id' => $userId]);
    $note = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$note) {
        throw new Exception('Note not found or access denied');
    }

    // Toggle pinned status
    $newPinnedStatus = $note['is_pinned'] ? 0 : 1;
    
    $stmt = $pdo->prepare("
        UPDATE notes 
        SET 
            is_pinned = :is_pinned,
            updated_at = NOW()
        WHERE id = :note_id AND user_id = :user_id
    ");

    $stmt->execute([
        ':is_pinned' => $newPinnedStatus,
        ':note_id' => $noteId,
        ':user_id' => $userId
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to update note');
    }

    $action = $newPinnedStatus ? 'pinned' : 'unpinned';
    $message = $newPinnedStatus ? 'Note pinned successfully!' : 'Note unpinned successfully!';

    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'note_id' => $noteId,
            'is_pinned' => (bool) $newPinnedStatus,
            'action' => $action
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
