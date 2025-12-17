<?php
/**
 * Test if mailer works when called through Apache
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/mailer.php';

try {
    error_log("=== MAILER TEST START ===");
    
    $mail = mailer();
    
    error_log("Mailer object created successfully");
    error_log("SMTP Host: " . $mail->Host);
    error_log("SMTP Port: " . $mail->Port);
    error_log("SMTP User: " . $mail->Username);
    error_log("From Address: " . ($_ENV['SMTP_FROM'] ?? 'NOT SET'));
    
    $mail->addAddress('anothershelby7@gmail.com', 'Test User');
    $mail->Subject = 'Test Email from Apache';
    $mail->Body = '<h1>Test Email</h1><p>This is a test email sent through Apache.</p>';
    
    error_log("About to send email...");
    $result = $mail->send();
    error_log("Send result: " . ($result ? 'true' : 'false'));
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Email sent successfully'
        ]);
    } else {
        error_log("Mail Error Info: " . $mail->ErrorInfo);
        echo json_encode([
            'success' => false,
            'message' => 'Email send returned false',
            'error' => $mail->ErrorInfo
        ]);
    }
    
    error_log("=== MAILER TEST END ===");
} catch (Exception $e) {
    error_log("EXCEPTION: " . $e->getMessage());
    error_log("TRACE: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Exception occurred',
        'error' => $e->getMessage()
    ]);
}
