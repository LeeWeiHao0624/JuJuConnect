<?php
/**
 * JuJuConnect - Moderator Review Reports
 * Handle and review user reports
 */

require_once '../config/config.php';

// Check if user is moderator
require_role('Moderator');

$page_title = 'Review Reports';
$user_id = $_SESSION['user_id'];

// Handle report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $report_id = intval($_POST['report_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($report_id > 0) {
        try {
            switch ($action) {
                case 'resolve':
                    $stmt = $pdo->prepare("UPDATE Reports SET Status = 'Resolved', ResolvedAt = NOW() WHERE ReportID = ?");
                    $stmt->execute([$report_id]);
                    set_flash_message('success', 'Report marked as resolved.');
                    break;
                    
                case 'review':
                    $stmt = $pdo->prepare("UPDATE Reports SET Status = 'Under Review' WHERE ReportID = ?");
                    $stmt->execute([$report_id]);
                    set_flash_message('info', 'Report is now under review.');
                    break;
                    
                case 'suspend_user':
                    $reported_user_id = intval($_POST['reported_user_id'] ?? 0);
                    if ($reported_user_id > 0) {
                        $stmt = $pdo->prepare("UPDATE Users SET Status = 'Suspended' WHERE UserID = ?");
                        $stmt->execute([$reported_user_id]);
                        set_flash_message('warning', 'User has been suspended.');
                        
                        // Log moderation action
                        log_activity($pdo, $user_id, 'moderator_suspend_user', 'User', $reported_user_id);
                    }
                    break;
            }
            
            log_activity($pdo, $user_id, "moderator_{$action}_report", 'Report', $report_id);
            
        } catch (PDOException $e) {
            error_log("Moderator report action error: " . $e->getMessage());
            set_flash_message('error', 'Failed to perform action.');
        }
    }
    
    redirect(SITE_URL . '/moderator/review-reports.php');
}

// Get reports
$filter_status = $_GET['status'] ?? 'pending';
$where_sql = '';
$params = [];

if ($filter_status === 'pending') {
    $where_sql = "WHERE r.Status = 'Pending'";
} elseif ($filter_status === 'review') {
    $where_sql = "WHERE r.Status = 'Under Review'";
} elseif ($filter_status === 'resolved') {
    $where_sql = "WHERE r.Status = 'Resolved'";
}

$stmt = $pdo->prepare("
    SELECT r.*, 
           reporter.FullName as ReporterName,
           reporter.Email as ReporterEmail,
           reported.FullName as ReportedUserName,
           reported.Email as ReportedEmail,
           reported.Status as ReportedUserStatus,
           ride.OriginLocation, ride.DestinationLocation
    FROM Reports r
    LEFT JOIN Users reporter ON r.ReporterID = reporter.UserID
    LEFT JOIN Users reported ON r.ReportedUserID = reported.UserID
    LEFT JOIN Rides ride ON r.RideID = ride.RideID
    $where_sql
    ORDER BY r.CreatedAt DESC
    LIMIT 50
");
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN Status = 'Pending' THEN 1 END) as pending,
        COUNT(CASE WHEN Status = 'Under Review' THEN 1 END) as under_review,
        COUNT(CASE WHEN Status = 'Resolved' THEN 1 END) as resolved
    FROM Reports
");
$stats = $stats_stmt->fetch();

include '../includes/header.php';
?>

<div class="container my-5">
    <?php display_flash_message(); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-flag text-warning"></i> Review Reports</h1>
            <p class="text-muted mb-0">Handle user safety and conduct reports</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                    <small class="text-muted">Total Reports</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h4 class="mb-0 text-warning"><?php echo $stats['pending']; ?></h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h4 class="mb-0 text-info"><?php echo $stats['under_review']; ?></h4>
                    <small class="text-muted">Under Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h4 class="mb-0 text-success"><?php echo $stats['resolved']; ?></h4>
                    <small class="text-muted">Resolved</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $filter_status === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                <i class="fas fa-clock"></i> Pending (<?php echo $stats['pending']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter_status === 'review' ? 'active' : ''; ?>" href="?status=review">
                <i class="fas fa-search"></i> Under Review (<?php echo $stats['under_review']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter_status === 'resolved' ? 'active' : ''; ?>" href="?status=resolved">
                <i class="fas fa-check"></i> Resolved (<?php echo $stats['resolved']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter_status === 'all' ? 'active' : ''; ?>" href="?status=all">
                <i class="fas fa-list"></i> All (<?php echo $stats['total']; ?>)
            </a>
        </li>
    </ul>

    <!-- Reports List -->
    <?php foreach ($reports as $report): ?>
        <?php
        $status_class = '';
        switch ($report['Status']) {
            case 'Pending': $status_class = 'warning'; break;
            case 'Under Review': $status_class = 'info'; break;
            case 'Resolved': $status_class = 'success'; break;
        }
        ?>
        <div class="card shadow-sm mb-3 border-<?php echo $status_class; ?>">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between mb-2">
                            <h5 class="mb-0">
                                <i class="fas fa-flag me-2"></i>Report #<?php echo $report['ReportID']; ?>
                            </h5>
                            <span class="badge bg-<?php echo $status_class; ?>">
                                <?php echo e($report['Status']); ?>
                            </span>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Reporter:</strong><br>
                                <?php echo e($report['ReporterName']); ?><br>
                                <small class="text-muted"><?php echo e($report['ReporterEmail']); ?></small>
                            </div>
                            <div class="col-md-6">
                                <strong>Reported User:</strong><br>
                                <?php echo e($report['ReportedUserName']); ?>
                                <span class="badge bg-<?php echo $report['ReportedUserStatus'] === 'Active' ? 'success' : 'danger'; ?> ms-2">
                                    <?php echo $report['ReportedUserStatus']; ?>
                                </span><br>
                                <small class="text-muted"><?php echo e($report['ReportedEmail']); ?></small>
                            </div>
                        </div>
                        
                        <?php if ($report['RideID']): ?>
                            <div class="mb-2">
                                <strong><i class="fas fa-car me-1"></i>Related Ride:</strong> 
                                <?php echo e($report['OriginLocation']); ?> → <?php echo e($report['DestinationLocation']); ?>
                                <a href="<?php echo SITE_URL; ?>/rides/view.php?id=<?php echo $report['RideID']; ?>" 
                                   class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-light mb-2">
                            <strong><i class="fas fa-comment me-1"></i>Report Reason:</strong><br>
                            <?php echo nl2br(e($report['Reason'])); ?>
                        </div>
                        
                        <small class="text-muted">
                            <i class="far fa-clock"></i> Reported <?php echo time_ago($report['CreatedAt']); ?>
                            <?php if ($report['ResolvedAt']): ?>
                                • Resolved <?php echo time_ago($report['ResolvedAt']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="d-grid gap-2">
                            <?php if ($report['Status'] !== 'Resolved'): ?>
                                <!-- Mark Under Review -->
                                <?php if ($report['Status'] === 'Pending'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                        <input type="hidden" name="action" value="review">
                                        <button type="submit" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-search"></i> Start Review
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Suspend User -->
                                <?php if ($report['ReportedUserStatus'] === 'Active'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                        <input type="hidden" name="reported_user_id" value="<?php echo $report['ReportedUserID']; ?>">
                                        <input type="hidden" name="action" value="suspend_user">
                                        <button type="submit" class="btn btn-warning btn-sm w-100"
                                                onclick="return confirm('Suspend this user? They will not be able to use the platform.');">
                                            <i class="fas fa-pause"></i> Suspend User
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Resolve Report -->
                                <form method="POST">
                                    <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                    <input type="hidden" name="action" value="resolve">
                                    <button type="submit" class="btn btn-success btn-sm w-100"
                                            onclick="return confirm('Mark this report as resolved?');">
                                        <i class="fas fa-check"></i> Resolve
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <!-- View User Profile -->
                            <a href="<?php echo SITE_URL; ?>/profile/view.php?id=<?php echo $report['ReportedUserID']; ?>" 
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-user"></i> View Profile
                            </a>
                            
                            <!-- View Moderation Logs -->
                            <a href="moderation-logs.php?user_id=<?php echo $report['ReportedUserID']; ?>" 
                               class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-history"></i> View History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($reports)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Reports Found</h4>
            <p class="text-muted">
                <?php if ($filter_status === 'pending'): ?>
                    There are no pending reports at the moment.
                <?php elseif ($filter_status === 'review'): ?>
                    No reports are currently under review.
                <?php else: ?>
                    No reports match your filter.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

