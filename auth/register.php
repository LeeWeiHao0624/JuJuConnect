<?php
require_once '../config/config.php';

$page_title = "Register";
$error = '';
$success = '';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(SITE_URL . '/dashboard.php');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'Passenger');
    $terms = isset($_POST['terms']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!validate_email($email)) {
        $error = "Please enter a valid email address.";
    } elseif (!validate_phone($phone)) {
        $error = "Please enter a valid Malaysian phone number.";
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!$terms) {
        $error = "You must agree to the terms and conditions.";
    } elseif (!in_array($role, ['Driver', 'Passenger'])) {
        $error = "Invalid role selected.";
    } else {
        // Check if email already exists
        $existing_user = get_user_by_email($pdo, $email);
        
        if ($existing_user) {
            $error = "An account with this email already exists.";
        } else {
            // Create new user
            try {
                $hashed_password = hash_password($password);
                $verification_token = generate_token();
                
                $stmt = $pdo->prepare("
                    INSERT INTO Users (FullName, Email, Password, Phone, Role, VerificationToken, Status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Active')
                ");
                
                $stmt->execute([
                    $full_name,
                    $email,
                    $hashed_password,
                    $phone,
                    $role,
                    $verification_token
                ]);
                
                $user_id = $pdo->lastInsertId();
                
                // Log activity
                log_activity($pdo, $user_id, 'register', 'User', $user_id);
                
                // In production, send verification email here
                // send_verification_email($email, $verification_token);
                
                set_flash_message('success', 'Registration successful! Please login.');
                redirect(SITE_URL . '/auth/login.php?registered=1');
                
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                        <h2 class="fw-bold">Join <?php echo SITE_NAME; ?></h2>
                        <p class="text-muted">Create your account and start carpooling sustainably!</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registerForm">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo e($full_name ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo e($email ?? ''); ?>" required>
                            </div>
                            <small class="text-muted">We'll never share your email with anyone else.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="e.g., 0123456789" value="<?php echo e($phone ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">I want to register as *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="Passenger" <?php echo ($role ?? 'Passenger') === 'Passenger' ? 'selected' : ''; ?>>
                                    Passenger - I want to find rides
                                </option>
                                <option value="Driver" <?php echo ($role ?? '') === 'Driver' ? 'selected' : ''; ?>>
                                    Driver - I want to offer rides
                                </option>
                            </select>
                            <small class="text-muted">You can change your role later in your profile.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div id="passwordMatch" class="form-text"></div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="<?php echo SITE_URL; ?>/terms.php" target="_blank">Terms of Service</a> 
                                and <a href="<?php echo SITE_URL; ?>/privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? 
                            <a href="login.php" class="text-success fw-bold">Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Check password match
document.getElementById('confirm_password').addEventListener('keyup', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (confirmPassword === '') {
        matchDiv.textContent = '';
        matchDiv.className = 'form-text';
    } else if (password === confirmPassword) {
        matchDiv.textContent = '✓ Passwords match';
        matchDiv.className = 'form-text text-success';
    } else {
        matchDiv.textContent = '✗ Passwords do not match';
        matchDiv.className = 'form-text text-danger';
    }
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function() {
    // Remove non-numeric characters
    this.value = this.value.replace(/\D/g, '');
});

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const terms = document.getElementById('terms').checked;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (!terms) {
        e.preventDefault();
        alert('Please agree to the terms and conditions');
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>

