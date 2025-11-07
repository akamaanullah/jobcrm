<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access or Admin access required');
    }

    $pdo = connectDatabase();

    // Get admin profile data
    $profileSql = "
        SELECT 
            id,
            first_name,
            last_name,
            email,
            phone_number,
            bio,
            created_at,
            profile_image
        FROM users 
        WHERE id = :admin_id AND role = 'admin'
    ";
    
    $profileStmt = $pdo->prepare($profileSql);
    $profileStmt->bindParam(':admin_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $profileStmt->execute();
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        throw new Exception('Admin profile not found');
    }

    // Format join date
    $profile['join_date'] = date('F j, Y', strtotime($profile['created_at']));
    
    // Set default values if empty
    $profile['first_name'] = $profile['first_name'] ?: 'Admin';
    $profile['last_name'] = $profile['last_name'] ?: 'User';
    $profile['phone_number'] = $profile['phone_number'] ?: '+1 (555) 123-4567';
    $profile['bio'] = $profile['bio'] ?: 'Experienced system administrator with expertise in managing job portal systems, user management, and system maintenance. Committed to ensuring smooth operations and excellent user experience.';

    echo json_encode([
        'success' => true,
        'data' => $profile
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
