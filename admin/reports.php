<?php
/**
 * JuJuConnect - Admin Reports Management
 * View and handle user reports
 */

require_once '../config/config.php';

// Check if user is admin
require_role('Admin');

$page_title = 'User Reports';

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
            }
            
            log_activity($pdo, $_SESSION['user_id'], "admin_{$action}_report", 'Report', $report_id);
            
        } catch (PDOException $e) {
            error_log("Admin report action error: " . $e->getMessage());
            set_flash_message('error', 'Failed to perform action.');
        }
    }
    
    redirect(SITE_URL . '/admin/reports.php');
}

// Get reports
$filter_status = $_GET['status'] ?? 'all';
$where_sql = '';
$params = [];

if ($filter_status !== 'all') {
    $where_sql = "WHERE r.Status = ?";
    $params[] = $filter_status;
}

$stmt = $pdo->prepare("
    SELECT r.*, 
           reporter.FullName as ReporterName,
           reported.FullName as ReportedUserName,
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
            <h1><i class="fas fa-flag text-warning"></i> User Reports</h1>
            <p class="text-muted mb-0">Manage safety and conduct reports</p>
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
            <a class="nav-link <?php echo $filter_status === 'all' ? 'active' : ''; ?>" href="?status=all">
                All (<?php echo $stats['total']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter_status === 'Pending' ? 'active' : ''; ?>" href="?status=Pending">
                Pending (<?php echo $stats['pending']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter_status === 'Under Review' ? 'active' : ''; ?>" href="?status=Under Review">
                Under Review (<?php echo $stats['under_review']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter_status === 'Resolved' ? 'active' : ''; ?>" href="?status=Resolved">
                Resolved (<?php echo $stats['resolved']; ?>)
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
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between mb-2">
                            <h5 class="mb-0">Report #<?php echo $report['ReportID']; ?></h5>
                            <span class="badge bg-<?php echo $status_class; ?>">
                                <?php echo e($report['Status']); ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Reporter:</strong> <?php echo e($report['ReporterName']); ?><br>
                            <strong>Reported User:</strong> <?php echo e($report['ReportedUserName']); ?>
                        </div>
                        <?php if ($report['RideID']): ?>
                            <div class="mb-2">
                                <strong>Related Ride:</strong> 
                                <?php echo e($report['OriginLocation']); ?> → <?php echo e($report['DestinationLocation']); ?>
                                <a href="<?php echo SITE_URL; ?>/rides/view.php?id=<?php echo $report['RideID']; ?>" 
                                   class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-eye"></i> View Ride
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="alert alert-light mb-0">
                            <strong>Reason:</strong><br>
                            <?php echo nl2br(e($report['Reason'])); ?>
                        </div>
                        <small class="text-muted">
                            Reported <?php echo time_ago($report['CreatedAt']); ?>
                            <?php if ($report['ResolvedAt']): ?>
                                • Resolved <?php echo time_ago($report['ResolvedAt']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if ($report['Status'] !== 'Resolved'): ?>
                            <form method="POST" class="mb-2">
                                <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                <input type="hidden" name="action" value="review">
                                <button type="submit" class="btn btn-info btn-sm w-100">
                                    <i class="fas fa-search"></i> Mark Under Review
                                </button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                <input type="hidden" name="action" value="resolve">
                                <button type="submit" class="btn btn-success btn-sm w-100" 
                                        onclick="return confirm('Mark this report as resolved?');">
                                    <i class="fas fa-check"></i> Resolve
                                </button>
                            </form>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/profile/view.php?id=<?php echo $report['ReportedUserID']; ?>" 
                           class="btn btn-outline-secondary btn-sm w-100 mt-2">
                            <i class="fas fa-user"></i> View User Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($reports)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Reports Found</h4>
            <p class="text-muted">There are no reports matching your filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

