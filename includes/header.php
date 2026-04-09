<?php
if (!isset($page_title)) {
    $page_title = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JuJuConnect - Sustainable Carpooling Platform for APU Community">
    <meta name="keywords" content="carpooling, rideshare, sustainable transport, eco-friendly">
    <title><?php echo e($page_title); ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>/index.php">
                <i class="fas fa-leaf me-2"></i>
                <strong><?php echo SITE_NAME; ?></strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <!-- Logged in menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/dashboard.php">
                                <i class="fas fa-dashboard me-1"></i> Dashboard
                            </a>
                        </li>
                        
                        <?php if (has_role('Admin')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cog me-1"></i> Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/users.php">
                                        <i class="fas fa-users me-2"></i> Manage Users
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/rides.php">
                                        <i class="fas fa-car me-2"></i> Manage Rides
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/reports.php">
                                        <i class="fas fa-flag me-2"></i> View Reports
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings.php">
                                        <i class="fas fa-wrench me-2"></i> System Settings
                                    </a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (has_any_role(['Driver', 'Passenger'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/rides/search.php">
                                    <i class="fas fa-search me-1"></i> Find Rides
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (has_any_role(['Driver'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/rides/create.php">
                                    <i class="fas fa-plus-circle me-1"></i> Offer Ride
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/rides/requests.php">
                                    <i class="fas fa-user-check me-1"></i> Requests
                                    <?php 
                                    $pending_requests_stmt = $pdo->prepare("
                                        SELECT COUNT(*) 
                                        FROM RideRequests rr
                                        JOIN Rides r ON rr.RideID = r.RideID
                                        WHERE r.DriverID = ? AND rr.Status = 'Pending'
                                    ");
                                    $pending_requests_stmt->execute([$_SESSION['user_id']]);
                                    $pending_requests_count = $pending_requests_stmt->fetchColumn();
                                    if ($pending_requests_count > 0): 
                                    ?>
                                        <span class="badge bg-danger"><?php echo $pending_requests_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/leaderboard.php">
                                <i class="fas fa-trophy me-1"></i> Leaderboard
                            </a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php 
                                $unread_count = get_unread_notifications_count($pdo, $_SESSION['user_id']);
                                if ($unread_count > 0): 
                                ?>
                                    <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/notifications.php">
                                    <i class="fas fa-bell me-2"></i>View All Notifications
                                </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo get_profile_picture($_SESSION['profile_picture'] ?? 'default-avatar.png'); ?>" 
                                     alt="Profile" class="rounded-circle" width="30" height="30">
                                <?php echo e($_SESSION['full_name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile/view.php?id=<?php echo $_SESSION['user_id']; ?>">
                                    <i class="fas fa-user me-2"></i> My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile/edit.php">
                                    <i class="fas fa-edit me-2"></i> Edit Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile/change-password.php">
                                    <i class="fas fa-key me-2"></i> Change Password
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/index.php">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/about.php">
                                <i class="fas fa-info-circle me-1"></i> About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2 px-3" href="<?php echo SITE_URL; ?>/auth/register.php">
                                <i class="fas fa-user-plus me-1"></i> Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">