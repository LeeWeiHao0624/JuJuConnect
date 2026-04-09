<?php
/**
 * JuJuConnect - Admin Ride Management
 * View and manage all rides on the platform
 */

require_once '../config/config.php';

// Check if user is admin
require_role('Admin');

$page_title = 'Manage Rides';

// Pagination and filtering
$items_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where_clauses = [];
$params = [];

if ($filter_status !== 'all') {
    $where_clauses[] = "r.Status = ?";
    $params[] = $filter_status;
}

if (!empty($search)) {
    $where_clauses[] = "(r.OriginLocation LIKE ? OR r.DestinationLocation LIKE ? OR u.FullName LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total count
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM Rides r
    JOIN Users u ON r.DriverID = u.UserID
    $where_sql
");
$count_stmt->execute($params);
$total_rides = $count_stmt->fetchColumn();

// Get rides
$params[] = $items_per_page;
$params[] = $offset;
$stmt = $pdo->prepare("
    SELECT r.*, u.FullName as DriverName, u.ProfilePicture as DriverPicture,
           (SELECT COUNT(*) FROM RideRequests WHERE RideID = r.RideID AND Status = 'Approved') as PassengerCount
    FROM Rides r
    JOIN Users u ON r.DriverID = u.UserID
    $where_sql
    ORDER BY r.CreatedAt DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$rides = $stmt->fetchAll();

$total_pages = ceil($total_rides / $items_per_page);

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN Status = 'Available' THEN 1 END) as available,
        COUNT(CASE WHEN Status = 'Full' THEN 1 END) as full,
        COUNT(CASE WHEN Status = 'Completed' THEN 1 END) as completed,
        COUNT(CASE WHEN Status = 'Cancelled' THEN 1 END) as cancelled
    FROM Rides
");
$stats = $stats_stmt->fetch();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-car text-success"></i> Manage Rides</h1>
            <p class="text-muted mb-0">Total: <?php echo number_format($total_rides); ?> rides</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                    <small class="text-muted">Total Rides</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h4 class="mb-0 text-info"><?php echo $stats['available']; ?></h4>
                    <small class="text-muted">Available</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h4 class="mb-0 text-success"><?php echo $stats['completed']; ?></h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h4 class="mb-0 text-danger"><?php echo $stats['cancelled']; ?></h4>
                    <small class="text-muted">Cancelled</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo e($search); ?>" placeholder="Location or driver name...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="Available" <?php echo $filter_status === 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Full" <?php echo $filter_status === 'Full' ? 'selected' : ''; ?>>Full</option>
                        <option value="In Progress" <?php echo $filter_status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo $filter_status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo $filter_status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rides List -->
    <?php foreach ($rides as $ride): ?>
        <?php
        $status_class = '';
        switch ($ride['Status']) {
            case 'Available': $status_class = 'info'; break;
            case 'Full': $status_class = 'warning'; break;
            case 'Completed': $status_class = 'success'; break;
            case 'Cancelled': $status_class = 'danger'; break;
            default: $status_class = 'secondary';
        }
        ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="<?php echo get_profile_picture($ride['DriverPicture']); ?>" 
                             class="rounded-circle" width="60" height="60" alt="Driver">
                        <div class="mt-2">
                            <small class="fw-bold"><?php echo e($ride['DriverName']); ?></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt text-success me-1"></i>
                            <strong><?php echo e($ride['OriginLocation']); ?></strong>
                        </div>
                        <div>
                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                            <strong><?php echo e($ride['DestinationLocation']); ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small">
                            <i class="far fa-calendar me-1"></i>
                            <?php echo format_date($ride['DepartureDate']); ?>
                        </div>
                        <div class="small">
                            <i class="far fa-clock me-1"></i>
                            <?php echo format_time($ride['DepartureTime']); ?>
                        </div>
                        <div class="small mt-2">
                            <i class="fas fa-chair me-1"></i>
                            <?php echo $ride['AvailableSeats']; ?> seats available
                        </div>
                        <div class="small">
                            <i class="fas fa-users me-1"></i>
                            <?php echo $ride['PassengerCount']; ?> passengers
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <span class="badge bg-<?php echo $status_class; ?> mb-2">
                            <?php echo e($ride['Status']); ?>
                        </span>
                        <div class="small text-muted">
                            RM <?php echo number_format($ride['PricePerSeat'], 2); ?>/seat
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <a href="<?php echo SITE_URL; ?>/rides/view.php?id=<?php echo $ride['RideID']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">
                        Previous
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === 1 || $i === $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php elseif ($i === $page - 3 || $i === $page + 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

