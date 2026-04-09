<?php
require_once 'config/config.php';
$page_title = "Home - Sustainable Carpooling Platform";

// Get some statistics for display
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM Users WHERE Status = 'Active'");
    $total_users = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM Rides WHERE Status = 'Completed'");
    $total_rides = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(CO2Saved), 0) FROM RideHistory");
    $total_co2_saved = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(EcoPoints), 0) FROM Users");
    $total_eco_points = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_users = 0;
    $total_rides = 0;
    $total_co2_saved = 0;
    $total_eco_points = 0;
}
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-gradient-success text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-3 fw-bold mb-4">
                    <i class="fas fa-leaf"></i> JuJuConnect
                </h1>
                <h2 class="h3 mb-4">Ride Together, Save Together, Sustain Forever</h2>
                <p class="lead mb-4">
                    Join the APU community in reducing carbon emissions through sustainable carpooling. 
                    Share rides, save money, and earn eco-points while making a positive impact on our environment.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (!is_logged_in()): ?>
                        <a href="auth/register.php" class="btn btn-light btn-lg px-5">
                            <i class="fas fa-user-plus me-2"></i>Get Started
                        </a>
                        <a href="auth/login.php" class="btn btn-outline-light btn-lg px-5">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-light btn-lg px-5">
                            <i class="fas fa-dashboard me-2"></i>Go to Dashboard
                        </a>
                        <a href="rides/search.php" class="btn btn-outline-light btn-lg px-5">
                            <i class="fas fa-search me-2"></i>Find Rides
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/hero-illustration.svg" alt="Carpooling" class="img-fluid" 
                     onerror="this.src='assets/images/hero-placeholder.png'">
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                        <h3 class="fw-bold"><?php echo number_format($total_users); ?>+</h3>
                        <p class="text-muted mb-0">Active Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-car fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold"><?php echo number_format($total_rides); ?>+</h3>
                        <p class="text-muted mb-0">Rides Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-leaf fa-3x text-success mb-3"></i>
                        <h3 class="fw-bold"><?php echo number_format($total_co2_saved, 1); ?> kg</h3>
                        <p class="text-muted mb-0">CO₂ Saved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                        <h3 class="fw-bold"><?php echo number_format($total_eco_points); ?>+</h3>
                        <p class="text-muted mb-0">Eco-Points Earned</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose JuJuConnect?</h2>
            <p class="lead text-muted">Experience the benefits of sustainable carpooling</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                        </div>
                        <h4 class="fw-bold">Save Money</h4>
                        <p class="text-muted">Share fuel costs and reduce your daily commute expenses significantly.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-leaf fa-2x text-primary"></i>
                        </div>
                        <h4 class="fw-bold">Go Green</h4>
                        <p class="text-muted">Reduce carbon emissions and contribute to a sustainable environment.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-trophy fa-2x text-warning"></i>
                        </div>
                        <h4 class="fw-bold">Earn Rewards</h4>
                        <p class="text-muted">Collect eco-points, unlock achievements, and climb the leaderboard.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <h4 class="fw-bold">Build Community</h4>
                        <p class="text-muted">Connect with fellow APU students and staff. Make new friends!</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fa-2x text-danger"></i>
                        </div>
                        <h4 class="fw-bold">Stay Safe</h4>
                        <p class="text-muted">User ratings, reviews, and emergency contact features ensure your safety.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-mobile-alt fa-2x text-secondary"></i>
                        </div>
                        <h4 class="fw-bold">Easy to Use</h4>
                        <p class="text-muted">Simple and intuitive interface. Find or offer rides in just a few clicks.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">How It Works</h2>
            <p class="lead text-muted">Get started in three simple steps</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-number bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                        1
                    </div>
                    <h4 class="fw-bold">Sign Up</h4>
                    <p class="text-muted">Create your free account as a driver, passenger, or both!</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-number bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                        2
                    </div>
                    <h4 class="fw-bold">Find or Offer Rides</h4>
                    <p class="text-muted">Search for available rides or create your own ride offer.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-number bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                        3
                    </div>
                    <h4 class="fw-bold">Earn & Save</h4>
                    <p class="text-muted">Complete rides, earn eco-points, and make a difference!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-success text-white">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4">Ready to Start Your Sustainable Journey?</h2>
        <p class="lead mb-4">Join thousands of APU community members making a difference!</p>
        <?php if (!is_logged_in()): ?>
            <a href="auth/register.php" class="btn btn-light btn-lg px-5">
                <i class="fas fa-user-plus me-2"></i>Join Now - It's Free!
            </a>
        <?php else: ?>
            <a href="rides/search.php" class="btn btn-light btn-lg px-5">
                <i class="fas fa-search me-2"></i>Find Your Next Ride
            </a>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

