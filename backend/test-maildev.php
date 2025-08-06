<?php
require_once 'includes/loader.php';

use App\EmailService;
use Database\Database;

try {
    // Initialize database and email service
    $database = new Database(
        \EnvLoader::get('DB_HOST'),
        \EnvLoader::get('DB_NAME'),
        \EnvLoader::get('DB_USERNAME'),
        \EnvLoader::get('DB_PASSWORD'),
        (int)\EnvLoader::get('DB_PORT', '3306')
    );
    $emailService = new EmailService($database);
    
    echo "<h2>Testing MailDev Connection</h2>\n";
    echo "<pre>\n";
    
    // Test the connection
    $result = $emailService->testConnection();
    
    echo "Connection Test Result:\n";
    echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: " . $result['message'] . "\n";
    
    if (isset($result['debug'])) {
        echo "\nDebug Output:\n";
        echo $result['debug'] . "\n";
    }
    
    // If connection is successful, try sending a test email
    if ($result['success']) {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Attempting to send test email...\n";
        
        $testEmail = $emailService->sendWelcomeEmail('test@example.com', [
            'user_type' => 'developer',
            'desired_features' => ['quick ordering', 'real-time tracking']
        ]);
        
        echo "Test Email Result:\n";
        echo "Success: " . ($testEmail['success'] ? 'YES' : 'NO') . "\n";
        echo "Message: " . $testEmail['message'] . "\n";
        
        if ($testEmail['success']) {
            echo "\n✅ Email sent successfully! Check MailDev interface at http://localhost:1080\n";
        }
    }
    
    echo "</pre>\n";
    
} catch (Exception $e) {
    echo "<pre>Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "</pre>\n";
}
