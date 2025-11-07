<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

// Start session
session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
        throw new Exception('Unauthorized access');
    }

    $pdo = connectDatabase();

    $vendorId = $_GET['vendor_id'] ?? null;
    if (!$vendorId) {
        throw new Exception('Vendor ID is required');
    }

    // Get vendor details with job assignment verification
    $vendorQuery = "
        SELECT 
            v.*,
            j.id as job_id
        FROM vendors v
        JOIN jobs j ON v.job_id = j.id
        WHERE v.id = :vendor_id AND j.assigned_to = :user_id
    ";

    $stmt = $pdo->prepare($vendorQuery);
    $stmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        throw new Exception('Vendor not found or not authorized');
    }

    // Format vendor data
    $formattedVendor = [
        'id' => $vendor['id'],
        'vendor_name' => $vendor['vendor_name'],
        'phone' => $vendor['phone'],
        'quote_type' => $vendor['quote_type'],
        'quote_amount' => $vendor['quote_amount'],
        'vendor_platform' => $vendor['vendor_platform'],
        'location' => $vendor['location'],
        'appointment_date_time' => $vendor['appointment_date_time'],
        'status' => $vendor['status'],
        'created_at' => $vendor['created_at'],
        'updated_at' => $vendor['updated_at']
    ];

    echo json_encode([
        'success' => true,
        'vendor' => $formattedVendor
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>