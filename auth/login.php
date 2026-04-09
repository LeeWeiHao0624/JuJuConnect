<?php
require_once '../config/config.php';

$page_title = "Login";
$error = '';
$success = '';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(SITE_URL . '/dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!validate_email($email)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check user credentials
        $user = get_user_by_email($pdo, $email);
        
        if ($user && verify_password($password, $user['Password'])) {
            // Check if account is active
            if ($user['Status'] !== 'Active') {
                $error = "Your account has been " . strtolower($user['Status']) . ". Please contact support.";
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['full_name'] = $user['FullName'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['role'] = $user['Role'];
                $_SESSION['profile_picture'] = $user['ProfilePicture'];
                
                // Update last login
                update_last_login($pdo, $user['UserID']);
                
                // Log activity
                log_activity($pdo, $user['UserID'], 'login', 'User', $user['UserID']);
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = generate_token();
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                }
                
                // Redirect to intended page or dashboard
                $redirect_url = $_SESSION['redirect_url'] ?? SITE_URL . '/dashboard.php';
                unset($_SESSION['redirect_url']);
                redirect($redirect_url);
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-leaf fa-3x text-success mb-3"></i>
                        <h2 class="fw-bold">Welcome Back!</h2>
                        <p class="text-muted">Login to your <?php echo SITE_NAME; ?> account</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['registered'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Registration successful! Please login.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo e($email ?? ''); ?>" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? 
                            <a href="register.php" class="text-success fw-bold">Sign Up</a>
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

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all fields');
    }
});
</script>

<?php include '../includes/footer.php'; ?>

