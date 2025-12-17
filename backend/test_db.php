# Create a test file
cat > /var/www/html/civic-connect/backend/test_db.php << 'EOF'
<?php
require_once __DIR__ . '/config/bootstrap.php';

try {
    $host = $_ENV['DB_HOST'];
    $db = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection successful";

    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<br>✅ Users table accessible, count: " . $result['count'];
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
EOF
?>
