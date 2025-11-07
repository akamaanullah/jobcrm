<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../../../config/database.php';

// Check if request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Start session and check admin authorization
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Admin privileges required.']);
    exit;
}

try {
    // Get database connection
    $pdo = getDB();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (empty($input['userId'])) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    $userId = $input['userId'];

    // Validate required fields
    $required_fields = ['firstName', 'lastName', 'username', 'role'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            exit;
        }
    }

    // Validate username format (alphanumeric, underscore, and dot allowed, 3-20 characters)
    if (!preg_match('/^[a-zA-Z0-9_.]{3,20}$/', $input['username'])) {
        echo json_encode(['success' => false, 'message' => 'Username must be 3-20 characters and contain only letters, numbers, underscores, and dots']);
        exit;
    }

    // Validate role
    if (!in_array($input['role'], ['user', 'manager', 'admin'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        exit;
    }

    // Check if user exists
    $check_user = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->execute([$userId]);
    if (!$check_user->fetch()) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check if username already exists for another user
    $check_username = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check_username->execute([$input['username'], $userId]);
    if ($check_username->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists for another user']);
        exit;
    }

    // Prepare update query
    $sql = "UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            username = ?, 
            email = ?,
            bio = ?, 
            role = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    // Execute query
    $result = $stmt->execute([
        $input['firstName'],
        $input['lastName'],
        $input['username'],
        $input['username'] . '@jobcrm.local', // Auto-generate email from username
        $input['bio'] ?? null,
        $input['role'],
        $userId
    ]);

    if ($result) {
        // Get the updated user data
        $user_query = $pdo->prepare("SELECT id, first_name, last_name, username, email, bio, profile_image, role, created_at, updated_at FROM users WHERE id = ?");
        $user_query->execute([$userId]);
        $user = $user_query->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user']);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>