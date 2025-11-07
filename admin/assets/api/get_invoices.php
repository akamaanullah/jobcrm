<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    $pdo = getDB();
    
    // Get query parameters
    $search = $_GET['search'] ?? '';
    $company = $_GET['company'] ?? '';
    $sort = $_GET['sort'] ?? 'created_at';
    $order = $_GET['order'] ?? 'DESC';
    
    // Build query
    $sql = "SELECT i.id, i.company_name, i.date, i.invoice_number, i.invoice_to, i.total_amount, i.created_at,
                   (SELECT COUNT(*) FROM invoices_items ii WHERE ii.invoice_number = i.invoice_number) as items_count,
                   (SELECT COUNT(*) FROM invoice_address ia WHERE ia.invc_id = i.id) as addresses_count
            FROM invoices i 
            WHERE 1=1";
    $params = [];
    
    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (i.invoice_number LIKE :search OR i.invoice_to LIKE :search OR i.company_name LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Add company filter
    if (!empty($company)) {
        $sql .= " AND i.company_name = :company";
        $params[':company'] = $company;
    }
    
    // Add sorting
    $allowedSorts = ['invoice_number', 'company_name', 'invoice_to', 'date', 'total_amount', 'created_at'];
    if (in_array($sort, $allowedSorts)) {
        $sql .= " ORDER BY i.$sort";
        if (strtoupper($order) === 'ASC' || strtoupper($order) === 'DESC') {
            $sql .= " $order";
        }
    } else {
        $sql .= " ORDER BY i.created_at DESC";
    }
    
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get invoice statistics
    $stats = [
        'total_invoices' => 0,
        'total_amount' => 0,
        'companies' => []
    ];
    
    // Count total invoices
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM invoices");
    $countStmt->execute();
    $stats['total_invoices'] = $countStmt->fetch()['total'];
    
    // Calculate total amount
    $amountStmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM invoices");
    $amountStmt->execute();
    $stats['total_amount'] = $amountStmt->fetch()['total'] ?? 0;
    
    // Get unique companies
    $companyStmt = $pdo->prepare("SELECT DISTINCT company_name FROM invoices ORDER BY company_name");
    $companyStmt->execute();
    $stats['companies'] = $companyStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Format invoice data
    foreach ($invoices as &$invoice) {
        // Format date
        $invoice['formatted_date'] = date('M d, Y', strtotime($invoice['date']));
        $invoice['formatted_created'] = date('M d, Y H:i', strtotime($invoice['created_at']));
        
        // Format amount
        $invoice['formatted_amount'] = '$' . number_format($invoice['total_amount'], 2);
        
        // Add status (for future use)
        $invoice['status'] = 'active';
    }
    
    echo json_encode([
        'success' => true,
        'invoices' => $invoices,
        'stats' => $stats,
        'total' => count($invoices)
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
