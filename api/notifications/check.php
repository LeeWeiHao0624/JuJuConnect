<?php
/**
 * API Endpoint: Check for new notifications
 * Returns JSON with unread notification count
 */

// Set JSON content type
header('Content-Type: application/json');

// Include config (handles session and database)
require_once '../../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'count' => 0, 'error' => 'Not authenticated']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get unread notification count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM Notifications 
        WHERE UserID = ? AND IsRead = 0
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => (int)$result['count']
    ]);
    
} catch (PDOException $e) {
    error_log("Notification check error: " . $e->getMessage());
    echo json_encode(['success' => false, 'count' => 0, 'error' => 'Database error']);
}

