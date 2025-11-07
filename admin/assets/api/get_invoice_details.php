<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $invoiceNumber = $_GET['invoice_number'] ?? '';
    
    if (empty($invoiceNumber)) {
        echo json_encode(['success' => false, 'message' => 'Invoice number is required.']);
        exit;
    }
    
    try {
        $pdo = getDB();
        
        // Get invoice header details
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE invoice_number = ?");
        $stmt->execute([$invoiceNumber]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice not found.']);
            exit;
        }
        
        // Get invoice items
        $itemStmt = $pdo->prepare("SELECT * FROM invoices_items WHERE invoice_number = ? ORDER BY id");
        $itemStmt->execute([$invoiceNumber]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get invoice addresses
        $addressStmt = $pdo->prepare("SELECT * FROM invoice_address WHERE invc_id = ? ORDER BY id");
        $addressStmt->execute([$invoice['id']]);
        $addresses = $addressStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format invoice data
        $invoice['formatted_date'] = date('M d, Y', strtotime($invoice['date']));
        $invoice['formatted_created'] = date('M d, Y H:i', strtotime($invoice['created_at']));
        $invoice['formatted_amount'] = '$' . number_format($invoice['total_amount'], 2);
        
        // Format items
        foreach ($items as &$item) {
            $item['formatted_price'] = '$' . number_format($item['unit_price'], 2);
            $item['total'] = $item['quantity'] * $item['unit_price'];
            $item['formatted_total'] = '$' . number_format($item['total'], 2);
        }
        
        echo json_encode([
            'success' => true,
            'invoice' => $invoice,
            'items' => $items,
            'addresses' => $addresses
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    } catch (Exception $e) {
        error_log("General error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
