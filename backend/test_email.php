<?php
require_once __DIR__ . '/config/mailer.php';

try {
    $mail = mailer();
    $mail->addAddress('joephp6@gmail.com', 'Test User');
    $mail->Subject = 'Test Email - Civic Connect';
    $mail->Body = '<h1>Test Email</h1><p>If you receive this, your email configuration is working!</p>';
    
    if ($mail->send()) {
        echo "Email sent successfully!\n";
    } else {
        echo "Failed to send email\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
