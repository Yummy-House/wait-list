<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../includes/loader.php';
require_once __DIR__ . '/../src/EmailService.php';

use App\EmailService;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit();
    }

    // Initialize email service
    $emailService = new EmailService($db);
    $emailService->createEmailLogTable();

    $action = $data['action'] ?? '';

    switch ($action) {
        case 'test_welcome':
            if (empty($data['email'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Email address is required'
                ]);
                exit();
            }

            // Send a test welcome email
            $testData = [
                'user_type' => $data['user_type'] ?? 'food lover',
                'desired_features' => $data['desired_features'] ?? ['Online ordering', 'Real-time tracking']
            ];
            
            $result = $emailService->sendWelcomeEmail($data['email'], $testData);
            
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
            echo json_encode($result);
            break;

        case 'test_connection':
            // Test SMTP connection
            $result = $emailService->testConnection();
            
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
            echo json_encode($result);
            break;

        case 'send_bulk':
            if (empty($data['subject']) || empty($data['message'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Subject and message are required for bulk email'
                ]);
                exit();
            }

            $filters = $data['filters'] ?? [];
            $result = $emailService->sendBulkEmail($data['subject'], $data['message'], $filters);
            
            http_response_code(200);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: test_welcome, test_connection, send_bulk'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Error in email test API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
