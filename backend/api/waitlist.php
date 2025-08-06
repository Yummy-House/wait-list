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
require_once __DIR__ . '/../src/Waitlist.php';
require_once __DIR__ . '/../src/EmailService.php';

use App\Waitlist;
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

    // Initialize waitlist service
    $waitlist = new Waitlist($db);
    $emailService = new EmailService($db);
    
    // Create tables if they don't exist
    $waitlist->createTable();
    $emailService->createEmailLogTable();

    // Process the survey data
    $waitlistData = [
        'email' => $data['email'] ?? '',
    ];

    // Map survey responses
    if (isset($data['survey'])) {
        $survey = $data['survey'];
        
        // Question 1: How did you hear about us?
        if (isset($survey['1'])) {
            $waitlistData['how_heard'] = $survey['1'];
        }
        
        // Question 2: Are you a restaurant owner or a food lover?
        if (isset($survey['2'])) {
            $waitlistData['user_type'] = $survey['2'];
        }
        
        // Question 3: What features would you like to see in our app?
        if (isset($survey['3'])) {
            $features = is_array($survey['3']) ? $survey['3'] : [$survey['3']];
            $waitlistData['desired_features'] = $features;
        }
        
        // Question 4: How often do you order food online?
        if (isset($survey['4'])) {
            $waitlistData['ordering_frequency'] = $survey['4'];
        }
        
        // Handle "Other" responses and additional feedback
        if (isset($survey['other_feedback'])) {
            $waitlistData['other_feedback'] = $survey['other_feedback'];
        }
    }

    // Add to waitlist
    $result = $waitlist->addToWaitlist($waitlistData);

    if ($result['success']) {
        // Send welcome email after successful registration
        $emailResult = $emailService->sendWelcomeEmail($waitlistData['email'], $waitlistData);
        
        // Log email result but don't fail the registration if email fails
        if (!$emailResult['success']) {
            error_log("Failed to send welcome email to {$waitlistData['email']}: " . $emailResult['message']);
        }
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for joining our waitlist! We\'ll notify you when we launch.',
            'data' => [
                'id' => $result['id'] ?? null,
                'email_sent' => $emailResult['success']
            ]
        ]);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }

} catch (Exception $e) {
    error_log("Error in waitlist API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
