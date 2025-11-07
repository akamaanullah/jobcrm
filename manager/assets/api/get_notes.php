<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

// Start session to get user info
session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    $userId = $_SESSION['user_id'];
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'newest';
    $statsOnly = isset($_GET['stats_only']) && $_GET['stats_only'] === 'true';

    $pdo = getDB();

    // If only stats are requested, return early
    if ($statsOnly) {
        $statsQuery = "
            SELECT 
                COUNT(*) as total_notes,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_notes,
                COUNT(CASE WHEN is_pinned = 1 THEN 1 END) as favorite_notes
            FROM notes 
            WHERE user_id = :user_id
        ";
        
        $statsStmt = $pdo->prepare($statsQuery);
        $statsStmt->execute([':user_id' => $userId]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        exit;
    }

    // Build base query
    $baseQuery = "
        SELECT 
            n.id,
            n.title,
            n.content,
            n.color,
            n.is_pinned,
            n.created_at,
            n.updated_at,
            TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) as minutes_ago,
            TIMESTAMPDIFF(HOUR, n.created_at, NOW()) as hours_ago,
            TIMESTAMPDIFF(DAY, n.created_at, NOW()) as days_ago
        FROM notes n
        WHERE n.user_id = :user_id
    ";

    $params = [':user_id' => $userId];

    // Apply search filter
    if (!empty($search)) {
        $baseQuery .= " AND (n.title LIKE :search OR n.content LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    // Apply sorting - pinned notes always come first
    switch ($sort) {
        case 'oldest':
            $baseQuery .= " ORDER BY n.is_pinned DESC, n.created_at ASC";
            break;
        case 'title':
            $baseQuery .= " ORDER BY n.is_pinned DESC, n.title ASC";
            break;
        case 'updated':
            $baseQuery .= " ORDER BY n.is_pinned DESC, n.updated_at DESC";
            break;
        case 'newest':
        default:
            $baseQuery .= " ORDER BY n.is_pinned DESC, n.created_at DESC";
            break;
    }

    // Execute query
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total_notes,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_notes,
            COUNT(CASE WHEN is_pinned = 1 THEN 1 END) as favorite_notes
        FROM notes 
        WHERE user_id = :user_id
    ";

    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute([':user_id' => $userId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Format notes data
    $formattedNotes = [];
    foreach ($notes as $note) {
        $formattedNotes[] = [
            'id' => $note['id'],
            'title' => $note['title'],
            'content' => $note['content'],
            'color' => $note['color'],
            'is_pinned' => (bool) $note['is_pinned'],
            'created_at' => $note['created_at'],
            'updated_at' => $note['updated_at'],
            'created_ago' => getTimeAgo($note['created_at']),
            'updated_ago' => getTimeAgo($note['updated_at'])
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $formattedNotes,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}
?>
