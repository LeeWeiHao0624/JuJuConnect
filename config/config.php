<?php
/**
 * JuJuConnect - Application Configuration
 * Global configuration settings
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Auto-detect site URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Get the base path by finding the root directory
// __DIR__ is the config folder, so go up one level to get root
$doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
$app_root = realpath(__DIR__ . '/..');
$base_path = str_replace($doc_root, '', $app_root);
$base_path = str_replace('\\', '/', $base_path); // Windows compatibility

$site_url = $protocol . '://' . $host . $base_path;

// Application settings
define('SITE_NAME', 'JuJuConnect');
define('SITE_URL', rtrim($site_url, '/'));
define('SITE_EMAIL', 'noreply@jujuconnect.com');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PROFILE_PICS_DIR', UPLOAD_DIR . 'profiles/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Pagination settings
define('ITEMS_PER_PAGE', 10);
define('LEADERBOARD_LIMIT', 50);

// Eco-points calculation settings
define('BASE_ECO_POINTS', 10);
define('ECO_POINTS_PER_KM', 0.5);
define('PASSENGER_BONUS_POINTS', 5);
define('CO2_PER_KM', 0.12); // kg

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
$pdo = require_once __DIR__ . '/database.php';

// Include utility functions
require_once __DIR__ . '/../includes/functions.php';
?>

