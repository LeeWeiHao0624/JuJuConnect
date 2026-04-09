<?php
/**
 * JuJuConnect - Ride Requests Management
 * View and manage all ride requests for drivers
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a driver
require_login();

if (!has_role('Driver')) {
    set_flash_message('error', 'Access denied. Only drivers can view this page.');
    redirect(SITE_URL . '/dashboard.php');
}

$page_title = 'Manage Ride Requests';
$user_id = $_SESSION['user_id'];

// Get filter
$filter = $_GET['filter'] ?? 'pending'; // pending, approved, rejected, all

// Pagination
$items_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Build query based on filter
$where_clause = "WHERE r.DriverID = ?";
$params = [$user_id];

if ($filter === 'pending') {
    $where_clause .= " AND rr.Status = 'Pending'";
} elseif ($filter === 'approved') {
    $where_clause .= " AND rr.Status = 'Approved'";
} elseif ($filter === 'rejected') {
    $where_clause .= " AND rr.Status = 'Rejected'";
}

// Get total count
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM RideRequests rr
    JOIN Rides r ON rr.RideID = r.RideID
    $where_clause
");
$count_stmt->execute($params);
$total_requests = $count_stmt->fetchColumn();

// Get requests
$stmt = $pdo->prepare("
    SELECT 
        rr.*,
        r.OriginLocation,
        r.DestinationLocation,
        r.DepartureDate,
        r.DepartureTime,
        r.AvailableSeats,
        r.Status as RideStatus,
        u.FullName as PassengerName,
        u.ProfilePicture as PassengerPicture,
        u.Rating as PassengerRating,
        u.TotalRatings as PassengerTotalRatings,
        u.Phone as PassengerPhone,
        u.Email as PassengerEmail
    FROM RideRequests rr
    JOIN Rides r ON rr.RideID = r.RideID
    JOIN Users u ON rr.PassengerID = u.UserID
    $where_clause
    ORDER BY 
        CASE rr.Status 
            WHEN 'Pending' THEN 1 
            WHEN 'Approved' THEN 2 
            ELSE 3 
        END,
        rr.RequestedAt DESC
    LIMIT ? OFFSET ?
");
$params[] = $items_per_page;
$params[] = $offset;
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Calculate pagination
$total_pages = ceil($total_requests / $items_per_page);

// Get statistics
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN rr.Status = 'Pending' THEN 1 END) as pending_count,
        COUNT(CASE WHEN rr.Status = 'Approved' THEN 1 END) as approved_count,
        COUNT(CASE WHEN rr.Status = 'Rejected' THEN 1 END) as rejected_count,
        COUNT(*) as total_count
    FROM RideRequests rr
    JOIN Rides r ON rr.RideID = r.RideID
    WHERE r.DriverID = ?
");
$stats_stmt->execute([$user_id]);
$stats = $stats_stmt->fetch();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-2">
                        <i class="fas fa-user-check text-success"></i> Ride Requests
                    </h1>
                    <p class="text-muted mb-0">Manage passenger requests for your rides</p>
                </div>
                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-warning shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['pending_count']; ?></h3>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['approved_count']; ?></h3>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['rejected_count']; ?></h3>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-primary shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-list fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['total_count']; ?></h3>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" 
                       href="?filter=pending">
                        <i class="fas fa-clock"></i> Pending (<?php echo $stats['pending_count']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'approved' ? 'active' : ''; ?>" 
                       href="?filter=approved">
                        <i class="fas fa-check-circle"></i> Approved (<?php echo $stats['approved_count']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'rejected' ? 'active' : ''; ?>" 
                       href="?filter=rejected">
                        <i class="fas fa-times-circle"></i> Rejected (<?php echo $stats['rejected_count']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                       href="?filter=all">
                        <i class="fas fa-list"></i> All (<?php echo $stats['total_count']; ?>)
                    </a>
                </li>
            </ul>

            <!-- Requests List -->
            <?php if (count($requests) > 0): ?>
                <div class="row">
                    <?php foreach ($requests as $request): ?>
                        <?php
                        $status_class = '';
                        $status_icon = '';
                        switch ($request['Status']) {
                            case 'Pending':
                                $status_class = 'warning';
                                $status_icon = 'fa-clock';
                                break;
                            case 'Approved':
                                $status_class = 'success';
                                $status_icon = 'fa-check-circle';
                                break;
                            case 'Rejected':
                                $status_class = 'danger';
                                $status_icon = 'fa-times-circle';
                                break;
                        }
                        ?>
                        
                        <div class="col-12 mb-3">
                            <div class="card border-<?php echo $status_class; ?> shadow-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <!-- Passenger Info -->
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo get_profile_picture($request['PassengerPicture']); ?>" 
                                                     class="rounded-circle me-3" width="60" height="60" 
                                                     alt="<?php echo e($request['PassengerName']); ?>">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="<?php echo SITE_URL; ?>/profile/view.php?id=<?php echo $request['PassengerID']; ?>" 
                                                           class="text-decoration-none">
                                                            <?php echo e($request['PassengerName']); ?>
                                                        </a>
                                                    </h6>
                                                    <div class="small">
                                                        <?php echo display_star_rating($request['PassengerRating']); ?>
                                                        <span class="text-muted">(<?php echo $request['PassengerTotalRatings']; ?>)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Ride Details -->
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <i class="fas fa-map-marker-alt text-success me-1"></i>
                                                <strong><?php echo e($request['OriginLocation']); ?></strong>
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                <strong><?php echo e($request['DestinationLocation']); ?></strong>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="far fa-calendar me-1"></i>
                                                <?php echo format_date($request['DepartureDate']); ?> at 
                                                <?php echo format_time($request['DepartureTime']); ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Request Details -->
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <i class="fas <?php echo $status_icon; ?>"></i> <?php echo $request['Status']; ?>
                                                </span>
                                            </div>
                                            <div class="small">
                                                <i class="fas fa-chair me-1"></i>
                                                <strong><?php echo $request['SeatsRequested']; ?></strong> seat(s) requested
                                            </div>
                                            <div class="small text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                Requested <?php echo time_ago($request['RequestedAt']); ?>
                                            </div>
                                            <?php if ($request['RespondedAt']): ?>
                                                <div class="small text-muted">
                                                    <i class="fas fa-reply me-1"></i>
                                                    Responded <?php echo time_ago($request['RespondedAt']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="col-md-2 text-end">
                                            <div class="d-flex flex-column gap-2" style="width: 120px; margin-left: auto;">
                                                <?php if ($request['Status'] === 'Pending'): ?>
                                                    <a href="manage-requests.php?id=<?php echo $request['RequestID']; ?>&action=approve" 
                                                       class="btn btn-success btn-sm w-100"
                                                       onclick="return confirm('Approve this request?');">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <a href="manage-requests.php?id=<?php echo $request['RequestID']; ?>&action=reject" 
                                                       class="btn btn-danger btn-sm w-100"
                                                       onclick="return confirm('Reject this request?');">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                    <div style="height: 8px;"></div>
                                                <?php endif; ?>
                                                <a href="../rides/view.php?id=<?php echo $request['RideID']; ?>" 
                                                   class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="fas fa-eye"></i> View Ride
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Message -->
                                    <?php if ($request['Message']): ?>
                                        <div class="mt-3 pt-3 border-top">
                                            <small class="text-muted">
                                                <i class="fas fa-comment me-2"></i>
                                                <strong>Message:</strong> <?php echo nl2br(e($request['Message'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Request pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <!-- Previous -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                            
                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i === 1 || $i === $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php elseif ($i === $page - 3 || $i === $page + 3): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- Next -->
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- No Requests -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-inbox" style="font-size: 5rem; color: #e0e0e0;"></i>
                    </div>
                    <h3 class="text-muted mb-3">No Requests Found</h3>
                    <p class="text-muted">
                        <?php if ($filter === 'pending'): ?>
                            You have no pending ride requests at the moment.
                        <?php elseif ($filter === 'approved'): ?>
                            You haven't approved any ride requests yet.
                        <?php elseif ($filter === 'rejected'): ?>
                            You haven't rejected any ride requests yet.
                        <?php else: ?>
                            You don't have any ride requests yet.
                        <?php endif; ?>
                    </p>
                    <a href="<?php echo SITE_URL; ?>/rides/create.php" class="btn btn-success mt-3">
                        <i class="fas fa-plus-circle"></i> Create a Ride Offer
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Better button spacing and alignment */
.gap-2 {
    gap: 0.5rem !important;
}

/* Ensure buttons are same width and aligned */
.btn-group-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 120px;
    margin-left: auto;
}

.btn-group-actions .btn {
    width: 100%;
}

/* Add visual separator between approve/reject and view ride */
.action-separator {
    height: 8px;
}
</style>

<?php include '../includes/footer.php'; ?>

