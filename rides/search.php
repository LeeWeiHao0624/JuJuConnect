<?php
require_once '../config/config.php';
require_login();

$page_title = "Search Rides";

// Get search parameters
$search_origin = sanitize_input($_GET['origin'] ?? '');
$search_destination = sanitize_input($_GET['destination'] ?? '');
$search_date = sanitize_input($_GET['date'] ?? '');
$search_seats = intval($_GET['seats'] ?? 1);

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["r.Status = 'Available'", "r.DepartureDate >= CURDATE()", "u.Status = 'Active'"];
$params = [];

if (!empty($search_origin)) {
    $where_conditions[] = "r.OriginLocation LIKE ?";
    $params[] = "%$search_origin%";
}

if (!empty($search_destination)) {
    $where_conditions[] = "r.DestinationLocation LIKE ?";
    $params[] = "%$search_destination%";
}

if (!empty($search_date)) {
    $where_conditions[] = "r.DepartureDate = ?";
    $params[] = $search_date;
}

if ($search_seats > 0) {
    $where_conditions[] = "r.AvailableSeats >= ?";
    $params[] = $search_seats;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_query = "
    SELECT COUNT(*) 
    FROM Rides r 
    INNER JOIN Users u ON r.DriverID = u.UserID 
    WHERE $where_clause
";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_items = $stmt->fetchColumn();

// Get rides
$query = "
    SELECT 
        r.*,
        u.FullName AS DriverName,
        u.ProfilePicture AS DriverPicture,
        u.Rating AS DriverRating,
        u.TotalRatings AS DriverTotalRatings,
        (SELECT Status FROM RideRequests WHERE RideID = r.RideID AND PassengerID = ?) AS MyRequestStatus
    FROM Rides r
    INNER JOIN Users u ON r.DriverID = u.UserID
    WHERE $where_clause
    ORDER BY r.DepartureDate ASC, r.DepartureTime ASC
    LIMIT $limit OFFSET $offset
";

$params_with_user = array_merge([$_SESSION['user_id']], $params);
$stmt = $pdo->prepare($query);
$stmt->execute($params_with_user);
$rides = $stmt->fetchAll();

// Pagination info
$pagination = paginate($total_items, $limit, $page);
?>

<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <!-- Search Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h4 class="mb-3"><i class="fas fa-search me-2"></i>Find Your Ride</h4>
            <form method="GET" action="" id="searchForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="origin" class="form-label">From</label>
                        <input type="text" class="form-control" id="origin" name="origin" 
                               placeholder="Origin" value="<?php echo e($search_origin); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="destination" class="form-label">To</label>
                        <input type="text" class="form-control" id="destination" name="destination" 
                               placeholder="Destination" value="<?php echo e($search_destination); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo e($search_date); ?>" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="seats" class="form-label">Seats</label>
                        <select class="form-select" id="seats" name="seats">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $search_seats == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> seat<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Results -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            <?php echo $total_items; ?> ride<?php echo $total_items != 1 ? 's' : ''; ?> found
        </h5>
        <?php if (has_role('Driver')): ?>
            <a href="create.php" class="btn btn-success">
                <i class="fas fa-plus-circle me-2"></i>Offer a Ride
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($rides)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-car fa-4x text-muted mb-3"></i>
                <h5>No rides found</h5>
                <p class="text-muted">Try adjusting your search criteria or check back later.</p>
                <?php if (has_role('Driver')): ?>
                    <a href="create.php" class="btn btn-success mt-3">
                        <i class="fas fa-plus-circle me-2"></i>Be the first to offer a ride!
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($rides as $ride): ?>
            <div class="card mb-3 border-0 shadow-sm hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Driver Info -->
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            <img src="<?php echo get_profile_picture($ride['DriverPicture']); ?>" 
                                 class="rounded-circle mb-2" width="70" height="70" alt="Driver">
                            <h6 class="mb-1"><?php echo e($ride['DriverName']); ?></h6>
                            <div class="small">
                                <?php echo display_star_rating($ride['DriverRating']); ?>
                            </div>
                            <small class="text-muted">(<?php echo $ride['DriverTotalRatings']; ?>)</small>
                        </div>
                        
                        <!-- Ride Details -->
                        <div class="col-md-7 mb-3 mb-md-0">
                            <div class="d-flex align-items-start mb-2">
                                <div class="me-3">
                                    <i class="fas fa-circle text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo e($ride['OriginLocation']); ?></h6>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-2 text-muted" style="margin-left: 7px;">
                                <i class="fas fa-ellipsis-v fa-xs me-3"></i>
                                <small>
                                    <?php if ($ride['Distance']): ?>
                                        <?php echo number_format($ride['Distance'], 1); ?> km • 
                                    <?php endif; ?>
                                    <?php echo format_date($ride['DepartureDate']); ?> at <?php echo format_time($ride['DepartureTime']); ?>
                                </small>
                            </div>
                            
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="fas fa-circle text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo e($ride['DestinationLocation']); ?></h6>
                                </div>
                            </div>
                            
                            <?php if ($ride['Notes']): ?>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?php echo e(substr($ride['Notes'], 0, 100)); ?>
                                        <?php if (strlen($ride['Notes']) > 100): ?>...<?php endif; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Tags -->
                            <div class="mt-2">
                                <?php if ($ride['VehicleType']): ?>
                                    <span class="badge bg-secondary me-1">
                                        <i class="fas fa-car me-1"></i><?php echo e($ride['VehicleType']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-info">
                                    <i class="fas fa-users me-1"></i><?php echo $ride['AvailableSeats']; ?> seat<?php echo $ride['AvailableSeats'] > 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Price & Action -->
                        <div class="col-md-3 text-center text-md-end">
                            <div class="mb-3">
                                <h4 class="text-success mb-0">RM <?php echo number_format($ride['PricePerSeat'], 2); ?></h4>
                                <small class="text-muted">per seat</small>
                            </div>
                            
                            <?php if ($ride['DriverID'] == $_SESSION['user_id']): ?>
                                <span class="badge bg-success mb-2">Your Ride</span><br>
                                <a href="view.php?id=<?php echo $ride['RideID']; ?>" class="btn btn-primary btn-sm">
                                    Manage
                                </a>
                            <?php elseif ($ride['MyRequestStatus'] === 'Pending'): ?>
                                <span class="badge bg-warning mb-2">Request Pending</span><br>
                                <a href="view.php?id=<?php echo $ride['RideID']; ?>" class="btn btn-outline-secondary btn-sm">
                                    View Details
                                </a>
                            <?php elseif ($ride['MyRequestStatus'] === 'Approved'): ?>
                                <span class="badge bg-success mb-2">Booked</span><br>
                                <a href="view.php?id=<?php echo $ride['RideID']; ?>" class="btn btn-outline-success btn-sm">
                                    View Details
                                </a>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <a href="request.php?ride_id=<?php echo $ride['RideID']; ?>" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Request Ride
                                    </a>
                                    <a href="view.php?id=<?php echo $ride['RideID']; ?>" class="btn btn-outline-primary">
                                        View Details
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="mt-4">
                <?php 
                $base_url = 'search.php?' . http_build_query([
                    'origin' => $search_origin,
                    'destination' => $search_destination,
                    'date' => $search_date,
                    'seats' => $search_seats
                ]);
                echo display_pagination($pagination['total_pages'], $page, $base_url);
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.hover-shadow {
    transition: box-shadow 0.3s ease;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
</style>

<?php include '../includes/footer.php'; ?>

