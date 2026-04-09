<?php
require_once '../config/config.php';
require_login();

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    set_flash_message('error', 'Invalid user ID.');
    redirect(SITE_URL . '/dashboard.php');
}

// Get user details
$user = get_user_by_id($pdo, $user_id);

if (!$user || $user['Status'] === 'Banned') {
    set_flash_message('error', 'User not found.');
    redirect(SITE_URL . '/dashboard.php');
}

$page_title = $user['FullName'] . "'s Profile";
$is_own_profile = ($user_id == $_SESSION['user_id']);

// Get user statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT CASE WHEN Role = 'Driver' THEN RideID END) as total_drives,
        COUNT(DISTINCT CASE WHEN Role = 'Passenger' THEN RideID END) as total_rides,
        COALESCE(SUM(EcoPointsEarned), 0) as total_points_earned,
        COALESCE(SUM(CO2Saved), 0) as total_co2_saved
    FROM RideHistory 
    WHERE UserID = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Get recent achievements
$stmt = $pdo->prepare("
    SELECT * FROM Achievements 
    WHERE UserID = ? 
    ORDER BY EarnedAt DESC 
    LIMIT 6
");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll();

// Get recent ratings
$stmt = $pdo->prepare("
    SELECT r.*, u.FullName as RaterName, rides.OriginLocation, rides.DestinationLocation
    FROM Ratings r
    JOIN Users u ON r.RaterID = u.UserID
    JOIN Rides rides ON r.RideID = rides.RideID
    WHERE r.RatedUserID = ?
    ORDER BY r.CreatedAt DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$ratings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <?php display_flash_message(); ?>
    
    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0">
                <div class="card-body text-center">
                    <img src="<?php echo get_profile_picture($user['ProfilePicture']); ?>" 
                         class="rounded-circle mb-3" width="150" height="150" alt="Profile">
                    
                    <h3 class="mb-1"><?php echo e($user['FullName']); ?></h3>
                    
                    <div class="mb-2">
                        <span class="badge bg-<?php echo $user['Role'] === 'Driver' ? 'success' : 'info'; ?> fs-6">
                            <?php echo e($user['Role']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <?php echo display_star_rating($user['Rating']); ?>
                        <div class="small text-muted mt-1">
                            Based on <?php echo $user['TotalRatings']; ?> rating<?php echo $user['TotalRatings'] != 1 ? 's' : ''; ?>
                        </div>
                    </div>
                    
                    <?php if ($user['Bio']): ?>
                        <p class="text-muted"><?php echo nl2br(e($user['Bio'])); ?></p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <h5 class="text-success mb-0"><?php echo number_format($user['EcoPoints']); ?></h5>
                            <small class="text-muted">Eco Points</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-primary mb-0"><?php echo $stats['total_drives'] + $stats['total_rides']; ?></h5>
                            <small class="text-muted">Total Rides</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-warning mb-0"><?php echo $stats['total_drives']; ?></h5>
                            <small class="text-muted">As Driver</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-info mb-0"><?php echo $stats['total_rides']; ?></h5>
                            <small class="text-muted">As Passenger</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <?php if ($is_own_profile): ?>
                            <a href="edit.php" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                        <?php else: ?>
                            <a href="mailto:<?php echo e($user['Email']); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i>Send Message
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <small class="text-muted d-block mt-3">
                        <i class="fas fa-calendar me-1"></i>
                        Joined <?php echo format_date($user['CreatedAt'], 'M Y'); ?>
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Environmental Impact -->
            <div class="card shadow border-0 mb-4 bg-success text-white">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-leaf me-2"></i>Environmental Impact</h5>
                    <div class="row text-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_co2_saved'], 1); ?> kg</h2>
                            <p class="mb-0 opacity-75">CO₂ Saved</p>
                        </div>
                        <div class="col-md-6">
                            <h2 class="fw-bold mb-0"><?php echo ceil($stats['total_co2_saved'] / 20); ?></h2>
                            <p class="mb-0 opacity-75">Trees Equivalent 🌳</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Achievements -->
            <?php if (!empty($achievements)): ?>
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Recent Achievements</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($achievements as $achievement): ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="text-center">
                                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-award fa-2x text-warning"></i>
                                        </div>
                                        <h6 class="mb-1"><?php echo e($achievement['AchievementName']); ?></h6>
                                        <small class="text-muted"><?php echo time_ago($achievement['EarnedAt']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Ratings & Reviews -->
            <div class="card shadow border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Ratings & Reviews</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ratings)): ?>
                        <p class="text-muted text-center py-4">No ratings yet</p>
                    <?php else: ?>
                        <?php foreach ($ratings as $rating): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong><?php echo e($rating['RaterName']); ?></strong>
                                        <div class="small">
                                            <?php echo display_star_rating($rating['Rating']); ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo time_ago($rating['CreatedAt']); ?></small>
                                </div>
                                
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-route me-1"></i>
                                    <?php echo e(substr($rating['OriginLocation'], 0, 30)); ?> → 
                                    <?php echo e(substr($rating['DestinationLocation'], 0, 30)); ?>
                                </p>
                                
                                <?php if ($rating['Review']): ?>
                                    <p class="mb-0"><?php echo e($rating['Review']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

