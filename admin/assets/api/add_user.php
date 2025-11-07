<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../../../config/database.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Debug: Log received data
    error_log("Received input data: " . print_r($input, true));
    error_log("Role value received: " . ($input['role'] ?? 'NOT SET'));
    
    // Validate required fields
    $required_fields = ['firstName', 'lastName', 'username', 'password', 'role'];
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
    
    // Check if username already exists
    $check_username = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check_username->execute([$input['username']]);
    if ($check_username->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Prepare insert query
    $sql = "INSERT INTO users (first_name, last_name, username, email, password, bio, profile_image, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // Prepare values for insertion - role should already be validated
    $role_value = $input['role'];
    error_log("Role value being inserted: " . $role_value);
    
    // Execute query
    $result = $stmt->execute([
        $input['firstName'],
        $input['lastName'],
        $input['username'],
        $input['username'] . '@jobcrm.local', // Auto-generate email from username
        $hashed_password,
        $input['bio'] ?? null,
        $input['profileImage'] ?? null,
        $role_value
    ]);
    
    if ($result) {
        $user_id = $pdo->lastInsertId();
        
        // Get the created user data
        $user_query = $pdo->prepare("SELECT id, first_name, last_name, username, email, bio, profile_image, role, created_at FROM users WHERE id = ?");
        $user_query->execute([$user_id]);
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create user']);
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
