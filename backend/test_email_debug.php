<?php
require_once __DIR__ . '/config/mailer.php';

echo "Testing email to anothershelby7@gmail.com...\n\n";

try {
    $mail = mailer();
    
    // Enable verbose debug output
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo "Debug level $level: $str\n";
    };
    
    $mail->addAddress('anothershelby7@gmail.com', 'Test User');
    $mail->Subject = 'OTP Verification Test - Civic Connect';
    $mail->Body = '
        <html>
        <body style="font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
                <h2>Welcome to Civic Connect!</h2>
                <p>Thank you for registering. Please verify your email address using the OTP code below:</p>
                <div style="background-color: #f0f0f0; padding: 20px; border-radius: 4px; text-align: center; margin: 20px 0;">
                    <h3 style="font-size: 24px; letter-spacing: 2px; margin: 0;">371423</h3>
                </div>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn\'t create this account, please ignore this email.</p>
                <hr>
                <p style="color: #777; font-size: 12px;">Civic Connect - Building Better Communities</p>
            </div>
        </body>
        </html>
    ';
    
    echo "\n--- Attempting to send email ---\n";
    if ($mail->send()) {
        echo "\n✅ SUCCESS: Email sent successfully!\n";
    } else {
        echo "\n❌ FAILED: Could not send email\n";
        echo "Error info: " . $mail->ErrorInfo . "\n";
    }
} catch (Exception $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Error info: " . $mail->ErrorInfo . "\n";
}
