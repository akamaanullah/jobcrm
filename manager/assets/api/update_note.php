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
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    $requiredFields = ['note_id', 'title', 'content'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }

    $userId = $_SESSION['user_id'];
    $noteId = (int) $input['note_id'];
    $title = trim($input['title']);
    $content = trim($input['content']);
    $color = $input['color'] ?? '#ffffff';

    // Validate note ID
    if ($noteId <= 0) {
        throw new Exception('Invalid note ID');
    }

    // Validate title length
    if (strlen($title) > 255) {
        throw new Exception('Title must be less than 255 characters');
    }

    // Validate color format
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
        $color = '#ffffff'; // Default color if invalid
    }

    $pdo = getDB();

    // Check if note exists and belongs to user
    $checkStmt = $pdo->prepare("
        SELECT id FROM notes 
        WHERE id = :note_id AND user_id = :user_id
    ");
    $checkStmt->execute([':note_id' => $noteId, ':user_id' => $userId]);
    
    if (!$checkStmt->fetch()) {
        throw new Exception('Note not found or access denied');
    }

    // Update note
    $stmt = $pdo->prepare("
        UPDATE notes 
        SET 
            title = :title,
            content = :content,
            color = :color,
            updated_at = NOW()
        WHERE id = :note_id AND user_id = :user_id
    ");

    $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':color' => $color,
        ':note_id' => $noteId,
        ':user_id' => $userId
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to update note');
    }

    // Get the updated note
    $getNoteStmt = $pdo->prepare("
        SELECT 
            id,
            title,
            content,
            color,
            created_at,
            updated_at
        FROM notes 
        WHERE id = :id AND user_id = :user_id
    ");
    
    $getNoteStmt->execute([':id' => $noteId, ':user_id' => $userId]);
    $note = $getNoteStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Note updated successfully!',
        'data' => $note
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
