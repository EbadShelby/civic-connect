<?php
/**
 * User Controller - Handles user registration, login, and profile management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mailer.php';
require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/helpers.php';

class UserController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Register a new user
     * POST /api/users/register
     */
    public function register() {
        if (!Middleware::validateMethod('POST')) {
            sendError('Method not allowed', 405);
        }

        $data = getRequestData();

        // Validate required fields
        $required = ['email', 'password', 'first_name', 'last_name'];
        if (!Middleware::validateRequired($data, $required)) {
            sendError('Missing required fields: ' . implode(', ', $required), 400);
        }

        // Validate email format
        if (!isValidEmail($data['email'])) {
            sendError('Invalid email format', 400);
        }

        // Validate password strength
        if (strlen($data['password']) < 8) {
            sendError('Password must be at least 8 characters long', 400);
        }

        // Check if user already exists
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                sendError('Email already registered', 409);
            }
        } catch (PDOException $e) {
            sendError('Database error: ' . $e->getMessage(), 500);
        }

        // Create user
        try {
            $password_hash = hashPassword($data['password']);
            $otp_code = generateOTP(6);
            $otp_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $this->pdo->prepare("
                INSERT INTO users (email, password_hash, first_name, last_name, phone, otp_code, otp_expires_at, email_verified)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)
            ");

            $phone = $data['phone'] ?? null;
            $stmt->execute([
                $data['email'],
                $password_hash,
                $data['first_name'],
                $data['last_name'],
                $phone,
                $otp_code,
                $otp_expires
            ]);

            $user_id = $this->pdo->lastInsertId();

            // Send verification email with OTP
            $this->sendVerificationEmail($data['email'], $data['first_name'], $otp_code);

            // Log audit trail
            Middleware::logAuditTrail($user_id, 'USER_CREATED', 'users', $user_id, null, [
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name']
            ]);

            sendResponse([
                'success' => true,
                'message' => 'User registered successfully. Please verify your email using the OTP code sent to your email address.',
                'user_id' => $user_id,
                'email' => $data['email']
            ], 201);
        } catch (PDOException $e) {
            sendError('Failed to register user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify email with OTP
     * POST /api/users/verify-email
     */
    public function verifyEmail() {
        if (!Middleware::validateMethod('POST')) {
            sendError('Method not allowed', 405);
        }

        $data = getRequestData();

        // Validate required fields
        if (!Middleware::validateRequired($data, ['email', 'otp_code'])) {
            sendError('Email and OTP code required', 400);
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, otp_code, otp_expires_at, otp_attempts, email_verified
                FROM users
                WHERE email = ?
            ");
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch();

            if (!$user) {
                sendError('User not found', 404);
            }

            // Check if already verified
            if ($user['email_verified']) {
                sendError('Email already verified', 400);
            }

            // Check OTP attempts
            if ($user['otp_attempts'] >= 5) {
                sendError('Maximum OTP attempts exceeded. Please request a new OTP.', 429);
            }

            // Check OTP expiration
            if (strtotime($user['otp_expires_at']) < time()) {
                sendError('OTP code has expired', 400);
            }

            // Verify OTP
            if ($user['otp_code'] !== $data['otp_code']) {
                // Increment attempts
                $stmt = $this->pdo->prepare("
                    UPDATE users
                    SET otp_attempts = otp_attempts + 1
                    WHERE id = ?
                ");
                $stmt->execute([$user['id']]);

                sendError('Invalid OTP code', 400);
            }

            // Update user - mark as verified
            $stmt = $this->pdo->prepare("
                UPDATE users
                SET email_verified = 1, email_verified_at = NOW(), otp_code = NULL, otp_expires_at = NULL, otp_attempts = 0
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);

            // Log audit trail
            Middleware::logAuditTrail($user['id'], 'EMAIL_VERIFIED', 'users', $user['id']);

            sendResponse([
                'success' => true,
                'message' => 'Email verified successfully',
                'user_id' => $user['id']
            ], 200);
        } catch (PDOException $e) {
            sendError('Verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resend OTP code
     * POST /api/users/resend-otp
     */
    public function resendOTP() {
        if (!Middleware::validateMethod('POST')) {
            sendError('Method not allowed', 405);
        }

        $data = getRequestData();

        if (!isset($data['email'])) {
            sendError('Email is required', 400);
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, first_name, email_verified
                FROM users
                WHERE email = ?
            ");
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch();

            if (!$user) {
                sendError('User not found', 404);
            }

            if ($user['email_verified']) {
                sendError('Email is already verified', 400);
            }

            // Generate new OTP
            $otp_code = generateOTP(6);
            $otp_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $this->pdo->prepare("
                UPDATE users
                SET otp_code = ?, otp_expires_at = ?, otp_attempts = 0
                WHERE id = ?
            ");
            $stmt->execute([$otp_code, $otp_expires, $user['id']]);

            // Send verification email
            $this->sendVerificationEmail($data['email'], $user['first_name'], $otp_code);

            // Log audit trail
            Middleware::logAuditTrail($user['id'], 'OTP_RESENT', 'users', $user['id']);

            sendResponse([
                'success' => true,
                'message' => 'OTP code sent to your email address'
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to resend OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Login user
     * POST /api/users/login
     */
    public function login() {
        if (!Middleware::validateMethod('POST')) {
            sendError('Method not allowed', 405);
        }

        $data = getRequestData();

        // Validate required fields
        if (!Middleware::validateRequired($data, ['email', 'password'])) {
            sendError('Email and password required', 400);
        }

        // Rate limiting
        if (!Middleware::rateLimit($data['email'], 5, 300)) {
            sendError('Too many login attempts. Please try again later.', 429);
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, email, password_hash, first_name, last_name, email_verified, is_active
                FROM users
                WHERE email = ?
            ");
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch();

            if (!$user) {
                sendError('Invalid email or password', 401);
            }

            if (!$user['is_active']) {
                sendError('Account is inactive', 403);
            }

            if (!$user['email_verified']) {
                sendError('Please verify your email address first', 403);
            }

            // Verify password
            if (!verifyPassword($data['password'], $user['password_hash'])) {
                sendError('Invalid email or password', 401);
            }

            // Generate token
            $token = bin2hex(random_bytes(32));

            // Store session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['token'] = $token;

            // Log audit trail
            Middleware::logAuditTrail($user['id'], 'USER_LOGIN', 'users', $user['id']);

            sendResponse([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ],
                'token' => $token
            ], 200);
        } catch (PDOException $e) {
            sendError('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Logout user
     * POST /api/users/logout
     */
    public function logout() {
        if (!Middleware::validateMethod('POST')) {
            sendError('Method not allowed', 405);
        }

        $user = Middleware::requireAuth();

        // Log audit trail
        Middleware::logAuditTrail($user['user_id'], 'USER_LOGOUT', 'users', $user['user_id']);

        session_start();
        session_destroy();

        sendResponse([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get user profile
     * GET /api/users/{id}
     */
    public function getProfile($user_id) {
        if (!Middleware::validateMethod('GET')) {
            sendError('Method not allowed', 405);
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, email, first_name, last_name, phone, profile_image, bio, location, created_at
                FROM users
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                sendError('User not found', 404);
            }

            sendResponse([
                'success' => true,
                'user' => $user
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to fetch user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user profile
     * PUT /api/users/{id}
     */
    public function updateProfile($user_id) {
        if (!Middleware::validateMethod('PUT')) {
            sendError('Method not allowed', 405);
        }

        $auth_user = Middleware::requireAuth();

        // Check if user owns this profile
        if (!Middleware::ownsResource($auth_user['user_id'], $user_id)) {
            sendError('Unauthorized: Cannot update other user profiles', 403);
        }

        $data = getRequestData();

        // Allowed fields for update
        $allowed_fields = ['first_name', 'last_name', 'phone', 'bio', 'location'];
        $update_fields = [];
        $update_values = [];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "$field = ?";
                $update_values[] = $data[$field];
            }
        }

        if (empty($update_fields)) {
            sendError('No fields to update', 400);
        }

        $update_values[] = $user_id;

        try {
            $stmt = $this->pdo->prepare("
                UPDATE users
                SET " . implode(', ', $update_fields) . "
                WHERE id = ?
            ");
            $stmt->execute($update_values);

            // Log audit trail
            Middleware::logAuditTrail($user_id, 'USER_UPDATED', 'users', $user_id, null, $data);

            sendResponse([
                'success' => true,
                'message' => 'Profile updated successfully'
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Send verification email with OTP
     */
    private function sendVerificationEmail($email, $first_name, $otp_code) {
        try {
            $mail = mailer();
            $mail->addAddress($email, $first_name);
            $mail->Subject = 'Email Verification - Civic Connect';
            $mail->Body = $this->getVerificationEmailHTML($first_name, $otp_code);

            return $mail->send();
        } catch (Exception $e) {
            // Log error but don't fail the registration
            error_log('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get verification email HTML template
     */
    private function getVerificationEmailHTML($first_name, $otp_code) {
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
}
?>
