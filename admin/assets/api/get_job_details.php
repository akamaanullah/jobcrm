<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get job_id from URL parameters
$jobId = $_GET['job_id'] ?? '';

if (empty($jobId)) {
    echo json_encode(['success' => false, 'message' => 'Job ID is required']);
    exit;
}

try {
    $pdo = connectDatabase();

    // Get job details with assigned user and vendor information
    $jobQuery = "
        SELECT 
            j.*,
            CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
            v.vendor_name
        FROM jobs j
        LEFT JOIN users u ON j.assigned_to = u.id
        LEFT JOIN vendors v ON j.id = v.job_id AND v.status = 'vendor_payment_accepted'
        WHERE j.id = :job_id
    ";

    $stmt = $pdo->prepare($jobQuery);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->execute();

    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        echo json_encode(['success' => false, 'message' => 'Job not found']);
        exit;
    }

    // Format the job data
    $formattedJob = [
        'id' => $job['id'],
        'store_name' => $job['store_name'],
        'address' => $job['address'],
        'job_type' => $job['job_type'],
        'job_detail' => $job['job_detail'],
        'additional_notes' => $job['additional_notes'],
        'job_sla' => $job['job_sla'],
        'status' => $job['status'],
        'assigned_to' => $job['assigned_to'],
        'assigned_to_name' => $job['assigned_to_name'],
        'vendor_name' => $job['vendor_name'] ?: 'No vendor assigned',
        'created_at' => $job['created_at'],
        'updated_at' => $job['updated_at']
    ];

    echo json_encode([
        'success' => true,
        'job' => $formattedJob
    ]);

} catch (Exception $e) {
    error_log("Get Job Details API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>