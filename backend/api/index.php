<?php
/**
 * API Router - Main entry point for all API requests
 * 
 * Routes:
 * POST   /api/users/register              - Register new user
 * POST   /api/users/verify-email          - Verify email with OTP
 * POST   /api/users/resend-otp            - Resend OTP code
 * POST   /api/users/login                 - Login user
 * POST   /api/users/logout                - Logout user
 * GET    /api/users/{id}                  - Get user profile
 * PUT    /api/users/{id}                  - Update user profile
 * 
 * POST   /api/issues                      - Create issue
 * GET    /api/issues                      - List all issues (with filters)
 * GET    /api/issues/{id}                 - Get single issue
 * PUT    /api/issues/{id}                 - Update issue
 * DELETE /api/issues/{id}                 - Delete issue
 * GET    /api/users/{id}/issues           - Get user's issues
 * 
 * POST   /api/upload/issue                - Upload issue image
 * POST   /api/upload/profile              - Upload profile image
 * DELETE /api/files                       - Delete file
 * PUT    /api/issues/{id}/image           - Update issue image
 * GET    /api/files/{filename}            - Get file info
 * 
 * POST   /api/issues/{id}/upvotes         - Upvote issue
 * DELETE /api/issues/{id}/upvotes         - Remove upvote
 * GET    /api/issues/{id}/upvotes         - Get issue upvotes
 */

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/IssueController.php';
require_once __DIR__ . '/controllers/FileController.php';
require_once __DIR__ . '/controllers/AdminController.php';

// Set CORS headers
Middleware::setCORSHeaders();

// Set content type to JSON
header('Content-Type: application/json');

// Start session for token-based authentication
session_start();

// Get request URI and method
// Support multiple ways to access the API:
// 1. Via .htaccess rewrite: route parameter
// 2. Via DirectoryIndex (when accessing /api/): parse REQUEST_URI
// 3. Via explicit path: parse REQUEST_URI

$request_uri = '';

if (isset($_GET['route']) && !empty($_GET['route'])) {
    // Method 1: .htaccess passed the route as a query parameter
    $request_uri = $_GET['route'];
} else {
    // Method 2 & 3: Parse REQUEST_URI directly
    $full_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base_path = '/civic-connect/backend/api';
    
    // Remove the base path to get the route
    if (strpos($full_uri, $base_path) === 0) {
        $request_uri = substr($full_uri, strlen($base_path));
    } else {
        $request_uri = $full_uri;
    }
    
    // Remove index.php if it's in the path (for DirectoryIndex scenario)
    $request_uri = str_replace('/index.php', '', $request_uri);
}

$request_method = $_SERVER['REQUEST_METHOD'];

// Route the request
routeRequest($request_uri, $request_method);

/**
 * Route API requests to appropriate controllers
 */
function routeRequest($uri, $method) {
    // Remove leading/trailing slashes
    $uri = trim($uri, '/');
    
    // If URI is empty, return an error with available endpoints
    if (empty($uri)) {
        sendError('API endpoint required. Available endpoints: /users, /issues, /upload, /files', 400);
    }
    
    // Split URI into parts
    $parts = explode('/', $uri);
    $endpoint = array_shift($parts);

    // User routes
    if ($endpoint === 'users') {
        handleUserRoutes($parts, $method);
    }
    // Issue routes
    elseif ($endpoint === 'issues') {
        handleIssueRoutes($parts, $method);
    }
    // Upload routes
    elseif ($endpoint === 'upload') {
        handleUploadRoutes($parts, $method);
    }
    // Files routes
    elseif ($endpoint === 'files') {
        handleFileRoutes($parts, $method);
    }
    // Admin routes
    elseif ($endpoint === 'admin') {
        handleAdminRoutes($parts, $method);
    }
    else {
        sendError('Endpoint not found', 404);
    }
}

/**
 * Handle user-related routes
 */
function handleUserRoutes($parts, $method) {
    $controller = new UserController();

    if (empty($parts)) {
        sendError('Invalid user endpoint', 400);
    }

    $action = array_shift($parts);

    switch ($action) {
        case 'register':
            $controller->register();
            break;

        case 'login':
            $controller->login();
            break;

        case 'logout':
            $controller->logout();
            break;

        case 'verify-email':
            $controller->verifyEmail();
            break;

        case 'resend-otp':
            $controller->resendOTP();
            break;

        default:
            // Check if this is a user ID endpoint
            if (is_numeric($action)) {
                $user_id = (int)$action;

                if (empty($parts)) {
                    // GET /users/{id} or PUT /users/{id}
                    if ($method === 'GET') {
                        $controller->getProfile($user_id);
                    } elseif ($method === 'PUT') {
                        $controller->updateProfile($user_id);
                    } else {
                        sendError('Method not allowed', 405);
                    }
                } else {
                    $sub_action = array_shift($parts);

                    if ($sub_action === 'issues') {
                        // GET /users/{id}/issues
                        if ($method === 'GET') {
                            require_once __DIR__ . '/controllers/IssueController.php';
                            $issue_controller = new IssueController();
                            $issue_controller->getUserIssues($user_id);
                        } else {
                            sendError('Method not allowed', 405);
                        }
                    } else {
                        sendError('Invalid endpoint', 404);
                    }
                }
            } else {
                sendError('Invalid endpoint', 404);
            }
    }
}

/**
 * Handle issue-related routes
 */
function handleIssueRoutes($parts, $method) {
    $controller = new IssueController();

    if (empty($parts)) {
        // POST /issues (create) or GET /issues (list)
        if ($method === 'POST') {
            $controller->create();
        } elseif ($method === 'GET') {
            $controller->listIssues();
        } else {
            sendError('Method not allowed', 405);
        }
    } else {
        $issue_id = array_shift($parts);

        if (is_numeric($issue_id)) {
            $issue_id = (int)$issue_id;

            if (empty($parts)) {
                // GET /issues/{id}, PUT /issues/{id}, DELETE /issues/{id}
                if ($method === 'GET') {
                    $controller->getIssue($issue_id);
                } elseif ($method === 'PUT') {
                    $controller->update($issue_id);
                } elseif ($method === 'DELETE') {
                    $controller->delete($issue_id);
                } else {
                    sendError('Method not allowed', 405);
                }
            } else {
                $sub_action = array_shift($parts);

                // Issue sub-routes
                if ($sub_action === 'image') {
                    // PUT /issues/{id}/image
                    if ($method === 'PUT') {
                        require_once __DIR__ . '/controllers/FileController.php';
                        $file_controller = new FileController();
                        $file_controller->updateIssueImage($issue_id);
                    } else {
                        sendError('Method not allowed', 405);
                    }
                } elseif ($sub_action === 'upvotes') {
                    // POST /issues/{id}/upvotes, GET /issues/{id}/upvotes, DELETE /issues/{id}/upvotes
                    require_once __DIR__ . '/controllers/UpvoteController.php';
                    $upvote_controller = new UpvoteController();

                    if ($method === 'POST') {
                        $upvote_controller->addUpvote($issue_id);
                    } elseif ($method === 'GET') {
                        $upvote_controller->getUpvotes($issue_id);
                    } elseif ($method === 'DELETE') {
                        $upvote_controller->removeUpvote($issue_id);
                    } else {
                        sendError('Method not allowed', 405);
                    }
                } else {
                    sendError('Invalid endpoint', 404);
                }
            }
        } else {
            sendError('Invalid issue ID', 400);
        }
    }
}

/**
 * Handle file upload routes
 */
function handleUploadRoutes($parts, $method) {
    if (empty($parts)) {
        sendError('Invalid upload endpoint', 400);
    }

    $controller = new FileController();
    $upload_type = array_shift($parts);

    if ($method !== 'POST') {
        sendError('Method not allowed', 405);
    }

    switch ($upload_type) {
        case 'issue':
            $controller->uploadIssueImage();
            break;

        case 'profile':
            $controller->uploadProfileImage();
            break;

        default:
            sendError('Invalid upload type', 400);
    }
}

/**
 * Handle file routes
 */
function handleFileRoutes($parts, $method) {
    $controller = new FileController();

    if ($method === 'DELETE') {
        // DELETE /files
        $controller->deleteFile();
    } elseif ($method === 'GET' && !empty($parts)) {
        // GET /files/{filename}
        $filename = array_shift($parts);
        $controller->getFileInfo($filename);
    } else {
        sendError('Invalid file endpoint', 400);
    }
}


/**
 * Handle admin routes
 */
function handleAdminRoutes($parts, $method) {
    // Check if parts is empty, meaning just /api/admin
    if (empty($parts)) {
        sendError('Invalid admin endpoint', 400);
    }

    $controller = new AdminController();
    $action = array_shift($parts);

    switch ($action) {
        case 'stats':
            // GET /api/admin/stats
            $controller->getStats();
            break;

        case 'users':
            // GET /api/admin/users
            if (empty($parts)) {
                $controller->getAllUsers();
            } else {
                $user_id = array_shift($parts);
                $sub_action = array_shift($parts);
                
                if ($sub_action === 'role') {
                    // PUT /api/admin/users/{id}/role
                    $controller->updateUserRole($user_id);
                } else {
                    sendError('Invalid admin users endpoint', 404);
                }
            }
            break;

        case 'audit-logs':
            // GET /api/admin/audit-logs
            $controller->getAuditLogs();
            break;

        default:
            sendError('Invalid admin endpoint', 404);
    }
}
?>
