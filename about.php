<?php
require_once 'config/config.php';
$page_title = "About Us";
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">About <?php echo SITE_NAME; ?></h1>
        <p class="lead text-muted">Building a sustainable future through shared transportation</p>
    </div>
    
    <!-- Mission Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h2 class="mb-3"><i class="fas fa-bullseye text-success me-2"></i>Our Mission</h2>
            <p class="lead">
                To create a sustainable transportation ecosystem within the APU community by connecting 
                drivers and passengers, reducing carbon emissions, and promoting eco-friendly commuting habits.
            </p>
            <p>
                <?php echo SITE_NAME; ?> is more than just a carpooling platform—it's a movement towards 
                environmental responsibility. We believe that small changes in our daily commute can lead 
                to significant positive impacts on our planet.
            </p>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg bg-success text-white">
                <div class="card-body p-5 text-center">
                    <i class="fas fa-leaf fa-5x mb-4"></i>
                    <h3 class="mb-3">Together We Make a Difference</h3>
                    <p>Every shared ride is a step towards a greener future</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Values Section -->
    <div class="mb-5">
        <h2 class="text-center mb-4"><i class="fas fa-heart text-danger me-2"></i>Our Core Values</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-leaf fa-2x text-success"></i>
                        </div>
                        <h4>Sustainability</h4>
                        <p class="text-muted">
                            We prioritize environmental conservation and promote eco-friendly transportation choices.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <h4>Community</h4>
                        <p class="text-muted">
                            Building a strong, connected APU community through shared experiences and mutual support.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fa-2x text-warning"></i>
                        </div>
                        <h4>Safety & Trust</h4>
                        <p class="text-muted">
                            Ensuring a safe and trustworthy platform through ratings, verification, and community moderation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Impact Section -->
    <div class="card border-0 shadow-lg bg-gradient-success text-white mb-5">
        <div class="card-body p-5">
            <h2 class="text-center mb-4">Our Environmental Impact</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <i class="fas fa-car fa-3x mb-3"></i>
                    <h3 class="fw-bold">Fewer Cars</h3>
                    <p>Reducing traffic congestion on campus and surrounding areas</p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <i class="fas fa-smog fa-3x mb-3"></i>
                    <h3 class="fw-bold">Lower Emissions</h3>
                    <p>Decreasing carbon footprint through shared transportation</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-tree fa-3x mb-3"></i>
                    <h3 class="fw-bold">Greener Future</h3>
                    <p>Contributing to a sustainable environment for generations to come</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="mb-5">
        <h2 class="text-center mb-4"><i class="fas fa-star text-warning me-2"></i>Platform Features</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div>
                        <h5>Easy Ride Matching</h5>
                        <p class="text-muted">Smart algorithm to connect drivers and passengers with similar routes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div>
                        <h5>Eco-Points Rewards</h5>
                        <p class="text-muted">Gamification system to encourage sustainable behavior</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div>
                        <h5>User Ratings & Reviews</h5>
                        <p class="text-muted">Build trust through transparent feedback system</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div>
                        <h5>Safety Features</h5>
                        <p class="text-muted">Emergency contacts and user verification for peace of mind</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Team Section (Optional) -->
    <div class="mb-5">
        <h2 class="text-center mb-4"><i class="fas fa-users text-primary me-2"></i>Development Team</h2>
        <p class="text-center text-muted mb-4">
            This project was developed by students of Asia Pacific University as part of the 
            Responsive Web Design & Development course (RWDD).
        </p>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Choong Yin Hang</strong></p>
                        <small class="text-muted">TP082795 - Group Leader</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Liew Chen Yau</strong></p>
                        <small class="text-muted">TP083359</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Khor Thung Nuok</strong></p>
                        <small class="text-muted">TP082055</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Lee Wei Hao</strong></p>
                        <small class="text-muted">TP082970</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Javion Leong Miu Ngai</strong></p>
                        <small class="text-muted">TP083045</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Kong Jen Wei</strong></p>
                        <small class="text-muted">TP080718</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="text-center">
        <div class="card border-success">
            <div class="card-body p-5">
                <h3 class="mb-3">Join the Green Movement!</h3>
                <p class="lead mb-4">Be part of the solution. Start carpooling today!</p>
                <?php if (!is_logged_in()): ?>
                    <a href="auth/register.php" class="btn btn-success btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Sign Up Now
                    </a>
                <?php else: ?>
                    <a href="rides/search.php" class="btn btn-success btn-lg me-2">
                        <i class="fas fa-search me-2"></i>Find Rides
                    </a>
                    <a href="rides/create.php" class="btn btn-outline-success btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>Offer a Ride
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

