<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if user role is 'user'
if ($_SESSION['user_role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $_SESSION['user_id'];
    $firstName = trim($input['firstName'] ?? '');
    $lastName = trim($input['lastName'] ?? '');
    $email = trim($input['email'] ?? '');
    $phoneNumber = trim($input['phoneNumber'] ?? '');
    $bio = trim($input['bio'] ?? '');
    
    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required.']);
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit();
    }
    
    try {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
        $stmt->execute([':email' => $email, ':user_id' => $userId]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            echo json_encode(['success' => false, 'message' => 'Email address is already taken by another user.']);
            exit();
        }
        
        // Update user profile
        $stmt = $pdo->prepare("UPDATE users SET 
            first_name = :first_name, 
            last_name = :last_name, 
            email = :email, 
            phone_number = :phone_number, 
            bio = :bio, 
            updated_at = CURRENT_TIMESTAMP 
            WHERE id = :user_id");
        
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':phone_number' => $phoneNumber,
            ':bio' => $bio,
            ':user_id' => $userId
        ]);
        
        // Update session data
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => [
                'id' => $userId,
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'role' => $_SESSION['user_role']
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in update_profile.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
