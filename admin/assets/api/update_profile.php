<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access or Admin access required');
    }

    $pdo = connectDatabase();

    // Get POST data
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phoneNumber = $_POST['phoneNumber'] ?? '';
    $bio = $_POST['bio'] ?? '';

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email)) {
        throw new Exception('First name, last name, and email are required');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if email already exists for another user
    $checkEmailSql = "SELECT id FROM users WHERE email = :email AND id != :admin_id";
    $checkEmailStmt = $pdo->prepare($checkEmailSql);
    $checkEmailStmt->bindParam(':email', $email, PDO::PARAM_STR);
    $checkEmailStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $checkEmailStmt->execute();
    
    if ($checkEmailStmt->fetch()) {
        throw new Exception('Email already exists for another user');
    }

    // Update profile
    $updateSql = "
        UPDATE users 
        SET 
            first_name = :first_name,
            last_name = :last_name,
            email = :email,
            phone_number = :phone_number,
            bio = :bio,
            updated_at = NOW()
        WHERE id = :admin_id AND role = 'admin'
    ";
    
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
    $updateStmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
    $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
    $updateStmt->bindParam(':phone_number', $phoneNumber, PDO::PARAM_STR);
    $updateStmt->bindParam(':bio', $bio, PDO::PARAM_STR);
    $updateStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    
    if ($updateStmt->execute()) {
        $affectedRows = $updateStmt->rowCount();
        
        // Update session data
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'debug' => [
                'affected_rows' => $affectedRows,
                'admin_id' => $_SESSION['user_id'],
                'updated_fields' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone_number' => $phoneNumber,
                    'bio' => $bio
                ]
            ]
        ]);
    } else {
        $errorInfo = $updateStmt->errorInfo();
        throw new Exception('Failed to update profile: ' . $errorInfo[2]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
