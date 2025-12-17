<?php
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
