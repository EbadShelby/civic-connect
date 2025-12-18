<?php
/**
 * Run notifications table migration
 */

require_once __DIR__ . '/backend/config/database.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        issue_id INT,
        type ENUM('status_change', 'upvote', 'comment', 'system') DEFAULT 'status_change',
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        old_status VARCHAR(50),
        new_status VARCHAR(50),
        is_read BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_is_read (is_read),
        INDEX idx_created_at (created_at),
        INDEX idx_type (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "✓ Notifications table created successfully!\n";
    
    // Verify table was created
    $result = $pdo->query("SHOW TABLES LIKE 'notifications'");
    if ($result->rowCount() > 0) {
        echo "✓ Notifications table verified\n";
        
        // Show table structure
        $result = $pdo->query("DESCRIBE notifications");
        echo "\nTable Structure:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-20s %-20s %-10s %-10s\n", "Field", "Type", "Null", "Key");
        echo str_repeat("-", 80) . "\n";
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            printf("%-20s %-20s %-10s %-10s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key']
            );
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
