<?php
require_once 'config/config.php';
require_login();

$page_title = "Dashboard";
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Redirect admin to admin dashboard
if ($user_role === 'Admin') {
    redirect(SITE_URL . '/admin/dashboard.php');
}

// Redirect moderator to moderator dashboard
if ($user_role === 'Moderator') {
    redirect(SITE_URL . '/moderator/dashboard.php');
}

// Get user information
$user = get_user_by_id($pdo, $user_id);

// Get user statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT CASE WHEN Role = 'Driver' THEN RideID END) as total_drives,
        COUNT(DISTINCT CASE WHEN Role = 'Passenger' THEN RideID END) as total_rides,
        COALESCE(SUM(EcoPointsEarned), 0) as total_points_earned,
        COALESCE(SUM(CO2Saved), 0) as total_co2_saved
    FROM RideHistory 
    WHERE UserID = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Get recent rides (last 5) from RideHistory
$stmt = $pdo->prepare("
    SELECT 
        rh.*,
        r.OriginLocation,
        r.DestinationLocation,
        r.DepartureDate,
        r.DepartureTime
    FROM RideHistory rh
    JOIN Rides r ON rh.RideID = r.RideID
    WHERE rh.UserID = ?
    ORDER BY rh.CompletedAt DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_rides = $stmt->fetchAll();

// Get completed rides for passengers (for rating)
if ($user_role === 'Passenger' || $user_role === 'Driver') {
    $stmt = $pdo->prepare("
        SELECT 
            r.RideID,
            r.OriginLocation,
            r.DestinationLocation,
            r.DepartureDate,
            r.DepartureTime,
            r.Distance,
            r.DriverID,
            u.FullName as DriverName,
            u.ProfilePicture as DriverPicture,
            u.Rating as DriverRating,
            rr.Status as RequestStatus,
            (SELECT Rating FROM Ratings WHERE RideID = r.RideID AND RaterID = ? AND RatedUserID = r.DriverID) as MyRating
        FROM Rides r
        JOIN Users u ON r.DriverID = u.UserID
        LEFT JOIN RideRequests rr ON r.RideID = rr.RideID AND rr.PassengerID = ?
        WHERE r.Status = 'Completed'
        AND (rr.PassengerID = ? OR r.DriverID = ?)
        AND (rr.Status = 'Approved' OR r.DriverID = ?)
        ORDER BY r.DepartureDate DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
    $completed_rides = $stmt->fetchAll();
} else {
    $completed_rides = [];
}

// Get upcoming rides (for passengers)
if ($user_role === 'Passenger' || $user_role === 'Driver') {
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            u.FullName as DriverName,
            u.ProfilePicture as DriverPicture,
            u.Rating as DriverRating,
            rr.Status as RequestStatus,
            rr.SeatsRequested
        FROM Rides r
        LEFT JOIN RideRequests rr ON r.RideID = rr.RideID AND rr.PassengerID = ?
        JOIN Users u ON r.DriverID = u.UserID
        WHERE (r.DriverID = ? OR rr.PassengerID = ?)
        AND r.Status IN ('Available', 'Full', 'In Progress')
        AND r.DepartureDate >= CURDATE()
        AND (rr.Status = 'Approved' OR r.DriverID = ?)
        ORDER BY r.DepartureDate ASC, r.DepartureTime ASC
        LIMIT 5
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
    $upcoming_rides = $stmt->fetchAll();
} else {
    $upcoming_rides = [];
}

// Get pending requests (for drivers)
if ($user_role === 'Driver') {
    $stmt = $pdo->prepare("
        SELECT 
            rr.*,
            r.OriginLocation,
            r.DestinationLocation,
            r.DepartureDate,
            r.DepartureTime,
            u.FullName as PassengerName,
            u.ProfilePicture as PassengerPicture,
            u.Rating as PassengerRating
        FROM RideRequests rr
        JOIN Rides r ON rr.RideID = r.RideID
        JOIN Users u ON rr.PassengerID = u.UserID
        WHERE r.DriverID = ? AND rr.Status = 'Pending'
        ORDER BY rr.RequestedAt DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $pending_requests = $stmt->fetchAll();
}

// Get leaderboard position
$stmt = $pdo->prepare("
    SELECT COUNT(*) + 1 as position
    FROM Users
    WHERE EcoPoints > ? AND Status = 'Active'
");
$stmt->execute([$user['EcoPoints']]);
$leaderboard_position = $stmt->fetchColumn();

// Get recent notifications
$stmt = $pdo->prepare("
    SELECT * FROM Notifications
    WHERE UserID = ?
    ORDER BY CreatedAt DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container my-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-success text-white border-0 shadow">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo get_profile_picture($user['ProfilePicture']); ?>" 
                             alt="Profile" class="rounded-circle me-3" width="80" height="80">
                        <div>
                            <h2 class="mb-1">Welcome back, <?php echo e($user['FullName']); ?>!</h2>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-user-tag me-2"></i><?php echo e($user['Role']); ?> 
                                <span class="ms-3"><i class="fas fa-star me-2"></i>Rating: <?php echo format_rating($user['Rating']); ?></span>
                                <span class="ms-3"><i class="fas fa-trophy me-2"></i>Rank: #<?php echo $leaderboard_position; ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo number_format($user['EcoPoints']); ?></h4>
                    <small class="text-muted">Eco Points</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-2x text-primary mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo $stats['total_drives'] + $stats['total_rides']; ?></h4>
                    <small class="text-muted">Total Rides</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-leaf fa-2x text-success mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo number_format($stats['total_co2_saved'], 1); ?> kg</h4>
                    <small class="text-muted">CO₂ Saved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x text-info mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo format_rating($user['Rating']); ?></h4>
                    <small class="text-muted">Your Rating</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Pending Requests (Driver only) -->
            <?php if ($user_role === 'Driver' && !empty($pending_requests)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Ride Requests</h5>
                        <a href="<?php echo SITE_URL; ?>/rides/requests.php" class="btn btn-sm btn-dark">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($pending_requests as $request): ?>
                            <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                                <img src="<?php echo get_profile_picture($request['PassengerPicture']); ?>" 
                                     class="rounded-circle me-3" width="50" height="50" alt="Passenger">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo e($request['PassengerName']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo e($request['OriginLocation']); ?> → <?php echo e($request['DestinationLocation']); ?>
                                        <br>
                                        <?php echo format_date($request['DepartureDate']); ?> at <?php echo format_time($request['DepartureTime']); ?>
                                    </small>
                                </div>
                                <div>
                                    <a href="rides/manage-requests.php?id=<?php echo $request['RequestID']; ?>&action=approve" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="rides/manage-requests.php?id=<?php echo $request['RequestID']; ?>&action=reject" 
                                       class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center pt-2">
                            <a href="<?php echo SITE_URL; ?>/rides/requests.php" class="btn btn-outline-warning btn-sm">
                                View All Requests <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Upcoming Rides -->
            <?php if (!empty($upcoming_rides)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Rides</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($upcoming_rides as $ride): ?>
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-2">
                                                <i class="fas fa-map-marker-alt text-success me-2"></i>
                                                <?php echo e($ride['OriginLocation']); ?>
                                            </h6>
                                            <h6 class="mb-2">
                                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                <?php echo e($ride['DestinationLocation']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i><?php echo format_date($ride['DepartureDate']); ?>
                                                <i class="fas fa-clock ms-2 me-1"></i><?php echo format_time($ride['DepartureTime']); ?>
                                                <?php if ($ride['DriverID'] == $user_id): ?>
                                                    <span class="badge bg-success ms-2">You're Driving</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info ms-2">Passenger</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="rides/view.php?id=<?php echo $ride['RideID']; ?>" 
                                               class="btn btn-sm btn-outline-primary">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center pt-2">
                            <a href="<?php echo SITE_URL; ?>/rides/search.php" class="btn btn-outline-primary btn-sm">
                                View All Rides <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Completed Rides (for rating) -->
            <?php if (!empty($completed_rides)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Completed Rides</h5>
                        <a href="<?php echo SITE_URL; ?>/rides/history.php" class="btn btn-sm btn-light">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($completed_rides as $ride): ?>
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="mb-2">
                                                <i class="fas fa-map-marker-alt text-success me-2"></i>
                                                <?php echo e($ride['OriginLocation']); ?>
                                            </h6>
                                            <h6 class="mb-2">
                                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                <?php echo e($ride['DestinationLocation']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i><?php echo format_date($ride['DepartureDate']); ?>
                                                <i class="fas fa-clock ms-2 me-1"></i><?php echo format_time($ride['DepartureTime']); ?>
                                                <?php if ($ride['Distance']): ?>
                                                    <i class="fas fa-road ms-2 me-1"></i><?php echo number_format($ride['Distance'], 1); ?> km
                                                <?php endif; ?>
                                            </small>
                                            <?php if ($ride['DriverID'] != $user_id): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">Driver: <?php echo e($ride['DriverName']); ?></small>
                                                    <span class="ms-2"><?php echo display_star_rating($ride['DriverRating']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <?php if ($ride['DriverID'] != $user_id): ?>
                                                <?php if (empty($ride['MyRating'])): ?>
                                                    <button class="btn btn-sm btn-warning mb-2 w-100" 
                                                            onclick="showRatingModal(<?php echo $ride['DriverID']; ?>, '<?php echo addslashes(e($ride['DriverName'])); ?>', <?php echo $ride['RideID']; ?>)">
                                                        <i class="fas fa-star me-1"></i>Rate Driver
                                                    </button>
                                                <?php else: ?>
                                                    <small class="text-success d-block mb-2">
                                                        <i class="fas fa-check-circle me-1"></i>Rated: <?php echo display_star_rating($ride['MyRating']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <a href="rides/view.php?id=<?php echo $ride['RideID']; ?>" 
                                               class="btn btn-sm btn-outline-primary w-100">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Recent Ride History -->
            <?php if (!empty($recent_rides)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Ride History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Route</th>
                                        <th>Role</th>
                                        <th>Points</th>
                                        <th>CO₂ Saved</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_rides as $ride): ?>
                                        <tr>
                                            <td><?php echo format_date($ride['DepartureDate']); ?></td>
                                            <td>
                                                <small><?php echo e(substr($ride['OriginLocation'], 0, 20)); ?> → 
                                                <?php echo e(substr($ride['DestinationLocation'], 0, 20)); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $ride['Role'] === 'Driver' ? 'success' : 'info'; ?>">
                                                    <?php echo e($ride['Role']); ?>
                                                </span>
                                            </td>
                                            <td>+<?php echo $ride['EcoPointsEarned']; ?></td>
                                            <td><?php echo number_format($ride['CO2Saved'], 2); ?> kg</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/rides/history.php" class="btn btn-success">View Full History</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="rides/search.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Find Rides
                        </a>
                        <?php if ($user_role === 'Driver'): ?>
                            <a href="rides/create.php" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i>Offer a Ride
                            </a>
                        <?php endif; ?>
                        <a href="profile/edit.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Notifications -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Notifications</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($notifications)): ?>
                        <div class="list-group-item text-muted text-center">No notifications yet</div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <a href="notifications.php" class="list-group-item list-group-item-action <?php echo $notification['IsRead'] ? '' : 'bg-light'; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo e($notification['Title']); ?></h6>
                                    <small><?php echo time_ago($notification['CreatedAt']); ?></small>
                                </div>
                                <p class="mb-1 small"><?php echo e(substr($notification['Message'], 0, 60)); ?>...</p>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($notifications)): ?>
                    <div class="card-footer text-center">
                        <a href="notifications.php" class="text-decoration-none">View All</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sustainability Impact -->
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-seedling fa-3x mb-3"></i>
                    <h5>Your Impact</h5>
                    <p class="mb-2">You've saved</p>
                    <h2 class="fw-bold"><?php echo number_format($stats['total_co2_saved'], 1); ?> kg</h2>
                    <p class="mb-0">of CO₂ emissions!</p>
                    <small class="opacity-75">
                        That's equivalent to planting <?php echo ceil($stats['total_co2_saved'] / 20); ?> trees! 🌳
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="ratingForm" method="POST" action="rides/view.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="submit_rating">
                <input type="hidden" name="rated_user_id" id="rated_user_id">
                <input type="hidden" name="ride_id" id="rating_ride_id">
                
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-star me-2"></i>Rate <span id="rated_user_name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">Your Rating</label>
                        <div class="rating-stars" style="font-size: 2rem; cursor: pointer;">
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
function showRatingModal(userId, userName, rideId) {
    document.getElementById('rated_user_id').value = userId;
    document.getElementById('rated_user_name').textContent = userName;
    document.getElementById('rating_ride_id').value = rideId;
    document.getElementById('rating_value').value = '';
    document.getElementById('rating_text').textContent = '';
    document.getElementById('review').value = '';
    
    // Update form action to go to the specific ride
    document.getElementById('ratingForm').action = 'rides/view.php?id=' + rideId;
    
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

<?php include 'includes/footer.php'; ?>

