<?php
// Test the actual registration endpoint
$url = 'http://localhost/civic-connect/backend/api/index.php/users/register';

$data = [
    'email' => 'test-registration@gmail.com',
    'password' => 'TestPass123',
    'first_name' => 'Test',
    'last_name' => 'User'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

echo "Testing registration endpoint...\n";
echo "URL: $url\n";
echo "Data: " . json_encode($data) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

curl_close($ch);

// Check if user was created
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->prepare("SELECT email, otp_code, otp_expires_at FROM users WHERE email = ?");
$stmt->execute(['test-registration@gmail.com']);
$user = $stmt->fetch();

echo "\nUser in database:\n";
print_r($user);
