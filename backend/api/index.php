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
 * 
 * POST   /api/issues/{id}/comments        - Add comment
 * GET    /api/issues/{id}/comments        - Get issue comments
 * PUT    /api/comments/{id}               - Update comment
 * DELETE /api/comments/{id}               - Delete comment
 */

require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/IssueController.php';
require_once __DIR__ . '/controllers/FileController.php';

// Set CORS headers
Middleware::setCORSHeaders();

// Set content type to JSON
header('Content-Type: application/json');

// Start session for token-based authentication
session_start();

// Get request URI and method
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove base path
$base_path = '/civic-connect/backend/api/';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Route the request
routeRequest($request_uri, $request_method);

/**
 * Route API requests to appropriate controllers
 */
function routeRequest($uri, $method) {
    // Remove leading/trailing slashes
    $uri = trim($uri, '/');
    
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
    // Comments routes
    elseif ($endpoint === 'comments') {
        handleCommentRoutes($parts, $method);
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
                } elseif ($sub_action === 'comments') {
                    // POST /issues/{id}/comments, GET /issues/{id}/comments
                    require_once __DIR__ . '/controllers/CommentController.php';
                    $comment_controller = new CommentController();

                    if ($method === 'POST') {
                        $comment_controller->addComment($issue_id);
                    } elseif ($method === 'GET') {
                        $comment_controller->getComments($issue_id);
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
 * Handle comment routes
 */
function handleCommentRoutes($parts, $method) {
    if (empty($parts)) {
        sendError('Invalid comment endpoint', 400);
    }

    $comment_id = array_shift($parts);

    if (!is_numeric($comment_id)) {
        sendError('Invalid comment ID', 400);
    }

    require_once __DIR__ . '/controllers/CommentController.php';
    $controller = new CommentController();
    $comment_id = (int)$comment_id;

    if ($method === 'PUT') {
        $controller->updateComment($comment_id);
    } elseif ($method === 'DELETE') {
        $controller->deleteComment($comment_id);
    } else {
        sendError('Method not allowed', 405);
    }
}
?>
