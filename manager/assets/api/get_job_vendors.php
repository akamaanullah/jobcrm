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

    $pdo = connectDatabase();
    $jobId = $_GET['job_id'] ?? null;

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Get vendors assigned to this job
    $sql = "
        SELECT 
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
            v.updated_at,
            COALESCE(fr.estimated_amount, 0) as estimated_amount,
            COALESCE(
                CASE 
                    WHEN v.status = 'vendor_payment_accepted' THEN fr.estimated_amount
                    ELSE (
                        SELECT SUM(pp.requested_amount) 
                        FROM partial_payments pp 
                        WHERE pp.vendor_id = v.id AND pp.status = 'approved'
                    )
                END, 
                0
            ) as total_paid
        FROM vendors v
        LEFT JOIN final_request_approvals fr ON fr.job_vendor_id = v.id AND fr.status = 'accepted'
        WHERE v.job_id = :job_id
        ORDER BY v.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->execute();
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add avatar, status badge, and action button for each vendor
    foreach ($vendors as &$vendor) {
        $vendor['avatar'] = getInitials($vendor['vendor_name']);
        $vendor['status_badge'] = getStatusBadge($vendor['status']);
        $vendor['action_button'] = getActionButton($vendor['status'], $vendor['id'], $vendor['vendor_name'], $pdo);

        // Add created_ago
        $vendor['created_ago'] = getTimeAgo($vendor['created_at']);

        // Add quote_display
        if ($vendor['quote_type'] === 'free_quote') {
            $vendor['quote_display'] = 'Free Quote';
        } else {
            $vendor['quote_display'] = '$' . number_format($vendor['quote_amount'], 2);
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $vendors
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Helper function to get initials from name
function getInitials($name)
{
    if (empty($name))
        return 'U';

    $words = explode(' ', trim($name));
    $initials = '';

    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }

    return substr($initials, 0, 2);
}

// Helper function to get time ago
function getTimeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60)
        return 'just now';
    if ($time < 3600)
        return floor($time / 60) . ' minutes ago';
    if ($time < 86400)
        return floor($time / 3600) . ' hours ago';
    if ($time < 2592000)
        return floor($time / 86400) . ' days ago';
    if ($time < 31536000)
        return floor($time / 2592000) . ' months ago';
    return floor($time / 31536000) . ' years ago';
}

// Helper function to get status badge
function getStatusBadge($status)
{
    $badgeMap = [
        'added' => ['text' => 'Added', 'class' => 'bg-secondary'],
        'visit_requested' => ['text' => 'Visit Requested', 'class' => 'bg-warning'],
        'visit_request_rejected' => ['text' => 'Visit Rejected', 'class' => 'bg-danger'],
        'final_visit_requested' => ['text' => 'Final Visit Requested', 'class' => 'bg-info'],
        'final_visit_request_rejected' => ['text' => 'Final Visit Rejected', 'class' => 'bg-danger'],
        'job_completed' => ['text' => 'Job Completed', 'class' => 'bg-success'],
        'requested_vendor_payment' => ['text' => 'Payment Requested', 'class' => 'bg-primary'],
        'payment_request_rejected' => ['text' => 'Payment Rejected', 'class' => 'bg-danger'],
        'request_visit_accepted' => ['text' => 'Visit Accepted', 'class' => 'bg-success'],
        'final_visit_request_accepted' => ['text' => 'Final Visit Accepted', 'class' => 'bg-success'],
        'partial_payment_requested' => ['text' => 'Partial Payment Requested', 'class' => 'bg-warning'],
        'partial_payment_accepted' => ['text' => 'Partial Payment Accepted', 'class' => 'bg-success'],
        'partial_payment_rejected' => ['text' => 'Partial Payment Rejected', 'class' => 'bg-danger'],
        'vendor_payment_accepted' => ['text' => 'Payment Accepted', 'class' => 'bg-success']
    ];

    $badge = $badgeMap[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-secondary'];

    return [
        'text' => $badge['text'],
        'class' => $badge['class']
    ];
}

// Helper function to get action button
function getActionButton($status, $vendorId, $vendorName, $pdo = null)
{
    $buttonMap = [
        'added' => [
            'show' => true,
            'action' => 'request_visit',
            'text' => 'Request Visit',
            'class' => 'btn-modern-primary',
            'icon' => 'bi-calendar-plus'
        ],
        'visit_requested' => [
            'show' => false,
            'action' => '',
            'text' => '',
            'class' => ''
        ],
        'visit_request_rejected' => [
            'show' => true,
            'action' => 'request_visit',
            'text' => 'Request Visit Again',
            'class' => 'btn-modern-primary',
            'icon' => 'bi-arrow-clockwise'
        ],
        'final_visit_requested' => [
            'show' => false,
            'action' => '',
            'text' => '',
            'class' => ''
        ],
        'final_visit_request_rejected' => [
            'show' => true,
            'action' => 'request_final_visit',
            'text' => 'Request Final Visit Again',
            'class' => 'btn-modern-info',
            'icon' => 'bi-arrow-clockwise'
        ],
        'job_completed' => [
            'show' => true,
            'action' => 'request_payment',
            'text' => 'Request Payment',
            'class' => 'btn-modern-success',
            'icon' => 'bi-currency-dollar'
        ],
        'requested_vendor_payment' => [
            'show' => false,
            'action' => '',
            'text' => '',
            'class' => ''
        ],
        'payment_request_rejected' => [
            'show' => true,
            'action' => 'request_payment',
            'text' => 'Request Payment Again',
            'class' => 'btn-modern-success',
            'icon' => 'bi-arrow-clockwise'
        ],
        'request_visit_accepted' => [
            'show' => true,
            'action' => 'request_final_visit',
            'text' => 'Request Final Visit',
            'class' => 'btn-modern-info',
            'icon' => 'bi-check-circle'
        ],
        'final_visit_request_accepted' => [
            'show' => true,
            'action' => 'request_partial_payment',
            'text' => 'Partial Payment',
            'class' => 'btn-modern-payment',
            'icon' => 'bi-cash-stack',
            'secondary' => [
                'action' => 'complete_job',
                'text' => 'Complete Job',
                'class' => 'btn-modern-warning',
                'icon' => 'bi-check2-all'
            ]
        ],
        'partial_payment_requested' => [
            'show' => true,
            'action' => 'complete_job',
            'text' => 'Complete Job',
            'class' => 'btn-modern-warning',
            'icon' => 'bi-check2-all'
        ],
        'partial_payment_accepted' => [
            'show' => true,
            'action' => 'complete_job',
            'text' => 'Complete Job',
            'class' => 'btn-modern-warning',
            'icon' => 'bi-check2-all'
        ],
        'partial_payment_rejected' => [
            'show' => true,
            'action' => 'request_partial_payment',
            'text' => 'Partial Payment',
            'class' => 'btn-modern-payment',
            'icon' => 'bi-cash-stack',
            'secondary' => [
                'action' => 'complete_job',
                'text' => 'Complete Job',
                'class' => 'btn-modern-warning',
                'icon' => 'bi-check2-all'
            ]
        ],
        'vendor_payment_accepted' => [
            'show' => false,
            'action' => '',
            'text' => '',
            'class' => ''
        ]
    ];

    $button = $buttonMap[$status] ?? [
        'show' => false,
        'action' => '',
        'text' => '',
        'class' => ''
    ];

    $result = [
        'show' => $button['show'],
        'action' => $button['action'],
        'text' => $button['text'],
        'class' => $button['class'],
        'vendor_id' => $vendorId,
        'vendor_name' => $vendorName
    ];

    // Add secondary button if exists
    if (isset($button['secondary'])) {
        $result['secondary'] = $button['secondary'];
    }

    // Special logic for partial payment buttons
    if ($status === 'final_visit_request_accepted' && $pdo) {
        // Check if any partial payments exist for this vendor
        $partialPaymentQuery = "
            SELECT COUNT(*) as count, COALESCE(SUM(requested_amount), 0) as total_paid
            FROM partial_payments 
            WHERE vendor_id = :vendor_id AND status = 'approved'
        ";
        $partialStmt = $pdo->prepare($partialPaymentQuery);
        $partialStmt->bindParam(':vendor_id', $vendorId, PDO::PARAM_INT);
        $partialStmt->execute();
        $partialData = $partialStmt->fetch(PDO::FETCH_ASSOC);

        if ($partialData['count'] > 0) {
            // Partial payments exist - change button text
            $result['text'] = 'More Payment';
            $result['payment_info'] = [
                'partial_payments_count' => $partialData['count'],
                'total_paid' => $partialData['total_paid']
            ];
        }
    }

    return $result;
}
?>