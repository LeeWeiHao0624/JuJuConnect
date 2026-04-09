<?php
/**
 * JuJuConnect - Common Functions
 * Utility functions used throughout the application
 */

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 */
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if user has any of the specified roles
 */
function has_any_role($roles) {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

/**
 * Require login - redirect to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit();
    }
}

/**
 * Require specific role - redirect if user doesn't have the role
 */
function require_role($role) {
    require_login();
    if (!has_role($role)) {
        header('Location: ' . SITE_URL . '/index.php?error=access_denied');
        exit();
    }
}

/**
 * Require any of the specified roles
 */
function require_any_role($roles) {
    require_login();
    if (!has_any_role($roles)) {
        header('Location: ' . SITE_URL . '/index.php?error=access_denied');
        exit();
    }
}

/**
 * Redirect to a specific page
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Set flash message in session
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Display and clear flash message
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        
        $alert_class = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        $class = $alert_class[$type] ?? 'alert-info';
        
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Format date for display
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format time for display
 */
function format_time($time, $format = 'g:i A') {
    return date($format, strtotime($time));
}

/**
 * Format datetime for display
 */
function format_datetime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Time ago function
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];
    
    foreach ($periods as $key => $value) {
        if ($difference >= $value) {
            $time = floor($difference / $value);
            return $time . ' ' . $key . ($time > 1 ? 's' : '') . ' ago';
        }
    }
    
    return 'Just now';
}

/**
 * Generate random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Malaysian format)
 */
function validate_phone($phone) {
    // Remove any spaces, dashes, or parentheses
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    // Check if it matches Malaysian phone pattern
    return preg_match('/^(\+?6?01)[0-9]{8,9}$/', $phone);
}

/**
 * Format rating for display
 */
function format_rating($rating, $decimals = 1) {
    return number_format($rating, $decimals);
}

/**
 * Generate star rating HTML
 */
function display_star_rating($rating, $max = 5) {
    $html = '<div class="star-rating">';
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5 ? 1 : 0;
    $empty_stars = $max - $full_stars - $half_star;
    
    // Full stars
    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    
    // Half star
    if ($half_star) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    
    // Empty stars
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star text-warning"></i>';
    }
    
    $html .= ' <span class="rating-value">' . format_rating($rating) . '</span>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Upload profile picture
 */
function upload_profile_picture($file) {
    // Check if upload directory exists
    if (!is_dir(PROFILE_PICS_DIR)) {
        mkdir(PROFILE_PICS_DIR, 0755, true);
    }
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }
    
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . uniqid() . '_' . time() . '.' . $extension;
    $destination = PROFILE_PICS_DIR . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file'];
}

/**
 * Get profile picture URL
 */
function get_profile_picture($filename) {
    if (empty($filename) || $filename === 'default-avatar.png') {
        return SITE_URL . '/assets/images/default-avatar.png';
    }
    return SITE_URL . '/uploads/profiles/' . $filename;
}

/**
 * Calculate eco points
 */
function calculate_eco_points($distance, $passengers_count, $role = 'passenger') {
    $points = BASE_ECO_POINTS;
    $points += ceil($distance * ECO_POINTS_PER_KM * ($passengers_count + 1));
    
    if ($role === 'driver') {
        $points += ($passengers_count * PASSENGER_BONUS_POINTS);
    }
    
    return $points;
}

/**
 * Calculate CO2 saved
 */
function calculate_co2_saved($distance, $passengers_count) {
    return round($distance * $passengers_count * CO2_PER_KM, 2);
}

/**
 * Get user by ID
 */
function get_user_by_id($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE UserID = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Get user by email
 */
function get_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Update user last login
 */
function update_last_login($pdo, $user_id) {
    $stmt = $pdo->prepare("UPDATE Users SET LastLogin = NOW() WHERE UserID = ?");
    $stmt->execute([$user_id]);
}

/**
 * Log activity
 */
function log_activity($pdo, $user_id, $action, $entity_type = null, $entity_id = null, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $pdo->prepare("
        INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, IPAddress, UserAgent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $action, $entity_type, $entity_id, $details, $ip, $user_agent]);
}

/**
 * Create notification
 */
function create_notification($pdo, $user_id, $type, $title, $message, $related_id = null) {
    $stmt = $pdo->prepare("
        INSERT INTO Notifications (UserID, Type, Title, Message, RelatedID) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $type, $title, $message, $related_id]);
}

/**
 * Get unread notifications count
 */
function get_unread_notifications_count($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE UserID = ? AND IsRead = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

/**
 * Paginate results
 */
function paginate($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset
    ];
}

/**
 * Generate pagination HTML
 */
function display_pagination($total_pages, $current_page, $base_url) {
    if ($total_pages <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    $disabled = $current_page <= 1 ? 'disabled' : '';
    $html .= '<li class="page-item ' . $disabled . '">';
    $html .= '<a class="page-link" href="' . $base_url . '&page=' . ($current_page - 1) . '">Previous</a>';
    $html .= '</li>';
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
    }
    
    // Next button
    $disabled = $current_page >= $total_pages ? 'disabled' : '';
    $html .= '<li class="page-item ' . $disabled . '">';
    $html .= '<a class="page-link" href="' . $base_url . '&page=' . ($current_page + 1) . '">Next</a>';
    $html .= '</li>';
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Escape output for HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token input field
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Award eco-points to a user
 */
function award_eco_points($pdo, $user_id, $points, $reason = '') {
    try {
        // Update user's eco-points
        $stmt = $pdo->prepare("UPDATE Users SET EcoPoints = EcoPoints + ? WHERE UserID = ?");
        $stmt->execute([$points, $user_id]);
        
        // Log the eco-points transaction (if you have a transactions table)
        // For now, we'll just log it as an activity
        log_activity($pdo, $user_id, 'earn_eco_points', 'User', $user_id, $reason);
        
        return true;
    } catch (PDOException $e) {
        error_log("Award eco-points error: " . $e->getMessage());
        return false;
    }
}
?>

