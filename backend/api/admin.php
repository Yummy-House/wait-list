<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../includes/loader.php';
require_once __DIR__ . '/../src/Waitlist.php';

use App\Waitlist;

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Initialize waitlist service
    $waitlist = new Waitlist($db);

    $action = $_GET['action'] ?? 'stats';

    switch ($action) {
        case 'stats':
            $stats = $waitlist->getStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;

        case 'entries':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $entries = $waitlist->getAllEntries($limit, $offset);
            echo json_encode([
                'success' => true,
                'data' => $entries,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]);
            break;

        case 'export':
            $csv = $waitlist->exportToCSV();
            if ($csv) {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="waitlist_export_' . date('Y-m-d_H-i-s') . '.csv"');
                echo $csv;
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to export data'
                ]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Error in admin API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
