<?php
require_once '../config/config.php';
require_login();

$ride_id = intval($_GET['id'] ?? 0);

if (!$ride_id) {
    set_flash_message('error', 'Invalid ride ID.');
    redirect(SITE_URL . '/rides/search.php');
}

// Get ride details with driver info
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.FullName as DriverName,
           u.ProfilePicture as DriverPicture,
           u.Rating as DriverRating,
           u.TotalRatings as DriverTotalRatings,
           u.Phone as DriverPhone,
           u.Email as DriverEmail
    FROM Rides r
    JOIN Users u ON r.DriverID = u.UserID
    WHERE r.RideID = ?
");
$stmt->execute([$ride_id]);
$ride = $stmt->fetch();

if (!$ride) {
    set_flash_message('error', 'Ride not found.');
    redirect(SITE_URL . '/rides/search.php');
}

$is_driver = ($ride['DriverID'] == $_SESSION['user_id']);
$page_title = "Ride Details";

// Handle ride actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid security token.');
    } else {
        $action = $_POST['action'];
        
        // Handle Complete Ride
        if ($action === 'complete_ride' && $is_driver && $ride['Status'] !== 'Completed' && $ride['Status'] !== 'Cancelled') {
            try {
                // Update ride status
                $stmt = $pdo->prepare("UPDATE Rides SET Status = 'Completed' WHERE RideID = ?");
                $stmt->execute([$ride_id]);
                
                // Get all approved passengers
                $stmt = $pdo->prepare("
                    SELECT PassengerID FROM RideRequests 
                    WHERE RideID = ? AND Status = 'Approved'
                ");
                $stmt->execute([$ride_id]);
                $passengers = $stmt->fetchAll();
                
                // Award eco-points to driver and passengers
                $distance = $ride['Distance'] ?? 10;
                $num_passengers = count($passengers);
                
                // Award points to driver
                $driver_points = calculate_eco_points($distance, $num_passengers);
                award_eco_points($pdo, $ride['DriverID'], $driver_points, 'Completed ride as driver');
                
                // Award points to each passenger
                foreach ($passengers as $passenger) {
                    $passenger_points = calculate_eco_points($distance, 1) + PASSENGER_BONUS_POINTS;
                    award_eco_points($pdo, $passenger['PassengerID'], $passenger_points, 'Completed ride as passenger');
                    
                    // Notify passenger
                    create_notification($pdo, $passenger['PassengerID'], 'Ride Completed',
                        'Ride has been completed!',
                        "The ride from {$ride['OriginLocation']} to {$ride['DestinationLocation']} has been completed. You can now rate the driver.",
                        $ride_id
                    );
                }
                
                // Log activity
                log_activity($pdo, $_SESSION['user_id'], 'complete_ride', 'Ride', $ride_id);
                
                set_flash_message('success', 'Ride has been completed! Eco-points awarded.');
                $ref_param = isset($_GET['ref']) ? '&ref=' . urlencode($_GET['ref']) : '';
                redirect(SITE_URL . '/rides/view.php?id=' . $ride_id . $ref_param);
                
            } catch (PDOException $e) {
                error_log("Complete ride error: " . $e->getMessage());
                set_flash_message('error', 'Failed to complete ride. Please try again.');
            }
        }
        
        // Handle Cancel Ride
        elseif ($action === 'cancel_ride' && $is_driver && $ride['Status'] !== 'Completed' && $ride['Status'] !== 'Cancelled') {
            try {
                // Update ride status
                $stmt = $pdo->prepare("UPDATE Rides SET Status = 'Cancelled' WHERE RideID = ?");
                $stmt->execute([$ride_id]);
                
                // Notify all passengers with approved requests
                $stmt = $pdo->prepare("
                    SELECT PassengerID FROM RideRequests 
                    WHERE RideID = ? AND Status = 'Approved'
                ");
                $stmt->execute([$ride_id]);
                $passengers_to_notify = $stmt->fetchAll();
                
                foreach ($passengers_to_notify as $passenger) {
                    create_notification($pdo, $passenger['PassengerID'], 'Ride Cancelled',
                        'Ride Cancelled by Driver',
                        "The ride from {$ride['OriginLocation']} to {$ride['DestinationLocation']} has been cancelled by the driver.",
                        $ride_id
                    );
                }
                
                // Log activity
                log_activity($pdo, $_SESSION['user_id'], 'cancel_ride', 'Ride', $ride_id);
                
                set_flash_message('success', 'Ride has been cancelled successfully.');
                // Redirect back to referrer or dashboard
                $redirect_url = (isset($_GET['ref']) && $_GET['ref'] === 'history') 
                    ? SITE_URL . '/rides/history.php' 
                    : SITE_URL . '/dashboard.php';
                redirect($redirect_url);
                
            } catch (PDOException $e) {
                error_log("Cancel ride error: " . $e->getMessage());
                set_flash_message('error', 'Failed to cancel ride. Please try again.');
            }
        }
        
        // Handle Rating
        elseif ($action === 'submit_rating') {
            $rated_user_id = intval($_POST['rated_user_id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 0);
            $review = sanitize_input($_POST['review'] ?? '');
            
            if ($rated_user_id > 0 && $rating >= 1 && $rating <= 5) {
                try {
                    // Check if already rated
                    $stmt = $pdo->prepare("SELECT * FROM Ratings WHERE RideID = ? AND RaterID = ? AND RatedUserID = ?");
                    $stmt->execute([$ride_id, $_SESSION['user_id'], $rated_user_id]);
                    $existing_rating = $stmt->fetch();
                    
                    if (!$existing_rating) {
                        // Insert rating
                        $stmt = $pdo->prepare("
                            INSERT INTO Ratings (RideID, RaterID, RatedUserID, Rating, Review)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$ride_id, $_SESSION['user_id'], $rated_user_id, $rating, $review]);
                        
                        // Update user's average rating
                        $stmt = $pdo->prepare("
                            UPDATE Users 
                            SET Rating = (SELECT AVG(Rating) FROM Ratings WHERE RatedUserID = ?),
                                TotalRatings = (SELECT COUNT(*) FROM Ratings WHERE RatedUserID = ?)
                            WHERE UserID = ?
                        ");
                        $stmt->execute([$rated_user_id, $rated_user_id, $rated_user_id]);
                        
                        // Notify the rated user
                        create_notification($pdo, $rated_user_id, 'New Rating',
                            'You received a new rating!',
                            "You have been rated {$rating} stars for the ride.",
                            $ride_id
                        );
                        
                        log_activity($pdo, $_SESSION['user_id'], 'submit_rating', 'Rating', $pdo->lastInsertId());
                        
                        set_flash_message('success', 'Thank you for your rating!');
                    } else {
                        set_flash_message('warning', 'You have already rated this user for this ride.');
                    }
                    
                    $ref_param = isset($_GET['ref']) ? '&ref=' . urlencode($_GET['ref']) : '';
                    redirect(SITE_URL . '/rides/view.php?id=' . $ride_id . $ref_param);
                    
                } catch (PDOException $e) {
                    error_log("Submit rating error: " . $e->getMessage());
                    set_flash_message('error', 'Failed to submit rating. Please try again.');
                }
            }
        }
    }
}

// Get passengers if user is driver
if ($is_driver) {
    $stmt = $pdo->prepare("
        SELECT rr.*, 
               u.FullName, u.ProfilePicture, u.Rating, u.TotalRatings, u.Phone, u.Email, u.UserID,
               (SELECT Rating FROM Ratings WHERE RideID = ? AND RaterID = ? AND RatedUserID = u.UserID) as MyRating
        FROM RideRequests rr
        JOIN Users u ON rr.PassengerID = u.UserID
        WHERE rr.RideID = ? AND rr.Status IN ('Pending', 'Approved')
        ORDER BY rr.RequestedAt DESC
    ");
    $stmt->execute([$ride_id, $_SESSION['user_id'], $ride_id]);
    $passengers = $stmt->fetchAll();
} else {
    // Check passenger's request status
    $stmt = $pdo->prepare("SELECT * FROM RideRequests WHERE RideID = ? AND PassengerID = ?");
    $stmt->execute([$ride_id, $_SESSION['user_id']]);
    $my_request = $stmt->fetch();
    
    // Check if passenger has rated the driver
    if (!empty($my_request) && $my_request['Status'] === 'Approved') {
        $stmt = $pdo->prepare("SELECT * FROM Ratings WHERE RideID = ? AND RaterID = ? AND RatedUserID = ?");
        $stmt->execute([$ride_id, $_SESSION['user_id'], $ride['DriverID']]);
        $my_driver_rating = $stmt->fetch();
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <?php display_flash_message(); ?>
    
    <div class="row">
        <!-- Ride Details -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-car me-2"></i>Ride Details</h4>
                </div>
                <div class="card-body">
                    <!-- Status Badge -->
                    <div class="mb-3">
                        <?php
                        $status_colors = [
                            'Available' => 'success',
                            'Full' => 'warning',
                            'Completed' => 'secondary',
                            'Cancelled' => 'danger',
                            'In Progress' => 'info'
                        ];
                        $color = $status_colors[$ride['Status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?php echo $color; ?> fs-6">
                            <?php echo e($ride['Status']); ?>
                        </span>
                    </div>
                    
                    <!-- Route -->
                    <div class="mb-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3" style="width: 30px;">
                                <i class="fas fa-circle text-success fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?php echo e($ride['OriginLocation']); ?></h5>
                                <small class="text-muted">Pickup Location</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3 ms-2 text-muted">
                            <i class="fas fa-ellipsis-v me-3"></i>
                            <small>
                                <?php if ($ride['Distance']): ?>
                                    <?php echo number_format($ride['Distance'], 1); ?> km • 
                                <?php endif; ?>
                                Estimated <?php echo ceil($ride['Distance'] / 40); ?> mins travel time
                            </small>
                        </div>
                        
                        <div class="d-flex align-items-start">
                            <div class="me-3" style="width: 30px;">
                                <i class="fas fa-circle text-danger fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?php echo e($ride['DestinationLocation']); ?></h5>
                                <small class="text-muted">Drop-off Location</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Details Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <strong>Date:</strong> <?php echo format_date($ride['DepartureDate'], 'l, F j, Y'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <strong>Time:</strong> <?php echo format_time($ride['DepartureTime']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <strong>Available Seats:</strong> <?php echo $ride['AvailableSeats']; ?> / <?php echo $ride['TotalSeats']; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <i class="fas fa-dollar-sign text-success me-2"></i>
                                    <strong>Price:</strong> RM <?php echo number_format($ride['PricePerSeat'], 2); ?> per seat
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($ride['VehicleType']): ?>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <i class="fas fa-car text-primary me-2"></i>
                                        <strong>Vehicle:</strong> <?php echo e($ride['VehicleType']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($ride['VehiclePlateNumber'] && $is_driver): ?>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <i class="fas fa-id-card text-primary me-2"></i>
                                        <strong>Plate:</strong> <?php echo e($ride['VehiclePlateNumber']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Notes -->
                    <?php if ($ride['Notes']): ?>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                            <p class="mb-0"><?php echo nl2br(e($ride['Notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <?php if (!$is_driver && $ride['Status'] === 'Available' && !has_any_role(['Admin', 'Moderator'])): ?>
                        <?php if (empty($my_request)): ?>
                            <div class="d-grid">
                                <a href="request.php?ride_id=<?php echo $ride_id; ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Request to Join This Ride
                                </a>
                            </div>
                        <?php elseif ($my_request['Status'] === 'Pending'): ?>
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-clock me-2"></i>Your request is pending driver approval
                            </div>
                        <?php elseif ($my_request['Status'] === 'Approved'): ?>
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle me-2"></i>Your request has been approved!
                            </div>
                        <?php elseif ($my_request['Status'] === 'Rejected'): ?>
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-times-circle me-2"></i>Your request was rejected
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Driver Actions -->
                    <?php if ($is_driver && $ride['Status'] !== 'Completed' && $ride['Status'] !== 'Cancelled'): ?>
                        <div class="mt-3">
                            <form method="POST" onsubmit="return confirm('Mark this ride as completed? This will award eco-points to all participants.');" class="mb-2">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="complete_ride">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle me-2"></i>Complete Ride
                                    </button>
                                </div>
                            </form>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this ride? All passengers will be notified.');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="cancel_ride">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-ban me-2"></i>Cancel This Ride
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Passengers List (for driver) -->
            <?php if ($is_driver && !empty($passengers)): ?>
                <div class="card shadow border-0 mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Passengers</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($passengers as $passenger): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo get_profile_picture($passenger['ProfilePicture']); ?>" 
                                         class="rounded-circle me-3" width="50" height="50" alt="Passenger">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo e($passenger['FullName']); ?></h6>
                                        <div class="small">
                                            <?php echo display_star_rating($passenger['Rating']); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $passenger['SeatsRequested']; ?> seat(s) • 
                                            Requested <?php echo time_ago($passenger['RequestedAt']); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($passenger['Status'] === 'Pending'): ?>
                                            <div class="mb-3">
                                                <span class="badge bg-warning">Pending</span>
                                            </div>
                                            <div class="d-flex flex-column gap-2" style="width: 100px; margin-left: auto;">
                                                <a href="manage-requests.php?id=<?php echo $passenger['RequestID']; ?>&action=approve&redirect=ride" 
                                                   class="btn btn-success btn-sm w-100"
                                                   onclick="return confirm('Approve this request?');">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                                <a href="manage-requests.php?id=<?php echo $passenger['RequestID']; ?>&action=reject&redirect=ride" 
                                                   class="btn btn-danger btn-sm w-100"
                                                   onclick="return confirm('Reject this request?');">
                                                    <i class="fas fa-times"></i> Reject
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($passenger['Message']): ?>
                                    <div class="mt-2 ps-5">
                                        <small class="text-muted">
                                            <i class="fas fa-comment me-1"></i><?php echo e($passenger['Message']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Rating Interface (only if ride is completed and passenger was approved) -->
                                <?php if ($ride['Status'] === 'Completed' && $passenger['Status'] === 'Approved'): ?>
                                    <div class="mt-3 ps-5 border-top pt-3">
                                        <?php if (empty($passenger['MyRating'])): ?>
                                            <button class="btn btn-sm btn-warning" onclick="showRatingModal(<?php echo $passenger['UserID']; ?>, '<?php echo addslashes(e($passenger['FullName'])); ?>')">
                                                <i class="fas fa-star me-1"></i>Rate This Passenger
                                            </button>
                                        <?php else: ?>
                                            <small class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>You rated: <?php echo display_star_rating($passenger['MyRating']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Passenger Rating Interface (for passengers to rate driver) -->
            <?php if (!$is_driver && !empty($my_request) && $my_request['Status'] === 'Approved' && $ride['Status'] === 'Completed'): ?>
                <div class="card shadow border-0 mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Rate Your Experience</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($my_driver_rating)): ?>
                            <p>How was your ride with <?php echo e($ride['DriverName']); ?>?</p>
                            <button class="btn btn-warning" onclick="showRatingModal(<?php echo $ride['DriverID']; ?>, '<?php echo addslashes(e($ride['DriverName'])); ?>')">
                                <i class="fas fa-star me-2"></i>Rate Driver
                            </button>
                        <?php else: ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                You rated this driver: <?php echo display_star_rating($my_driver_rating['Rating']); ?>
                                <?php if ($my_driver_rating['Review']): ?>
                                    <p class="mb-0 mt-2"><em>"<?php echo e($my_driver_rating['Review']); ?>"</em></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Driver Info -->
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Driver Information</h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo get_profile_picture($ride['DriverPicture']); ?>" 
                         class="rounded-circle mb-3" width="100" height="100" alt="Driver">
                    <h5 class="mb-2"><?php echo e($ride['DriverName']); ?></h5>
                    <div class="mb-2">
                        <?php echo display_star_rating($ride['DriverRating']); ?>
                    </div>
                    <p class="text-muted small"><?php echo $ride['DriverTotalRatings']; ?> ratings</p>
                    
                    <?php if ($is_driver): ?>
                        <span class="badge bg-success">You are the driver</span>
                    <?php else: ?>
                        <a href="../profile/view.php?id=<?php echo $ride['DriverID']; ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-2"></i>View Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Environmental Impact -->
            <div class="card shadow border-0 bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-leaf fa-3x mb-3"></i>
                    <h5>Environmental Impact</h5>
                    <?php
                    $potential_co2 = calculate_co2_saved($ride['Distance'] ?? 10, $ride['TotalSeats'] - $ride['AvailableSeats']);
                    $potential_points = calculate_eco_points($ride['Distance'] ?? 10, $ride['TotalSeats'] - $ride['AvailableSeats']);
                    ?>
                    <p class="mb-2">Potential CO₂ Savings</p>
                    <h3 class="fw-bold"><?php echo number_format($potential_co2, 1); ?> kg</h3>
                    <hr class="bg-white">
                    <p class="small mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Earn up to <?php echo $potential_points; ?> eco-points!
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <?php 
        // Check if there's a referrer parameter
        $ref = $_GET['ref'] ?? '';
        
        if ($ref === 'history'): ?>
            <a href="history.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Ride History
            </a>
        <?php elseif (has_role('Moderator')): ?>
            <a href="<?php echo SITE_URL; ?>/moderator/review-rides.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Review Rides
            </a>
        <?php elseif (has_role('Admin')): ?>
            <a href="<?php echo SITE_URL; ?>/admin/rides.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Manage Rides
            </a>
        <?php else: ?>
            <a href="search.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Search
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="submit_rating">
                <input type="hidden" name="rated_user_id" id="rated_user_id">
                
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-star me-2"></i>Rate <span id="rated_user_name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">Your Rating</label>
                        <div class="rating-stars" style="font-size: 2rem;">
                            <i class="far fa-star rating-star" data-rating="1" onclick="setRating(1)"></i>
                            <i class="far fa-star rating-star" data-rating="2" onclick="setRating(2)"></i>
                            <i class="far fa-star rating-star" data-rating="3" onclick="setRating(3)"></i>
                            <i class="far fa-star rating-star" data-rating="4" onclick="setRating(4)"></i>
                            <i class="far fa-star rating-star" data-rating="5" onclick="setRating(5)"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating_value" required>
                        <div class="text-muted small mt-2" id="rating_text"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review" class="form-label">Review (Optional)</label>
                        <textarea class="form-control" name="review" id="review" rows="3" 
                                  placeholder="Share your experience..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Rating</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRatingModal(userId, userName) {
    document.getElementById('rated_user_id').value = userId;
    document.getElementById('rated_user_name').textContent = userName;
    document.getElementById('rating_value').value = '';
    document.getElementById('rating_text').textContent = '';
    
    // Reset stars
    document.querySelectorAll('.rating-star').forEach(star => {
        star.classList.remove('fas');
        star.classList.add('far');
        star.style.color = '#ffc107';
    });
    
    new bootstrap.Modal(document.getElementById('ratingModal')).show();
}

function setRating(rating) {
    document.getElementById('rating_value').value = rating;
    
    const ratings = {
        1: 'Poor',
        2: 'Fair',
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
    };
    
    document.getElementById('rating_text').textContent = ratings[rating];
    
    // Update stars
    document.querySelectorAll('.rating-star').forEach(star => {
        const starRating = parseInt(star.getAttribute('data-rating'));
        if (starRating <= rating) {
            star.classList.remove('far');
            star.classList.add('fas');
            star.style.color = '#ffc107';
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
            star.style.color = '#ffc107';
        }
    });
}

// Hover effect for stars
document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('mouseover', function() {
        const rating = parseInt(this.getAttribute('data-rating'));
        document.querySelectorAll('.rating-star').forEach(s => {
            const sRating = parseInt(s.getAttribute('data-rating'));
            if (sRating <= rating) {
                s.classList.remove('far');
                s.classList.add('fas');
            } else {
                s.classList.remove('fas');
                s.classList.add('far');
            }
        });
    });
});

document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
    const currentRating = parseInt(document.getElementById('rating_value').value || 0);
    document.querySelectorAll('.rating-star').forEach(star => {
        const starRating = parseInt(star.getAttribute('data-rating'));
        if (starRating <= currentRating) {
            star.classList.remove('far');
            star.classList.add('fas');
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>

