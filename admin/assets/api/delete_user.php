<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $input['userId'] ?? null;
    
    // Basic validation
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required.']);
        exit;
    }
    
    // Validate user ID is numeric
    if (!is_numeric($userId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);
        if ($stmt->fetchColumn() === 0) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }
        
        // Check if user is admin (prevent deleting admin users)
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);
        $userRole = $stmt->fetchColumn();
        
        if ($userRole === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Cannot delete admin users.']);
            exit;
        }
        
        // Delete user from database
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
        }
        
    } catch (PDOException $e) {
        error_log("Database error in delete_user.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
