<?php
/**
 * JuJuConnect - Change Password
 * Simple password change form
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

require_login();

$page_title = "Change Password";
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid security token.";
    } else {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($new_password) || empty($confirm_password)) {
            $error = "All fields are required.";
        } elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $error = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE Users SET Password = ? WHERE UserID = ?");
                $stmt->execute([$hashed_password, $user_id]);
                
                // Log activity
                log_activity($pdo, $user_id, 'change_password', 'User', $user_id);
                
                set_flash_message('success', 'Password changed successfully!');
                redirect(SITE_URL . '/profile/view.php?id=' . $user_id);
                
            } catch (PDOException $e) {
                error_log("Change password error: " . $e->getMessage());
                $error = "Failed to change password. Please try again.";
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Enter new password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                            <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Re-enter new password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </button>
                            <a href="edit.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Security Tips -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-shield-alt me-2 text-success"></i>Password Tips</h6>
                    <small class="text-muted">
                        <ul class="mb-0">
                            <li>Use at least <?php echo PASSWORD_MIN_LENGTH; ?> characters</li>
                            <li>Include a mix of letters and numbers</li>
                            <li>Consider adding special characters</li>
                            <li>Don't use common passwords</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>