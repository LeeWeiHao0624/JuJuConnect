<?php
require_once '../config/config.php';
require_login();

$page_title = "Edit Profile";
$user_id = $_SESSION['user_id'];
$user = get_user_by_id($pdo, $user_id);
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid security token.";
    } else {
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $bio = sanitize_input($_POST['bio'] ?? '');
        
        // Validate
        if (empty($full_name) || empty($phone)) {
            $error = "Name and phone are required.";
        } elseif (!validate_phone($phone)) {
            $error = "Invalid phone number format.";
        } else {
            // Handle profile picture upload
            $profile_picture = $user['ProfilePicture'];
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $upload_result = upload_profile_picture($_FILES['profile_picture']);
                if ($upload_result['success']) {
                    $profile_picture = $upload_result['filename'];
                } else {
                    $error = $upload_result['message'];
                }
            }
            
            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE Users 
                        SET FullName = ?, Phone = ?, Bio = ?, ProfilePicture = ?
                        WHERE UserID = ?
                    ");
                    
                    $stmt->execute([$full_name, $phone, $bio, $profile_picture, $user_id]);
                    
                    // Update session
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['profile_picture'] = $profile_picture;
                    
                    // Log activity
                    log_activity($pdo, $user_id, 'update_profile', 'User', $user_id);
                    
                    set_flash_message('success', 'Profile updated successfully!');
                    redirect(SITE_URL . '/profile/view.php?id=' . $user_id);
                    
                } catch (PDOException $e) {
                    error_log("Update profile error: " . $e->getMessage());
                    $error = "Failed to update profile. Please try again.";
                }
            }
        }
    }
    
    // Refresh user data
    $user = get_user_by_id($pdo, $user_id);
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile</h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        
                        <!-- Profile Picture -->
                        <div class="text-center mb-4">
                            <img src="<?php echo get_profile_picture($user['ProfilePicture']); ?>" 
                                 class="rounded-circle mb-3" width="150" height="150" alt="Profile" id="profile-pic-preview">
                            <div>
                                <label for="profile_picture" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-camera me-2"></i>Change Photo
                                </label>
                                <input type="file" class="d-none" id="profile_picture" name="profile_picture" 
                                       accept="image/*" onchange="previewImage(this)">
                                <div class="small text-muted mt-2">Max 5MB (JPG, PNG, GIF, WEBP)</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo e($user['FullName']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo e($user['Phone']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?php echo e($user['Email']); ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo e($user['Role']); ?>" disabled>
                            <small class="text-muted">Role cannot be changed after registration</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" 
                                      placeholder="Tell us about yourself..."><?php echo e($user['Bio']); ?></textarea>
                            <small class="text-muted">Share your carpooling preferences, interests, etc.</small>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Account Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Eco Points:</strong> <?php echo number_format($user['EcoPoints']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Rating:</strong> <?php echo format_rating($user['Rating']); ?> / 5.0</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Account Status:</strong> 
                                    <span class="badge bg-success"><?php echo e($user['Status']); ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Member Since:</strong> <?php echo format_date($user['CreatedAt'], 'M Y'); ?></p>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="view.php?id=<?php echo $user_id; ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card shadow border-0 mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">For security reasons, changing password requires additional verification.</p>
                    <a href="change-password.php" class="btn btn-warning">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Check file size (5MB)
        if (file.size > 5242880) {
            alert('File too large. Maximum size is 5MB.');
            input.value = '';
            return;
        }
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-pic-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php include '../includes/footer.php'; ?>

