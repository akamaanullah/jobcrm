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
    $requiredFields = ['title', 'content'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }

    $userId = $_SESSION['user_id'];
    $title = trim($input['title']);
    $content = trim($input['content']);
    $color = $input['color'] ?? '#ffffff';

    // Validate title length
    if (strlen($title) > 255) {
        throw new Exception('Title must be less than 255 characters');
    }

    // Validate color format
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
        $color = '#ffffff'; // Default color if invalid
    }

    $pdo = getDB();

    // Insert note into database
    $stmt = $pdo->prepare("
        INSERT INTO notes (
            user_id,
            title,
            content,
            color,
            created_at,
            updated_at
        ) VALUES (
            :user_id,
            :title,
            :content,
            :color,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':content' => $content,
        ':color' => $color
    ]);

    $noteId = $pdo->lastInsertId();

    // Get the created note
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

    if (!$note) {
        throw new Exception('Failed to retrieve created note');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Note created successfully!',
        'data' => $note
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
