<?php
/**
 * Helper functions for file upload and image handling
 */

require_once __DIR__ . '/../config/bootstrap.php';



/**
 * Handle file upload for issues
 * 
 * @param array $file $_FILES array element
 * @param string $upload_dir Upload directory path
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function uploadIssueImage($file, $upload_dir = null) {
    if (!$upload_dir) {
        $upload_dir = __DIR__ . '/../uploads/issues/';
    }

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Validate file upload
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Invalid file upload'];
    }

    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit'];
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only images allowed'];
    }

    // Generate unique filename
    $filename = uniqid('issue_', true) . '.' . getFileExtension($file['name']);
    $filepath = $upload_dir . $filename;

    // Move uploaded file to destination
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'path' => 'uploads/issues/' . $filename,
            'filename' => $filename
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to save file'];
    }
}

/**
 * Delete an uploaded image
 * 
 * @param string $filepath Relative path to file
 * @return bool
 */
function deleteImage($filepath) {
    $full_path = __DIR__ . '/../' . $filepath;
    if (file_exists($full_path) && is_file($full_path)) {
        return unlink($full_path);
    }
    return false;
}

/**
 * Get file extension
 * 
 * @param string $filename
 * @return string
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Validate email format
 * 
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using bcrypt
 * 
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 * 
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random OTP code
 * 
 * @param int $length
 * @return string
 */
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Get request data (handles JSON and form data)
 * 
 * @return array
 */
function getRequestData() {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($content_type, 'application/json') !== false) {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    return $_POST;
}

/**
 * Send JSON response
 * 
 * @param mixed $data
 * @param int $status_code
 */
function sendResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 * 
 * @param string $message
 * @param int $status_code
 */
function sendError($message, $status_code = 400) {
    sendResponse(['error' => $message], $status_code);
}
?>
