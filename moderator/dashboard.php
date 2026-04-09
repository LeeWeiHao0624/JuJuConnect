<?php
/**
 * JuJuConnect - Moderator Dashboard
 * Overview of moderation activities
 */

require_once '../config/config.php';

// Check if user is moderator
require_role('Moderator');

$page_title = "Moderator Dashboard";
$user_id = $_SESSION['user_id'];

try {
    // Get pending reports
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM Reports WHERE Status IN ('Pending', 'Under Review')
    ");
    $pending_reports = $stmt->fetchColumn();
    
    // Get today's rides needing review
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM Rides 
        WHERE DepartureDate >= CURDATE() AND Status = 'Available'
    ");
    $active_rides = $stmt->fetchColumn();
    
    // Get recent reports
    $stmt = $pdo->query("
        SELECT r.*, 
               reporter.FullName as ReporterName,
               reported.FullName as ReportedUserName
        FROM Reports r
        LEFT JOIN Users reporter ON r.ReporterID = reporter.UserID
        LEFT JOIN Users reported ON r.ReportedUserID = reported.UserID
        WHERE r.Status IN ('Pending', 'Under Review')
        ORDER BY r.CreatedAt DESC
        LIMIT 10
    ");
    $recent_reports = $stmt->fetchAll();
    
    // Get recent rides
    $stmt = $pdo->query("
        SELECT r.*, u.FullName as DriverName
        FROM Rides r
        JOIN Users u ON r.DriverID = u.UserID
        WHERE r.DepartureDate >= CURDATE()
        ORDER BY r.CreatedAt DESC
        LIMIT 10
    ");
    $recent_rides = $stmt->fetchAll();
    
    // Get moderation stats
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_reports,
            COUNT(CASE WHEN Status = 'Pending' THEN 1 END) as pending,
            COUNT(CASE WHEN Status = 'Under Review' THEN 1 END) as under_review,
            COUNT(CASE WHEN Status = 'Resolved' THEN 1 END) as resolved
        FROM Reports
    ");
    $report_stats = $stmt->fetch();
    
    // Get user stats
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN Status = 'Active' THEN 1 END) as active,
            COUNT(CASE WHEN Status = 'Suspended' THEN 1 END) as suspended,
            COUNT(CASE WHEN Status = 'Banned' THEN 1 END) as banned
        FROM Users
    ");
    $user_stats = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Moderator dashboard error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shield-alt me-2 text-primary"></i>Moderator Dashboard</h2>
        <div>
            <a href="review-reports.php" class="btn btn-warning me-2">
                <i class="fas fa-flag me-2"></i>Review Reports
                <?php if ($pending_reports > 0): ?>
                    <span class="badge bg-danger"><?php echo $pending_reports; ?></span>
                <?php endif; ?>
            </a>
            <a href="review-rides.php" class="btn btn-info me-2">
                <i class="fas fa-car me-2"></i>Review Rides
            </a>
            <a href="moderation-logs.php" class="btn btn-secondary">
                <i class="fas fa-history me-2"></i>Moderation Logs
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Pending Reports</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $pending_reports; ?></h2>
                        </div>
                        <i class="fas fa-flag fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75">Need attention</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Active Rides</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $active_rides; ?></h2>
                        </div>
                        <i class="fas fa-car fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75">To monitor</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Resolved</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $report_stats['resolved']; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75">Total reports</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Active Users</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $user_stats['active']; ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                    <small class="opacity-75">On platform</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="row">
        <!-- Recent Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-flag me-2"></i>Recent Reports</h5>
                    <a href="review-reports.php" class="btn btn-sm btn-dark">View All</a>
                </div>
                <div class="card-body p-0" style="min-height: 400px;">
                    <?php if (count($recent_reports) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_reports as $report): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Report #<?php echo $report['ReportID']; ?></h6>
                                            <small class="text-muted">
                                                <strong><?php echo e($report['ReporterName']); ?></strong> reported 
                                                <strong><?php echo e($report['ReportedUserName']); ?></strong>
                                            </small>
                                            <p class="mb-1 small"><?php echo substr(e($report['Reason']), 0, 80); ?>...</p>
                                            <small class="text-muted"><?php echo time_ago($report['CreatedAt']); ?></small>
                                        </div>
                                        <span class="badge bg-<?php echo $report['Status'] === 'Pending' ? 'warning' : 'info'; ?>">
                                            <?php echo $report['Status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-2"></i>
                            <p>No pending reports</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Rides -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-car me-2"></i>Recent Rides</h5>
                    <a href="review-rides.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0" style="min-height: 400px;">
                    <?php if (count($recent_rides) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_rides as $ride): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo e($ride['DriverName']); ?></h6>
                                            <small class="text-success"><i class="fas fa-map-marker-alt"></i> <?php echo e($ride['OriginLocation']); ?></small><br>
                                            <small class="text-danger"><i class="fas fa-map-marker-alt"></i> <?php echo e($ride['DestinationLocation']); ?></small>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar"></i> <?php echo format_date($ride['DepartureDate']); ?> 
                                                    <i class="far fa-clock ms-2"></i> <?php echo format_time($ride['DepartureTime']); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo $ride['Status'] === 'Available' ? 'info' : 'secondary'; ?>">
                                                <?php echo $ride['Status']; ?>
                                            </span>
                                            <div class="mt-2">
                                                <a href="<?php echo SITE_URL; ?>/rides/view.php?id=<?php echo $ride['RideID']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-car fa-3x mb-2"></i>
                            <p>No recent rides</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="card shadow border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Platform Overview</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Report Status</h6>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Pending</span>
                            <span><?php echo $report_stats['pending']; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: <?php echo $report_stats['total_reports'] > 0 ? ($report_stats['pending']/$report_stats['total_reports'])*100 : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Under Review</span>
                            <span><?php echo $report_stats['under_review']; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: <?php echo $report_stats['total_reports'] > 0 ? ($report_stats['under_review']/$report_stats['total_reports'])*100 : 0; ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Resolved</span>
                            <span><?php echo $report_stats['resolved']; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: <?php echo $report_stats['total_reports'] > 0 ? ($report_stats['resolved']/$report_stats['total_reports'])*100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h6>User Status</h6>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Active</span>
                            <span><?php echo $user_stats['active']; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: <?php echo $user_stats['total'] > 0 ? ($user_stats['active']/$user_stats['total'])*100 : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Suspended</span>
                            <span><?php echo $user_stats['suspended']; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: <?php echo $user_stats['total'] > 0 ? ($user_stats['suspended']/$user_stats['total'])*100 : 0; ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Banned</span>
                            <span><?php echo $user_stats['banned']; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" style="width: <?php echo $user_stats['total'] > 0 ? ($user_stats['banned']/$user_stats['total'])*100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

