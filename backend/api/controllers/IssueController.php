<?php
/**
 * Issue Controller - Handles CRUD operations for issues
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Middleware.php';
require_once __DIR__ . '/../helpers.php';

class IssueController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Create a new issue
     * POST /api/issues
     */
    public function create() {
        if (!Middleware::validateMethod('POST')) {
            sendError('Method not allowed', 405);
        }

        $user = Middleware::requireAuth();
        $data = getRequestData();

        // Validate required fields
        $required = ['title', 'description', 'category'];
        if (!Middleware::validateRequired($data, $required)) {
            sendError('Missing required fields: ' . implode(', ', $required), 400);
        }

        // Validate title length
        if (strlen($data['title']) < 5 || strlen($data['title']) > 255) {
            sendError('Title must be between 5 and 255 characters', 400);
        }

        // Validate description length
        if (strlen($data['description']) < 10) {
            sendError('Description must be at least 10 characters long', 400);
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO issues (user_id, title, description, category, location, latitude, longitude, priority, status, is_anonymous)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $priority = $data['priority'] ?? 'medium';
            $status = $data['status'] ?? 'open';
            $is_anonymous = isset($data['is_anonymous']) ? (int)$data['is_anonymous'] : 0;
            $location = $data['location'] ?? null;
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;

            // Validate priority and status
            $valid_priorities = ['low', 'medium', 'high', 'critical'];
            $valid_statuses = ['open', 'in_progress', 'resolved', 'closed'];

            if (!in_array($priority, $valid_priorities)) {
                sendError('Invalid priority value', 400);
            }

            if (!in_array($status, $valid_statuses)) {
                sendError('Invalid status value', 400);
            }

            $stmt->execute([
                $user['user_id'],
                $data['title'],
                $data['description'],
                $data['category'],
                $location,
                $latitude,
                $longitude,
                $priority,
                $status,
                $is_anonymous
            ]);

            $issue_id = $this->pdo->lastInsertId();

            // Log audit trail
            Middleware::logAuditTrail($user['user_id'], 'ISSUE_CREATED', 'issues', $issue_id, null, [
                'title' => $data['title'],
                'category' => $data['category'],
                'priority' => $priority
            ]);

            sendResponse([
                'success' => true,
                'message' => 'Issue created successfully',
                'issue_id' => $issue_id
            ], 201);
        } catch (PDOException $e) {
            sendError('Failed to create issue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single issue
     * GET /api/issues/{id}
     */
    public function getIssue($issue_id) {
        if (!Middleware::validateMethod('GET')) {
            sendError('Method not allowed', 405);
        }

        try {
            // Try to get current user if authenticated (optional for this endpoint)
            $current_user = Middleware::authenticate();

            $stmt = $this->pdo->prepare("
                SELECT
                    i.*,
                    u.first_name,
                    u.last_name,
                    u.profile_image,
                    (SELECT COUNT(*) FROM upvotes WHERE issue_id = i.id) as upvote_count
                FROM issues i
                LEFT JOIN users u ON i.user_id = u.id
                WHERE i.id = ?
            ");
            $stmt->execute([$issue_id]);
            $issue = $stmt->fetch();

            if (!$issue) {
                sendError('Issue not found', 404);
            }

            // Check if current user has upvoted
            $user_has_upvoted = false;
            if ($current_user) {
                $upvote_stmt = $this->pdo->prepare("
                    SELECT id FROM upvotes 
                    WHERE issue_id = ? AND user_id = ?
                ");
                $upvote_stmt->execute([$issue_id, $current_user['user_id']]);
                $user_has_upvoted = (bool)$upvote_stmt->fetch();
            }
            $issue['user_has_upvoted'] = $user_has_upvoted;

            // Add image_url from image_path
            if (!empty($issue['image_path'])) {
                $issue['image_url'] = 'http://localhost/civic-connect/backend/' . $issue['image_path'];
            } else {
                $issue['image_url'] = null;
            }

            // Add user_name field
            if ($issue['is_anonymous']) {
                $issue['user_name'] = 'Anonymous';
                $issue['first_name'] = 'Anonymous';
                $issue['last_name'] = '';
                $issue['profile_image'] = null;
            } else {
                $issue['user_name'] = trim($issue['first_name'] . ' ' . $issue['last_name']);
            }

            // Convert numeric fields to proper types
            if ($issue['latitude'] !== null) {
                $issue['latitude'] = (float)$issue['latitude'];
            }
            if ($issue['longitude'] !== null) {
                $issue['longitude'] = (float)$issue['longitude'];
            }
            $issue['upvote_count'] = (int)$issue['upvote_count'];

            sendResponse([
                'success' => true,
                'issue' => $issue
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to fetch issue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all issues with filters and pagination
     * GET /api/issues
     */
    public function listIssues() {
        if (!Middleware::validateMethod('GET')) {
            sendError('Method not allowed', 405);
        }

        // Get query parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, (int)$_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;
        $category = $_GET['category'] ?? null;
        $status = $_GET['status'] ?? null;
        $priority = $_GET['priority'] ?? null;
        $search = $_GET['search'] ?? null;
        $sort_by = $_GET['sort_by'] ?? 'created_at';
        $sort_order = ($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        // Validate sort parameters
        $allowed_sorts = ['created_at', 'upvote_count', 'title', 'priority'];
        if (!in_array($sort_by, $allowed_sorts)) {
            $sort_by = 'created_at';
        }

        try {
            $where = ['1=1'];
            $params = [];

            if ($category) {
                $where[] = 'i.category = ?';
                $params[] = $category;
            }

            if ($status) {
                $where[] = 'i.status = ?';
                $params[] = $status;
            }

            if ($priority) {
                $where[] = 'i.priority = ?';
                $params[] = $priority;
            }

            if ($search) {
                $where[] = '(i.title LIKE ? OR i.description LIKE ?)';
                $search_term = "%$search%";
                $params[] = $search_term;
                $params[] = $search_term;
            }

            $where_clause = implode(' AND ', $where);

            // Get total count
            $count_stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM issues i
                WHERE $where_clause
            ");
            $count_stmt->execute($params);
            $total = $count_stmt->fetch()['total'];

            // Get issues
            $stmt = $this->pdo->prepare("
                SELECT
                    i.*,
                    u.first_name,
                    u.last_name,
                    u.profile_image,
                    (SELECT COUNT(*) FROM upvotes WHERE issue_id = i.id) as upvote_count
                FROM issues i
                LEFT JOIN users u ON i.user_id = u.id
                WHERE $where_clause
                ORDER BY i.$sort_by $sort_order
                LIMIT ? OFFSET ?
            ");

            // Bind WHERE clause parameters
            $param_index = 1;
            foreach ($params as $value) {
                $stmt->bindValue($param_index, $value);
                $param_index++;
            }
            
            // Bind LIMIT and OFFSET as integers
            $stmt->bindValue($param_index, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue($param_index + 1, (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $issues = $stmt->fetchAll();

            // Remove user details for anonymous issues
            foreach ($issues as &$issue) {
                if ($issue['is_anonymous']) {
                    $issue['first_name'] = 'Anonymous';
                    $issue['last_name'] = '';
                    $issue['profile_image'] = null;
                }
            }

            sendResponse([
                'success' => true,
                'issues' => $issues,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to fetch issues: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an issue
     * PUT /api/issues/{id}
     */
    public function update($issue_id) {
        if (!Middleware::validateMethod('PUT')) {
            sendError('Method not allowed', 405);
        }

        $user = Middleware::requireAuth();
        $data = getRequestData();

        try {
            // Get current issue
            $stmt = $this->pdo->prepare("SELECT user_id FROM issues WHERE id = ?");
            $stmt->execute([$issue_id]);
            $issue = $stmt->fetch();

            if (!$issue) {
                sendError('Issue not found', 404);
            }

            // Check ownership or role
            $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user['user_id']]);
            $user_role = $stmt->fetchColumn();

            if (!Middleware::ownsResource($user['user_id'], $issue['user_id']) && !in_array($user_role, ['admin', 'staff'])) {
                sendError('Unauthorized: Cannot update other user\'s issues', 403);
            }

            $allowed_fields = ['title', 'description', 'category', 'location', 'latitude', 'longitude', 'priority', 'status'];
            $update_fields = [];
            $update_values = [];

            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    // Validate title and description if provided
                    if ($field === 'title' && (strlen($data[$field]) < 5 || strlen($data[$field]) > 255)) {
                        sendError('Title must be between 5 and 255 characters', 400);
                    }

                    if ($field === 'description' && strlen($data[$field]) < 10) {
                        sendError('Description must be at least 10 characters long', 400);
                    }

                    // Validate priority and status enums
                    if ($field === 'priority' && !in_array($data[$field], ['low', 'medium', 'high', 'critical'])) {
                        sendError('Invalid priority value', 400);
                    }

                    if ($field === 'status' && !in_array($data[$field], ['open', 'in_progress', 'resolved', 'closed'])) {
                        sendError('Invalid status value', 400);
                    }

                    $update_fields[] = "i.$field = ?";
                    $update_values[] = $data[$field];
                }
            }

            if (empty($update_fields)) {
                sendError('No fields to update', 400);
            }

            $update_values[] = $issue_id;

            $stmt = $this->pdo->prepare("
                UPDATE issues i
                SET " . implode(', ', $update_fields) . "
                WHERE i.id = ?
            ");
            $stmt->execute($update_values);

            // Log audit trail
            Middleware::logAuditTrail($user['user_id'], 'ISSUE_UPDATED', 'issues', $issue_id, null, $data);

            sendResponse([
                'success' => true,
                'message' => 'Issue updated successfully'
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to update issue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an issue
     * DELETE /api/issues/{id}
     */
    public function delete($issue_id) {
        if (!Middleware::validateMethod('DELETE')) {
            sendError('Method not allowed', 405);
        }

        $user = Middleware::requireAuth();

        try {
            // Get issue
            $stmt = $this->pdo->prepare("SELECT user_id, image_path FROM issues WHERE id = ?");
            $stmt->execute([$issue_id]);
            $issue = $stmt->fetch();

            if (!$issue) {
                sendError('Issue not found', 404);
            }

            // Check ownership or role
            $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user['user_id']]);
            $user_role = $stmt->fetchColumn();

            if (!Middleware::ownsResource($user['user_id'], $issue['user_id']) && !in_array($user_role, ['admin', 'staff'])) {
                sendError('Unauthorized: Cannot delete other user\'s issues', 403);
            }

            // Delete image if exists
            if ($issue['image_path']) {
                deleteImage($issue['image_path']);
            }

            // Delete issue (cascade delete handled by DB)
            $stmt = $this->pdo->prepare("DELETE FROM issues WHERE id = ?");
            $stmt->execute([$issue_id]);

            // Log audit trail
            Middleware::logAuditTrail($user['user_id'], 'ISSUE_DELETED', 'issues', $issue_id);

            sendResponse([
                'success' => true,
                'message' => 'Issue deleted successfully'
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to delete issue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get issues by user
     * GET /api/users/{id}/issues
     */
    public function getUserIssues($user_id) {
        if (!Middleware::validateMethod('GET')) {
            sendError('Method not allowed', 405);
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(50, (int)$_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;

        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM issues
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            $total = $stmt->fetch()['total'];

            $stmt = $this->pdo->prepare("
                SELECT *
                FROM issues
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
                OFFSET ?
            ");
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $issues = $stmt->fetchAll();

            sendResponse([
                'success' => true,
                'issues' => $issues,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to fetch user issues: ' . $e->getMessage(), 500);
        }
    }
}
?>
