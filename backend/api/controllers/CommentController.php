<?php
/**
 * Comment Controller - Handles issue comments
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/helpers.php';

class CommentController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Add comment to an issue
     * POST /api/issues/{id}/comments
     */
    public function addComment($issue_id) {
        if (!Middleware::validateMethod('POST')) {
            sendError('Method not allowed', 405);
        }

        $user = Middleware::requireAuth();
        $data = getRequestData();

        // Validate required fields
        if (!Middleware::validateRequired($data, ['content'])) {
            sendError('Comment content is required', 400);
        }

        // Validate content length
        if (strlen(trim($data['content'])) < 2) {
            sendError('Comment must be at least 2 characters long', 400);
        }

        if (strlen($data['content']) > 5000) {
            sendError('Comment cannot exceed 5000 characters', 400);
        }

        try {
            // Check if issue exists
            $stmt = $this->pdo->prepare("SELECT id FROM issues WHERE id = ?");
            $stmt->execute([$issue_id]);
            if (!$stmt->fetch()) {
                sendError('Issue not found', 404);
            }

            // Create comment
            $is_anonymous = isset($data['is_anonymous']) ? (int)$data['is_anonymous'] : 0;

            $stmt = $this->pdo->prepare("
                INSERT INTO comments (issue_id, user_id, content, is_anonymous)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $issue_id,
                $user['user_id'],
                $data['content'],
                $is_anonymous
            ]);

            $comment_id = $this->pdo->lastInsertId();

            // Update comment count in issues table
            $stmt = $this->pdo->prepare("
                UPDATE issues
                SET comment_count = (SELECT COUNT(*) FROM comments WHERE issue_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$issue_id, $issue_id]);

            // Log audit trail
            Middleware::logAuditTrail($user['user_id'], 'COMMENT_ADDED', 'comments', $comment_id, null, [
                'issue_id' => $issue_id,
                'content' => substr($data['content'], 0, 100)
            ]);

            sendResponse([
                'success' => true,
                'message' => 'Comment added successfully',
                'comment_id' => $comment_id
            ], 201);
        } catch (PDOException $e) {
            sendError('Failed to add comment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get comments for an issue
     * GET /api/issues/{id}/comments
     */
    public function getComments($issue_id) {
        if (!Middleware::validateMethod('GET')) {
            sendError('Method not allowed', 405);
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(50, (int)$_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;

        try {
            // Check if issue exists
            $stmt = $this->pdo->prepare("SELECT id FROM issues WHERE id = ?");
            $stmt->execute([$issue_id]);
            if (!$stmt->fetch()) {
                sendError('Issue not found', 404);
            }

            // Get total comments
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM comments
                WHERE issue_id = ?
            ");
            $stmt->execute([$issue_id]);
            $total = $stmt->fetch()['total'];

            // Get comments with user details
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.id,
                    c.issue_id,
                    c.user_id,
                    c.content,
                    c.is_anonymous,
                    c.created_at,
                    c.updated_at,
                    u.first_name,
                    u.last_name,
                    u.profile_image
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.issue_id = ?
                ORDER BY c.created_at ASC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$issue_id, $limit, $offset]);
            $comments = $stmt->fetchAll();

            // Remove user details for anonymous comments
            foreach ($comments as &$comment) {
                if ($comment['is_anonymous']) {
                    $comment['first_name'] = 'Anonymous';
                    $comment['last_name'] = '';
                    $comment['profile_image'] = null;
                    $comment['user_id'] = null;
                }
            }

            sendResponse([
                'success' => true,
                'comments' => $comments,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to fetch comments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a comment
     * PUT /api/comments/{id}
     */
    public function updateComment($comment_id) {
        if (!Middleware::validateMethod('PUT')) {
            sendError('Method not allowed', 405);
        }

        $user = Middleware::requireAuth();
        $data = getRequestData();

        if (!isset($data['content']) || strlen(trim($data['content'])) < 2) {
            sendError('Comment content is required and must be at least 2 characters', 400);
        }

        if (strlen($data['content']) > 5000) {
            sendError('Comment cannot exceed 5000 characters', 400);
        }

        try {
            // Get comment
            $stmt = $this->pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            if (!$comment) {
                sendError('Comment not found', 404);
            }

            // Check ownership
            if (!Middleware::ownsResource($user['user_id'], $comment['user_id'])) {
                sendError('Unauthorized: Cannot update other user\'s comments', 403);
            }

            // Update comment
            $stmt = $this->pdo->prepare("
                UPDATE comments
                SET content = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$data['content'], $comment_id]);

            // Log audit trail
            Middleware::logAuditTrail($user['user_id'], 'COMMENT_UPDATED', 'comments', $comment_id, null, [
                'content' => substr($data['content'], 0, 100)
            ]);

            sendResponse([
                'success' => true,
                'message' => 'Comment updated successfully'
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to update comment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a comment
     * DELETE /api/comments/{id}
     */
    public function deleteComment($comment_id) {
        if (!Middleware::validateMethod('DELETE')) {
            sendError('Method not allowed', 405);
        }

        $user = Middleware::requireAuth();

        try {
            // Get comment
            $stmt = $this->pdo->prepare("SELECT user_id, issue_id FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            if (!$comment) {
                sendError('Comment not found', 404);
            }

            // Check ownership
            if (!Middleware::ownsResource($user['user_id'], $comment['user_id'])) {
                sendError('Unauthorized: Cannot delete other user\'s comments', 403);
            }

            // Delete comment
            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);

            // Update comment count
            $stmt = $this->pdo->prepare("
                UPDATE issues
                SET comment_count = (SELECT COUNT(*) FROM comments WHERE issue_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$comment['issue_id'], $comment['issue_id']]);

            // Log audit trail
            Middleware::logAuditTrail($user['user_id'], 'COMMENT_DELETED', 'comments', $comment_id);

            sendResponse([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to delete comment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get comment by ID
     * GET /api/comments/{id}
     */
    public function getComment($comment_id) {
        if (!Middleware::validateMethod('GET')) {
            sendError('Method not allowed', 405);
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.*,
                    u.first_name,
                    u.last_name,
                    u.profile_image
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = ?
            ");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            if (!$comment) {
                sendError('Comment not found', 404);
            }

            // Remove user details if anonymous
            if ($comment['is_anonymous']) {
                $comment['first_name'] = 'Anonymous';
                $comment['last_name'] = '';
                $comment['profile_image'] = null;
                $comment['user_id'] = null;
            }

            sendResponse([
                'success' => true,
                'comment' => $comment
            ], 200);
        } catch (PDOException $e) {
            sendError('Failed to fetch comment: ' . $e->getMessage(), 500);
        }
    }
}
?>
