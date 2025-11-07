<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in and has 'manager' role
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    $pdo = connectDatabase();

    $jobId = $_GET['job_id'] ?? null;
    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Verify that the job is assigned to the current user
    $jobAssignmentCheck = "SELECT id FROM jobs WHERE id = :job_id AND assigned_to = :user_id";
    $assignmentStmt = $pdo->prepare($jobAssignmentCheck);
    $assignmentStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $assignmentStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $assignmentStmt->execute();

    if (!$assignmentStmt->fetch()) {
        throw new Exception('Access denied: Job not assigned to this user.');
    }

    // Get all vendors that are NOT already added to this job
    // A vendor is available if it doesn't have a job_id or has a different job_id
    $availableVendorsQuery = "
        SELECT DISTINCT
            v.id,
            v.vendor_name,
            v.phone,
            v.quote_type,
            v.quote_amount,
            v.vendor_platform,
            v.location,
            v.appointment_date_time,
            v.status,
            v.created_at,
            v.updated_at
        FROM vendors v
        WHERE (v.job_id IS NULL OR v.job_id != :job_id)
        ORDER BY v.created_at DESC
    ";

    $stmt = $pdo->prepare($availableVendorsQuery);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->execute();
    $availableVendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format vendors for frontend
    $formattedVendors = array_map(function($vendor) {
        return [
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
    }, $availableVendors);

    echo json_encode([
        'success' => true,
        'vendors' => $formattedVendors,
        'count' => count($formattedVendors)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
