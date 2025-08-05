<?php

namespace App;

use Database\Database;
use PDO;

class Waitlist
{
    private PDO $connection;

    public function __construct(private Database $database)
    {
        $this->connection = $this->database->getConnection();
    }

    /**
     * Create the waitlist table if it doesn't exist
     */
    public function createTable(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS waitlist (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL UNIQUE,
            how_heard VARCHAR(100) DEFAULT NULL,
            user_type VARCHAR(50) DEFAULT NULL,
            desired_features JSON DEFAULT NULL,
            ordering_frequency VARCHAR(50) DEFAULT NULL,
            other_feedback TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->connection->exec($sql);
            return true;
        } catch (\PDOException $e) {
            error_log("Error creating waitlist table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a new waitlist entry
     */
    public function addToWaitlist(array $data): array
    {
        try {
            // Validate required email field
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Valid email address is required'
                ];
            }

            // Prepare survey data
            $desiredFeatures = isset($data['desired_features']) ? json_encode($data['desired_features']) : null;


            // Check if email already exists
            if ($this->emailExists($data['email'])) {
                $sql = "UPDATE waitlist SET 
                            how_heard = ?, 
                            user_type = ?, 
                            desired_features = ?, 
                            ordering_frequency = ?, 
                            other_feedback = ?, 
                            updated_at = CURRENT_TIMESTAMP 
                            WHERE email = ?";

                $stmt = $this->connection->prepare($sql);
                $result = $stmt->execute([
                    $data['how_heard'] ?? null,
                    $data['user_type'] ?? null,
                    $desiredFeatures,
                    $data['ordering_frequency'] ?? null,
                    $data['other_feedback'] ?? null,
                    $data['email']
                ]);

                if ($result) {
                    return [
                        'success' => true,
                        'message' => 'Waitlist entry updated successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to update waitlist entry'
                    ];
                }
            }

            $sql = "INSERT INTO waitlist (
                email, 
                how_heard, 
                user_type, 
                desired_features, 
                ordering_frequency, 
                other_feedback
            ) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([
                $data['email'],
                $data['how_heard'] ?? null,
                $data['user_type'] ?? null,
                $desiredFeatures,
                $data['ordering_frequency'] ?? null,
                $data['other_feedback'] ?? null
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Successfully added to waitlist',
                    'id' => $this->connection->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to add to waitlist'
                ];
            }
        } catch (\PDOException $e) {
            error_log("Database error in addToWaitlist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error occurred'
            ];
        }
    }

    /**
     * Check if email already exists in waitlist
     */
    private function emailExists(string $email): bool
    {
        $sql = "SELECT id FROM waitlist WHERE email = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get waitlist statistics
     */
    public function getStats(): array
    {
        try {
            $stats = [];

            // Total count
            $sql = "SELECT COUNT(*) as total FROM waitlist";
            $stmt = $this->connection->query($sql);
            $stats['total'] = $stmt->fetchColumn();

            // Count by how they heard about us
            $sql = "SELECT how_heard, COUNT(*) as count FROM waitlist WHERE how_heard IS NOT NULL GROUP BY how_heard";
            $stmt = $this->connection->query($sql);
            $stats['sources'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Count by user type
            $sql = "SELECT user_type, COUNT(*) as count FROM waitlist WHERE user_type IS NOT NULL GROUP BY user_type";
            $stmt = $this->connection->query($sql);
            $stats['user_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Count by ordering frequency
            $sql = "SELECT ordering_frequency, COUNT(*) as count FROM waitlist WHERE ordering_frequency IS NOT NULL GROUP BY ordering_frequency";
            $stmt = $this->connection->query($sql);
            $stats['ordering_frequency'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;
        } catch (\PDOException $e) {
            error_log("Database error in getStats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all waitlist entries (for admin use)
     */
    public function getAllEntries(int $limit = 100, int $offset = 0): array
    {
        try {
            $sql = "SELECT * FROM waitlist ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->bindParam(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in getAllEntries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export waitlist data as CSV
     */
    public function exportToCSV(): string
    {
        try {
            $sql = "SELECT 
                email,
                how_heard,
                user_type,
                desired_features,
                ordering_frequency,
                other_feedback,
                created_at
                FROM waitlist ORDER BY created_at DESC";

            $stmt = $this->connection->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $output = "Email,How Heard,User Type,Desired Features,Ordering Frequency,Other Feedback,Created At\n";

            foreach ($data as $row) {
                $features = json_decode($row['desired_features'], true);
                $featuresStr = is_array($features) ? implode('; ', $features) : '';

                $output .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $row['email'],
                    $row['how_heard'] ?? '',
                    $row['user_type'] ?? '',
                    $featuresStr,
                    $row['ordering_frequency'] ?? '',
                    $row['other_feedback'] ?? '',
                    $row['created_at']
                );
            }

            return $output;
        } catch (\PDOException $e) {
            error_log("Database error in exportToCSV: " . $e->getMessage());
            return '';
        }
    }
}
