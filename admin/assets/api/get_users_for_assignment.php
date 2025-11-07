<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('Unauthorized access');
    }

    if ($_SESSION['user_role'] !== 'admin') {
        throw new Exception('Admin access required');
    }

    $pdo = getDB();

    // Get all users (excluding admin)
    $sql = "SELECT id, first_name, last_name, email 
            FROM users 
            WHERE role = 'user' 
            ORDER BY first_name, last_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);

} catch (Exception $e) {
    error_log("Get Users for Assignment Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>
