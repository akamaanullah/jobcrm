<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    // Validate username format (alphanumeric, underscore, and dot allowed, 3-20 characters)
    if (!preg_match('/^[a-zA-Z0-9_.]{3,20}$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Invalid username format.']);
        exit;
    }

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, username, email, password, role FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            exit;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            exit;
        }

        // Start session
        session_start();

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Prepare user data for response
        $userData = [
            'id' => $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Determine redirect URL based on role
        if ($user['role'] === 'admin') {
            $redirectUrl = 'admin/dashboard.php';
        } elseif ($user['role'] === 'manager') {
            $redirectUrl = 'manager/dashboard.php';
        } else {
            $redirectUrl = 'user/dashboard.php';
        }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Redirecting...',
            'user' => $userData,
            'redirect_url' => $redirectUrl
        ]);

    } catch (PDOException $e) {
        error_log("Database error in login.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>