<?php

namespace App;

use Database\Database;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    private PDO $connection;
    private array $config;
    private PHPMailer $mailer;

    public function __construct(Database $database)
    {
        $this->connection = $database->getConnection();
        $this->loadConfig();
        $this->initializeMailer();
    }

    /**
     * Load email configuration
     */
    private function loadConfig(): void
    {
        $this->config = [
            'app_name' => \EnvLoader::get('APP_NAME', 'YummyHouse'),
            'app_url' => \EnvLoader::get('APP_URL', 'https://yummyhouse.com'),
            'support_email' => \EnvLoader::get('SUPPORT_EMAIL', 'support@yummyhouse.com'),
            'unsubscribe_url' => \EnvLoader::get('UNSUBSCRIBE_URL', 'https://yummyhouse.com/unsubscribe'),
            'from_email' => \EnvLoader::get('MAIL_FROM_ADDRESS', 'noreply@yummyhouse.com'),
            'from_name' => \EnvLoader::get('MAIL_FROM_NAME', 'YummyHouse')
        ];
    }

    /**
     * Initialize PHPMailer with SMTP configuration
     */
    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = \EnvLoader::get('SMTP_HOST', 'localhost');
            $this->mailer->SMTPAuth = \EnvLoader::get('SMTP_AUTH', 'false') === 'true'; // MailDev doesn't require auth by default
            $this->mailer->Username = \EnvLoader::get('SMTP_USERNAME', '');
            $this->mailer->Password = \EnvLoader::get('SMTP_PASSWORD', '');
            
            // MailDev configuration
            $smtpEncryption = \EnvLoader::get('SMTP_ENCRYPTION', 'none');
            if ($smtpEncryption === 'none' || $smtpEncryption === 'false') {
                $this->mailer->SMTPSecure = false;
            } else {
                $this->mailer->SMTPSecure = $smtpEncryption;
            }
            
            $this->mailer->Port = (int)\EnvLoader::get('SMTP_PORT', '1025'); // MailDev default port
            
            // Disable SSL/TLS verification for MailDev (local development)
            if (\EnvLoader::get('APP_ENV', 'production') === 'development') {
                $this->mailer->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
            
            // Default from address
            $this->mailer->setFrom(
                $this->config['from_email'],
                $this->config['from_name']
            );
            
            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            // Enable debug output if in development
            if (\EnvLoader::get('APP_ENV', 'production') === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF; // Change to DEBUG_SERVER for more verbose output
            }
            
        } catch (PHPMailerException $e) {
            error_log("Error initializing PHPMailer: " . $e->getMessage());
        }
    }

    /**
     * Create email log table if it doesn't exist
     */
    public function createEmailLogTable(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS email_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            recipient_email VARCHAR(255) NOT NULL,
            subject VARCHAR(500) NOT NULL,
            template_name VARCHAR(100) DEFAULT NULL,
            status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
            error_message TEXT DEFAULT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_recipient_email (recipient_email),
            INDEX idx_status (status),
            INDEX idx_sent_at (sent_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->connection->exec($sql);
            return true;
        } catch (\PDOException $e) {
            error_log("Error creating email_logs table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email to new waitlist subscriber
     */
    public function sendWelcomeEmail(string $email, array $userData = []): array
    {
        try {
            $subject = "Welcome to {$this->config['app_name']} Waitlist! 🎉";
            $template = $this->getWelcomeEmailTemplate($userData);
            
            return $this->sendEmail($email, $subject, $template, 'welcome');
        } catch (\Exception $e) {
            error_log("Error sending welcome email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send welcome email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test SMTP connection
     */
    public function testConnection(): array
    {
        try {
            // Create a new PHPMailer instance for testing
            $testMailer = new PHPMailer(true);
            
            // Configure with MailDev settings
            $testMailer->isSMTP();
            $testMailer->Host = \EnvLoader::get('SMTP_HOST', 'localhost'); // Changed from 0.0.0.0 to localhost
            $testMailer->SMTPAuth = \EnvLoader::get('SMTP_AUTH', 'false') === 'true';
            $testMailer->Username = \EnvLoader::get('SMTP_USERNAME', '');
            $testMailer->Password = \EnvLoader::get('SMTP_PASSWORD', '');
            $testMailer->SMTPSecure = false; // MailDev doesn't use encryption by default
            $testMailer->Port = (int)\EnvLoader::get('SMTP_PORT', '1025'); // MailDev default port
            
            // Disable SSL/TLS verification for MailDev
            $testMailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Enable debug output for testing
            $testMailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
            
            // Start output buffering to capture debug output
            ob_start();
            
            // Test the connection
            $connected = $testMailer->smtpConnect();
            
            // Get debug output
            $debugOutput = ob_get_clean();
            
            if ($connected) {
                $testMailer->smtpClose();
                return [
                    'success' => true,
                    'message' => 'SMTP connection successful',
                    'debug' => $debugOutput
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'SMTP connection failed',
                    'debug' => $debugOutput
                ];
            }
            
        } catch (PHPMailerException $e) {
            return [
                'success' => false,
                'message' => 'SMTP test failed: ' . $e->getMessage(),
                'debug' => isset($debugOutput) ? $debugOutput : 'No debug output available'
            ];
        }
    }

    /**
     * Send bulk email to multiple recipients
     */
    public function sendBulkEmail(string $subject, string $message, array $filters = []): array
    {
        try {
            $recipients = $this->getWaitlistEmails($filters);
            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            foreach ($recipients as $recipient) {
                $personalizedMessage = $this->getBulkEmailTemplate($message, $recipient);
                $result = $this->sendEmail($recipient['email'], $subject, $personalizedMessage, 'bulk');
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                    $errors[] = [
                        'email' => $recipient['email'],
                        'error' => $result['message']
                    ];
                }
                
                // Add delay to prevent overwhelming SMTP server
                usleep(200000); // 0.2 second delay
            }

            return [
                'success' => true,
                'message' => "Bulk email completed. Sent: {$successCount}, Failed: {$failureCount}",
                'stats' => [
                    'total_recipients' => count($recipients),
                    'sent' => $successCount,
                    'failed' => $failureCount,
                    'errors' => $errors
                ]
            ];
        } catch (\Exception $e) {
            error_log("Error sending bulk email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send bulk email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send individual email using PHPMailer
     */
    private function sendEmail(string $email, string $subject, string $htmlContent, string | null $templateName): array
    {
        try {
            // Clear any previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearReplyTos();
            
            // Set recipient
            $this->mailer->addAddress($email);
            
            // Set reply-to address
            $this->mailer->addReplyTo($this->config['support_email'], $this->config['from_name']);
            
            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlContent;
            $this->mailer->AltBody = strip_tags($htmlContent); // Plain text version
            
            // Send email
            $success = $this->mailer->send();
            
            // Log email
            $this->logEmail($email, $subject, $templateName, $success ? 'sent' : 'failed', 
                          $success ? null : 'PHPMailer send failed');
            
            return [
                'success' => $success,
                'message' => $success ? 'Email sent successfully' : 'Failed to send email'
            ];
            
        } catch (PHPMailerException $e) {
            $errorMessage = 'PHPMailer Error: ' . $e->getMessage();
            error_log($errorMessage);
            $this->logEmail($email, $subject, $templateName, 'failed', $errorMessage);
            
            return [
                'success' => false,
                'message' => 'Email error: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            $errorMessage = 'General Error: ' . $e->getMessage();
            error_log($errorMessage);
            $this->logEmail($email, $subject, $templateName, 'failed', $errorMessage);
            
            return [
                'success' => false,
                'message' => 'Email error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log email sending attempt
     */
    private function logEmail(string $email, string $subject, ?string $templateName, string $status, ?string $errorMessage = null): void
    {
        try {
            $sql = "INSERT INTO email_logs (recipient_email, subject, template_name, status, error_message) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$email, $subject, $templateName, $status, $errorMessage]);
        } catch (\PDOException $e) {
            error_log("Error logging email: " . $e->getMessage());
        }
    }

    /**
     * Get waitlist emails with optional filters
     */
    private function getWaitlistEmails(array $filters = []): array
    {
        try {
            $sql = "SELECT email, how_heard, user_type, desired_features, ordering_frequency, created_at FROM waitlist WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($filters['user_type'])) {
                $sql .= " AND user_type = ?";
                $params[] = $filters['user_type'];
            }

            if (!empty($filters['how_heard'])) {
                $sql .= " AND how_heard = ?";
                $params[] = $filters['how_heard'];
            }

            if (!empty($filters['ordering_frequency'])) {
                $sql .= " AND ordering_frequency = ?";
                $params[] = $filters['ordering_frequency'];
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in getWaitlistEmails: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get bulk email template
     */
    private function getBulkEmailTemplate(string $message, array $userData): string
    {
        $personalizedMessage = str_replace('[USER_TYPE]', $userData['user_type'] ?? 'valued subscriber', $message);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$this->config['app_name']} Update</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b6b, #ffa726); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 10px; margin: 20px 0; }
                .footer { text-align: center; color: #666; font-size: 12px; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$this->config['app_name']}</h1>
                <p>Update from the team</p>
            </div>
            
            <div class='content'>
                {$personalizedMessage}
            </div>
            
            <div class='footer'>
                <p>© 2025 {$this->config['app_name']}. All rights reserved.</p>
                <p>If you no longer wish to receive these emails, <a href='{$this->config['unsubscribe_url']}'>unsubscribe here</a></p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get welcome email template
     */
    private function getWelcomeEmailTemplate(array $userData): string
    {
        $userType = $userData['user_type'] ?? 'food lover';
        $features = '';
        
        if (!empty($userData['desired_features'])) {
            $featuresList = is_array($userData['desired_features']) ? $userData['desired_features'] : json_decode($userData['desired_features'], true);
            if (is_array($featuresList)) {
                $features = '<li>' . implode('</li><li>', $featuresList) . '</li>';
            }
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to {$this->config['app_name']}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b6b, #ffa726); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 10px; margin: 20px 0; }
                .features { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; color: #666; font-size: 12px; margin-top: 30px; }
                .btn { display: inline-block; background: #ff6b6b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🎉 Welcome to {$this->config['app_name']}!</h1>
                <p>Thank you for joining our waitlist</p>
            </div>
            
            <div class='content'>
                <h2>Hello " . ucfirst($userType) . "! 👋</h2>
                <p>We're thrilled to have you on our waitlist! You're now part of an exclusive group that will be the first to experience {$this->config['app_name']} when we launch.</p>
                
                " . (!empty($features) ? "
                <div class='features'>
                    <h3>🍽️ Features you're excited about:</h3>
                    <ul>{$features}</ul>
                </div>
                " : "") . "
                
                <h3>What happens next?</h3>
                <ul>
                    <li>📧 We'll send you exclusive updates about our progress</li>
                    <li>🎁 You'll get early access when we launch</li>
                    <li>💝 Special launch offers and promotions</li>
                    <li>🗣️ Opportunity to provide feedback and shape our product</li>
                </ul>
                
                <p>We're working hard to bring you the best food ordering experience. Stay tuned for exciting updates!</p>
                
                <a href='{$this->config['app_url']}' class='btn'>Visit Our Website</a>
            </div>
            
            <div class='footer'>
                <p>© 2025 {$this->config['app_name']}. All rights reserved.</p>
                <p>If you no longer wish to receive these emails, <a href='{$this->config['unsubscribe_url']}'>unsubscribe here</a></p>
            </div>
        </body>
        </html>";
    }
}
