<?php
/**
 * Test registration email flow
 */
require_once __DIR__ . '/config/mailer.php';
require_once __DIR__ . '/api/helpers.php';

echo "Testing registration email flow...\n\n";

$email = 'anothershelby7@gmail.com';
$first_name = 'Test';
$otp_code = generateOTP(6);

echo "Generated OTP: $otp_code\n";
echo "Sending to: $email\n\n";

function getVerificationEmailHTML($first_name, $otp_code) {
    return "
        <html>
        <body style='font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;'>
                <h2>Welcome to Civic Connect, {$first_name}!</h2>
                <p>Thank you for registering. Please verify your email address using the OTP code below:</p>
                <div style='background-color: #f0f0f0; padding: 20px; border-radius: 4px; text-align: center; margin: 20px 0;'>
                    <h3 style='font-size: 24px; letter-spacing: 2px; margin: 0;'>{$otp_code}</h3>
                </div>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't create this account, please ignore this email.</p>
                <hr>
                <p style='color: #777; font-size: 12px;'>Civic Connect - Building Better Communities</p>
            </div>
        </body>
        </html>
    ";
}

try {
    $mail = mailer();
    $mail->addAddress($email, $first_name);
    $mail->Subject = 'Email Verification - Civic Connect';
    $mail->Body = getVerificationEmailHTML($first_name, $otp_code);
    
    echo "Attempting to send email...\n";
    $result = $mail->send();
    
    if ($result) {
        echo "✅ SUCCESS: Email sent successfully!\n";
        echo "Expected behavior: User should receive OTP at $email\n";
    } else {
        echo "❌ FAILED: Could not send email\n";
        echo "Error info: " . $mail->ErrorInfo . "\n";
    }
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Error info: " . $mail->ErrorInfo . "\n";
}
