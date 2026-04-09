<?php
/**
 * JuJuConnect - Moderator Review Rides
 * Review and moderate ride listings
 */

require_once '../config/config.php';

// Check if user is moderator
require_role('Moderator');

$page_title = 'Review Rides';
$user_id = $_SESSION['user_id'];

// Handle ride actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $ride_id = intval($_POST['ride_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($ride_id > 0) {
        try {
            switch ($action) {
                case 'cancel':
                    $reason = sanitize_input($_POST['reason'] ?? '');
                    $stmt = $pdo->prepare("UPDATE Rides SET Status = 'Cancelled' WHERE RideID = ?");
                    $stmt->execute([$ride_id]);
                    
                    // Notify driver
                    $stmt = $pdo->prepare("SELECT DriverID FROM Rides WHERE RideID = ?");
                    $stmt->execute([$ride_id]);
                    $driver_id = $stmt->fetchColumn();
                    
                    create_notification($pdo, $driver_id, 'Ride Cancelled', 
                        'Your ride has been cancelled by a moderator', 
                        'Reason: ' . $reason, $ride_id);
                    
                    set_flash_message('success', 'Ride has been cancelled.');
                    break;
                    
                case 'flag':
                    $reason = sanitize_input($_POST['reason'] ?? '');
                    
                    // Get the driver ID for this ride
                    $stmt = $pdo->prepare("SELECT DriverID FROM Rides WHERE RideID = ?");
                    $stmt->execute([$ride_id]);
                    $driver_id = $stmt->fetchColumn();
                    
                    if ($driver_id) {
                        // Create a report in the Reports table
                        $stmt = $pdo->prepare("
                            INSERT INTO Reports (ReporterID, ReportedUserID, RideID, ReportType, Reason, Status)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $user_id,           // Moderator is the reporter
                            $driver_id,         // Driver is the reported user
                            $ride_id,           // The flagged ride
                            'Other',            // Report type
                            "Moderator flagged ride for review: " . $reason,  // Reason
                            'Under Review'      // Status
                        ]);
                        
                        // Notify the driver
                        create_notification($pdo, $driver_id, 'Ride Flagged',
                            'Your ride has been flagged for review',
                            'Reason: ' . $reason, $ride_id);
                    }
                    
                    log_activity($pdo, $user_id, 'moderator_flag_ride', 'Ride', $ride_id);
                    set_flash_message('info', 'Ride has been flagged for admin review.');
                    break;
            }
            
            log_activity($pdo, $user_id, "moderator_{$action}_ride", 'Ride', $ride_id);
            
        } catch (PDOException $e) {
            error_log("Moderator ride action error: " . $e->getMessage());
            set_flash_message('error', 'Failed to perform action: ' . $e->getMessage());
        }
    }
    
    redirect(SITE_URL . '/moderator/review-rides.php');
}

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
           u.Rating as DriverRating, u.Status as DriverStatus,
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
        COUNT(CASE WHEN Status = 'In Progress' THEN 1 END) as in_progress,
        COUNT(CASE WHEN Status = 'Completed' THEN 1 END) as completed,
        COUNT(CASE WHEN Status = 'Cancelled' THEN 1 END) as cancelled
    FROM Rides
");
$stats = $stats_stmt->fetch();

include '../includes/header.php';
?>

<div class="container my-5">
    <?php display_flash_message(); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-car text-info"></i> Review Rides</h1>
            <p class="text-muted mb-0">Monitor and moderate ride listings</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h4 class="mb-0 text-info"><?php echo $stats['available']; ?></h4>
                    <small class="text-muted">Available</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h4 class="mb-0 text-warning"><?php echo $stats['full']; ?></h4>
                    <small class="text-muted">Full</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h4 class="mb-0 text-primary"><?php echo $stats['in_progress']; ?></h4>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h4 class="mb-0 text-success"><?php echo $stats['completed']; ?></h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
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
            case 'In Progress': $status_class = 'primary'; break;
            case 'Completed': $status_class = 'success'; break;
            case 'Cancelled': $status_class = 'danger'; break;
            default: $status_class = 'secondary';
        }
        ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="<?php echo get_profile_picture($ride['DriverPicture']); ?>" 
                             class="rounded-circle mb-2" width="60" height="60" alt="Driver">
                        <div>
                            <small class="fw-bold"><?php echo e($ride['DriverName']); ?></small><br>
                            <small><?php echo display_star_rating($ride['DriverRating']); ?></small><br>
                            <span class="badge bg-<?php echo $ride['DriverStatus'] === 'Active' ? 'success' : 'danger'; ?>">
                                <?php echo $ride['DriverStatus']; ?>
                            </span>
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
                        <div class="small mt-1">
                            <strong>RM <?php echo number_format($ride['PricePerSeat'], 2); ?></strong>/seat
                        </div>
                    </div>
                    
                    <div class="col-md-2 text-center">
                        <span class="badge bg-<?php echo $status_class; ?> mb-2">
                            <?php echo e($ride['Status']); ?>
                        </span>
                        <div class="small text-muted">
                            Created <?php echo time_ago($ride['CreatedAt']); ?>
                        </div>
                    </div>
                    
                    <div class="col-md-1">
                        <div class="d-flex flex-column gap-1">
                            <!-- View Button -->
                            <a href="<?php echo SITE_URL; ?>/rides/view.php?id=<?php echo $ride['RideID']; ?>" 
                               class="btn btn-sm btn-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <!-- Actions Dropdown -->
                            <?php if ($ride['Status'] !== 'Completed' && $ride['Status'] !== 'Cancelled'): ?>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle w-100" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item text-warning" 
                                                    onclick="flagRide(<?php echo $ride['RideID']; ?>)">
                                                <i class="fas fa-flag me-2"></i>Flag for Review
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" 
                                                    onclick="cancelRide(<?php echo $ride['RideID']; ?>)">
                                                <i class="fas fa-ban me-2"></i>Cancel Ride
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($ride['Notes']): ?>
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Notes:</strong> <?php echo e($ride['Notes']); ?>
                        </small>
                    </div>
                <?php endif; ?>
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

<!-- Modals for Actions -->
<div class="modal fade" id="flagRideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-flag me-2"></i>Flag Ride</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ride_id" id="flag_ride_id">
                    <input type="hidden" name="action" value="flag">
                    <div class="mb-3">
                        <label class="form-label">Reason for flagging:</label>
                        <textarea class="form-control" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Flag Ride</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelRideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Cancel Ride</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ride_id" id="cancel_ride_id">
                    <input type="hidden" name="action" value="cancel">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will cancel the ride and notify the driver and passengers.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for cancellation:</label>
                        <textarea class="form-control" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Cancel Ride</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function flagRide(rideId) {
    document.getElementById('flag_ride_id').value = rideId;
    new bootstrap.Modal(document.getElementById('flagRideModal')).show();
}

function cancelRide(rideId) {
    document.getElementById('cancel_ride_id').value = rideId;
    new bootstrap.Modal(document.getElementById('cancelRideModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>

