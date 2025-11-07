<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Check if admin is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $userId = $input['userId'] ?? null;
    $newPassword = $input['newPassword'] ?? '';

    if (!$userId) {
        throw new Exception('User ID is required');
    }

    if (empty($newPassword)) {
        throw new Exception('New password is required');
    }

    if (strlen($newPassword) < 8) {
        throw new Exception('New password must be at least 8 characters long');
    }

    $pdo = connectDatabase();

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Hash new password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = :new_password, updated_at = NOW() WHERE id = :user_id");
    $stmt->bindParam(':new_password', $hashedNewPassword);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update password');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
