<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $currentPassword = $input['currentPassword'] ?? '';
    $newPassword = $input['newPassword'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        throw new Exception('Current password and new password are required');
    }

    if (strlen($newPassword) < 8) {
        throw new Exception('New password must be at least 8 characters long');
    }

    $pdo = connectDatabase();

    // Get current user's password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        throw new Exception('Current password is incorrect');
    }

    // Hash new password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = :new_password, updated_at = NOW() WHERE id = :user_id");
    $stmt->bindParam(':new_password', $hashedNewPassword);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    
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
