<?php
/**
 * JuJuConnect - Ride History
 * View all completed rides with rating functionality
 */

require_once '../config/config.php';
require_login();

$page_title = "Ride History";
$user_id = $_SESSION['user_id'];

// Pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Get total count
$count_stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT r.RideID)
    FROM Rides r
    LEFT JOIN RideRequests rr ON r.RideID = rr.RideID AND rr.PassengerID = ?
    WHERE r.Status = 'Completed'
    AND (rr.PassengerID = ? OR r.DriverID = ?)
    AND (rr.Status = 'Approved' OR r.DriverID = ?)
");
$count_stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$total_rides = $count_stmt->fetchColumn();

// Get completed rides
$stmt = $pdo->prepare("
    SELECT 
        r.RideID,
        r.OriginLocation,
        r.DestinationLocation,
        r.DepartureDate,
        r.DepartureTime,
        r.Distance,
        r.PricePerSeat,
        r.DriverID,
        u.FullName as DriverName,
        u.ProfilePicture as DriverPicture,
        u.Rating as DriverRating,
        rr.Status as RequestStatus,
        rr.SeatsRequested,
        CASE WHEN r.DriverID = ? THEN 'Driver' ELSE 'Passenger' END as MyRole,
        (SELECT Rating FROM Ratings WHERE RideID = r.RideID AND RaterID = ? AND RatedUserID = r.DriverID) as MyDriverRating,
        (SELECT COUNT(*) FROM RideRequests WHERE RideID = r.RideID AND Status = 'Approved') as PassengerCount
    FROM Rides r
    JOIN Users u ON r.DriverID = u.UserID
    LEFT JOIN RideRequests rr ON r.RideID = rr.RideID AND rr.PassengerID = ?
    WHERE r.Status = 'Completed'
    AND (rr.PassengerID = ? OR r.DriverID = ?)
    AND (rr.Status = 'Approved' OR r.DriverID = ?)
    ORDER BY r.DepartureDate DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $items_per_page, $offset]);
$completed_rides = $stmt->fetchAll();

$total_pages = ceil($total_rides / $items_per_page);

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-history me-2 text-success"></i>Ride History</h2>
            <p class="text-muted mb-0">Your completed rides and ratings</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
    
    <?php display_flash_message(); ?>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-2x text-primary mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo $total_rides; ?></h4>
                    <small class="text-muted">Completed Rides</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $driver_count = 0;
                    $passenger_count = 0;
                    foreach ($completed_rides as $ride) {
                        if ($ride['MyRole'] === 'Driver') $driver_count++;
                        else $passenger_count++;
                    }
                    ?>
                    <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo $driver_count; ?> / <?php echo $passenger_count; ?></h4>
                    <small class="text-muted">As Driver / Passenger</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $total_distance = 0;
                    foreach ($completed_rides as $ride) {
                        $total_distance += $ride['Distance'] ?? 0;
                    }
                    ?>
                    <i class="fas fa-road fa-2x text-info mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo number_format($total_distance, 0); ?> km</h4>
                    <small class="text-muted">Total Distance</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rides List -->
    <?php if (!empty($completed_rides)): ?>
        <?php foreach ($completed_rides as $ride): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="d-flex align-items-start mb-2">
                                <span class="badge bg-<?php echo $ride['MyRole'] === 'Driver' ? 'success' : 'info'; ?> me-2">
                                    <?php echo $ride['MyRole']; ?>
                                </span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <i class="fas fa-map-marker-alt text-success me-1"></i>
                                        <?php echo e($ride['OriginLocation']); ?>
                                    </h6>
                                    <h6 class="mb-2">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                        <?php echo e($ride['DestinationLocation']); ?>
                                    </h6>
                                </div>
                            </div>
                            
                            <div class="small text-muted">
                                <i class="fas fa-calendar me-1"></i><?php echo format_date($ride['DepartureDate']); ?>
                                <i class="fas fa-clock ms-2 me-1"></i><?php echo format_time($ride['DepartureTime']); ?>
                                <?php if ($ride['Distance']): ?>
                                    <i class="fas fa-road ms-2 me-1"></i><?php echo number_format($ride['Distance'], 1); ?> km
                                <?php endif; ?>
                                <?php if ($ride['MyRole'] === 'Driver'): ?>
                                    <i class="fas fa-users ms-2 me-1"></i><?php echo $ride['PassengerCount']; ?> passenger(s)
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($ride['MyRole'] === 'Passenger'): ?>
                                <div class="mt-2">
                                    <img src="<?php echo get_profile_picture($ride['DriverPicture']); ?>" 
                                         class="rounded-circle me-2" width="30" height="30" alt="Driver">
                                    <small><strong>Driver:</strong> <?php echo e($ride['DriverName']); ?></small>
                                    <span class="ms-2"><?php echo display_star_rating($ride['DriverRating']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-3 text-center border-start">
                            <?php if ($ride['MyRole'] === 'Passenger'): ?>
                                <?php if (empty($ride['MyDriverRating'])): ?>
                                    <button class="btn btn-warning btn-sm w-100 mb-2" 
                                            onclick="showRatingModal(<?php echo $ride['DriverID']; ?>, '<?php echo addslashes(e($ride['DriverName'])); ?>', <?php echo $ride['RideID']; ?>)">
                                        <i class="fas fa-star me-1"></i>Rate Driver
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-success mb-2 py-2">
                                        <small><i class="fas fa-check-circle me-1"></i>Rated</small>
                                        <div><?php echo display_star_rating($ride['MyDriverRating']); ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <small class="text-muted">You were the driver</small>
                            <?php endif; ?>
                            
                            <?php if ($ride['SeatsRequested']): ?>
                                <small class="text-muted d-block mt-2">
                                    <?php echo $ride['SeatsRequested']; ?> seat(s) • 
                                    RM <?php echo number_format($ride['PricePerSeat'] * $ride['SeatsRequested'], 2); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-2 text-end">
                            <a href="view.php?id=<?php echo $ride['RideID']; ?>&ref=history" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-eye me-1"></i>View Details
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
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i === 1 || $i === $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php elseif ($i === $page - 3 || $i === $page + 3): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-car fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Completed Rides Yet</h4>
            <p class="text-muted">Complete some rides to see your history here!</p>
            <a href="<?php echo SITE_URL; ?>/rides/search.php" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Find Rides
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="ratingForm" method="POST" action="view.php">
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
    document.getElementById('ratingForm').action = 'view.php?id=' + rideId + '&ref=history';
    
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
