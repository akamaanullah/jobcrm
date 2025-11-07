<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['companyName']) || empty($input['invoiceTo']) || empty($input['invoiceDate']) || empty($input['items']) || empty($input['addresses'])) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
        exit;
    }
    
    $companyName = trim($input['companyName']);
    $invoiceTo = trim($input['invoiceTo']);
    $invoiceDate = $input['invoiceDate'];
    $items = $input['items'];
    $addresses = $input['addresses'];
    $totalAmount = $input['total'];
    $jobId = $input['jobId'] ?? null; // Get job_id if provided
    
    try {
        $pdo = getDB();
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Generate unique invoice number
        $invoiceNumber = 'INV' . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check if invoice number already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ?");
        $checkStmt->execute([$invoiceNumber]);
        while ($checkStmt->fetchColumn() > 0) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $checkStmt->execute([$invoiceNumber]);
        }
        
        // Insert invoice header
        $stmt = $pdo->prepare("INSERT INTO invoices (company_name, date, invoice_number, invoice_to, job_id, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $companyName,
            $invoiceDate,
            $invoiceNumber,
            $invoiceTo,
            $jobId,
            $totalAmount
        ]);
        
        $invoiceId = $pdo->lastInsertId();
        
        // Insert invoice items (without addresses)
        $itemStmt = $pdo->prepare("INSERT INTO invoices_items (invoice_number, item, quantity, unit_price) VALUES (?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $itemStmt->execute([
                $invoiceNumber,
                $item['description'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        // Insert addresses in separate table
        $addressStmt = $pdo->prepare("INSERT INTO invoice_address (address, invc_id) VALUES (?, ?)");
        
        foreach ($addresses as $address) {
            $addressStmt->execute([
                $address,
                $invoiceId
            ]);
        }
        
        // If job_id is provided, mark invoice reminder notifications as resolved
        if ($jobId) {
            $updateNotificationStmt = $pdo->prepare("
                UPDATE notifications 
                SET is_read = 1, action_required = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE job_id = ? 
                AND type = 'invoice_reminder' 
                AND notify_for = 'admin'
            ");
            $updateNotificationStmt->execute([$jobId]);
            
            // Also mark invoice reminders as sent/completed
            $updateReminderStmt = $pdo->prepare("
                UPDATE invoice_reminders 
                SET notification_sent = 1, sent_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP 
                WHERE job_id = ?
            ");
            $updateReminderStmt->execute([$jobId]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Invoice saved successfully!',
            'data' => [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'company_name' => $companyName,
                'invoice_to' => $invoiceTo,
                'invoice_date' => $invoiceDate,
                'total_amount' => $totalAmount,
                'job_id' => $jobId,
                'items_count' => count($items),
                'addresses_count' => count($addresses)
            ]
        ]);
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        error_log("Database error in save_invoice.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        error_log("General error in save_invoice.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
