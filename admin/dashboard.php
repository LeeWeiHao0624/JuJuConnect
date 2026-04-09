<?php
require_once '../config/config.php';
require_role('Admin');

$page_title = "Admin Dashboard";

// Get statistics
try {
    // User statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN Status = 'Active' THEN 1 END) as active,
            COUNT(CASE WHEN Role = 'Driver' THEN 1 END) as drivers,
            COUNT(CASE WHEN Role = 'Passenger' THEN 1 END) as passengers
        FROM Users
    ");
    $user_stats = $stmt->fetch();
    
    // Ride statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN Status = 'Completed' THEN 1 END) as completed,
            COUNT(CASE WHEN Status = 'Available' THEN 1 END) as available,
            COUNT(CASE WHEN Status = 'Cancelled' THEN 1 END) as cancelled
        FROM Rides
    ");
    $ride_stats = $stmt->fetch();
    
    // Environmental impact
    $stmt = $pdo->query("
        SELECT 
            COALESCE(SUM(CO2Saved), 0) as total_co2,
            COALESCE(SUM(EcoPointsEarned), 0) as total_points
        FROM RideHistory
    ");
    $env_stats = $stmt->fetch();
    
    // Recent activities
    $stmt = $pdo->query("
        SELECT al.*, u.FullName
        FROM ActivityLogs al
        LEFT JOIN Users u ON al.UserID = u.UserID
        ORDER BY al.CreatedAt DESC
        LIMIT 10
    ");
    $recent_activities = $stmt->fetchAll();
    
    // Pending reports
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM Reports WHERE Status IN ('Pending', 'Under Review')
    ");
    $pending_reports = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
        <div>
            <a href="users.php" class="btn btn-primary me-2">
                <i class="fas fa-users me-2"></i>Manage Users
            </a>
            <a href="reports.php" class="btn btn-warning">
                <i class="fas fa-flag me-2"></i>Reports 
                <?php if ($pending_reports > 0): ?>
                    <span class="badge bg-danger"><?php echo $pending_reports; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Total Users</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($user_stats['total']); ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75"><?php echo $user_stats['active']; ?> active</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Total Rides</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($ride_stats['total']); ?></h2>
                        </div>
                        <i class="fas fa-car fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75"><?php echo $ride_stats['completed']; ?> completed</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">CO₂ Saved</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($env_stats['total_co2'], 1); ?></h2>
                        </div>
                        <i class="fas fa-leaf fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75">kilograms</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Eco-Points</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($env_stats['total_points']); ?></h2>
                        </div>
                        <i class="fas fa-trophy fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75">total earned</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Recent Activity -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>System Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>User Distribution</h6>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Drivers</span>
                                    <span><?php echo $user_stats['drivers']; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?php echo ($user_stats['drivers']/$user_stats['total'])*100; ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Passengers</span>
                                    <span><?php echo $user_stats['passengers']; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: <?php echo ($user_stats['passengers']/$user_stats['total'])*100; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Ride Status</h6>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Completed</span>
                                    <span><?php echo $ride_stats['completed']; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?php echo ($ride_stats['completed']/$ride_stats['total'])*100; ?>%"></div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Available</span>
                                    <span><?php echo $ride_stats['available']; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: <?php echo ($ride_stats['available']/$ride_stats['total'])*100; ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Cancelled</span>
                                    <span><?php echo $ride_stats['cancelled']; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" style="width: <?php echo ($ride_stats['cancelled']/$ride_stats['total'])*100; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 bg-gradient-success text-white h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-globe fa-4x mb-3 opacity-75"></i>
                    <h4>Environmental Impact</h4>
                    <h2 class="fw-bold"><?php echo ceil($env_stats['total_co2'] / 20); ?> Trees</h2>
                    <p class="mb-0 opacity-75">Equivalent planted</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="card shadow border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activities as $activity): ?>
                            <tr>
                                <td><?php echo e($activity['FullName'] ?? 'System'); ?></td>
                                <td><?php echo e($activity['Action']); ?></td>
                                <td><?php echo e($activity['EntityType'] ?? ''); ?> #<?php echo e($activity['EntityID'] ?? ''); ?></td>
                                <td><?php echo time_ago($activity['CreatedAt']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

