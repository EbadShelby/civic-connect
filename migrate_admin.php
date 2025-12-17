<?php
require_once __DIR__ . '/backend/config/database.php';

try {
    // Add role column
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('citizen', 'staff', 'admin') DEFAULT 'citizen'");
        echo "Added role column.\n";
    } catch (PDOException $e) {
        echo "Role column might already exist: " . $e->getMessage() . "\n";
    }

    // Add last_login column
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL");
        echo "Added last_login column.\n";
    } catch (PDOException $e) {
        echo "Last_login column might already exist: " . $e->getMessage() . "\n";
    }

    // Add index
    try {
        $pdo->exec("ALTER TABLE users ADD INDEX idx_role (role)");
        echo "Added idx_role index.\n";
    } catch (PDOException $e) {
        echo "Index might already exist: " . $e->getMessage() . "\n";
    }

    // Update first user to admin for testing (if exists)
    $stmt = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
    $user = $stmt->fetch();
    if ($user) {
        $pdo->exec("UPDATE users SET role = 'admin' WHERE id = " . $user['id']);
        echo "Updated user " . $user['id'] . " to admin.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
