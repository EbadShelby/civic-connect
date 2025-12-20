<?php
/**
 * Chatbot API Endpoint
 * Handles AI chatbot interactions using Google Gemini
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/GeminiService.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['message']) || empty(trim($input['message']))) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is required']);
        exit();
    }
    
    $userMessage = trim($input['message']);
    $conversationHistory = $input['history'] ?? [];
    
    // Validate conversation history format
    if (!is_array($conversationHistory)) {
        $conversationHistory = [];
    }
    
    // Limit conversation history to last 10 messages to avoid token limits
    if (count($conversationHistory) > 10) {
        $conversationHistory = array_slice($conversationHistory, -10);
    }
    
    // Initialize Gemini service
    $geminiService = new GeminiService();
    
    // Generate response
    $result = $geminiService->generateResponse($userMessage, $conversationHistory);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'timestamp' => date('c')
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error']
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ]);
}
