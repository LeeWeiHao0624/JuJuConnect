<?php
require_once '../config/config.php';
require_login();

// Check if user is Admin or Moderator - they can't request rides
if (has_any_role(['Admin', 'Moderator'])) {
    set_flash_message('error', 'Admins and Moderators cannot request rides.');
    redirect(SITE_URL . '/rides/view.php?id=' . ($_GET['ride_id'] ?? 0));
}

$ride_id = intval($_GET['ride_id'] ?? 0);

if (!$ride_id) {
    set_flash_message('error', 'Invalid ride ID.');
    redirect(SITE_URL . '/rides/search.php');
}

// Get ride details
$stmt = $pdo->prepare("
    SELECT r.*, u.FullName as DriverName, u.UserID as DriverID
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

// Check if user is the driver
if ($ride['DriverID'] == $_SESSION['user_id']) {
    set_flash_message('error', 'You cannot request your own ride.');
    redirect(SITE_URL . '/rides/view.php?id=' . $ride_id);
}

// Check if already requested
$stmt = $pdo->prepare("SELECT * FROM RideRequests WHERE RideID = ? AND PassengerID = ?");
$stmt->execute([$ride_id, $_SESSION['user_id']]);
$existing_request = $stmt->fetch();

if ($existing_request) {
    set_flash_message('warning', 'You have already requested this ride.');
    redirect(SITE_URL . '/rides/view.php?id=' . $ride_id);
}

$page_title = "Request Ride";
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid security token. Please try again.";
    } else {
        $seats_requested = intval($_POST['seats_requested'] ?? 1);
        $message = sanitize_input($_POST['message'] ?? '');
        
        if ($seats_requested < 1 || $seats_requested > $ride['AvailableSeats']) {
            $error = "Invalid number of seats requested.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO RideRequests (RideID, PassengerID, SeatsRequested, Message)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$ride_id, $_SESSION['user_id'], $seats_requested, $message]);
                
                // Log activity
                log_activity($pdo, $_SESSION['user_id'], 'request_ride', 'RideRequest', $pdo->lastInsertId());
                
                set_flash_message('success', 'Ride request sent successfully! Wait for driver approval.');
                redirect(SITE_URL . '/rides/view.php?id=' . $ride_id);
                
            } catch (PDOException $e) {
                error_log("Request ride error: " . $e->getMessage());
                $error = "Failed to send request. Please try again.";
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Request Ride</h4>
                </div>
                <div class="card-body">
                    <!-- Ride Summary -->
                    <div class="alert alert-info">
                        <h6 class="mb-2">Ride Details</h6>
                        <p class="mb-1"><strong>From:</strong> <?php echo e($ride['OriginLocation']); ?></p>
                        <p class="mb-1"><strong>To:</strong> <?php echo e($ride['DestinationLocation']); ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo format_date($ride['DepartureDate']); ?> at <?php echo format_time($ride['DepartureTime']); ?></p>
                        <p class="mb-1"><strong>Driver:</strong> <?php echo e($ride['DriverName']); ?></p>
                        <p class="mb-0"><strong>Available Seats:</strong> <?php echo $ride['AvailableSeats']; ?></p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="seats_requested" class="form-label">Number of Seats *</label>
                            <select class="form-select" id="seats_requested" name="seats_requested" required>
                                <?php for ($i = 1; $i <= $ride['AvailableSeats']; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> seat<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                            <small class="text-muted">Price: RM <?php echo number_format($ride['PricePerSeat'], 2); ?> per seat</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message to Driver (Optional)</label>
                            <textarea class="form-control" id="message" name="message" rows="3" 
                                      placeholder="Introduce yourself and mention any special requests..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="mb-2"><i class="fas fa-calculator me-2"></i>Cost Estimate</h6>
                                    <p class="mb-0">
                                        Total: RM <span id="totalCost"><?php echo number_format($ride['PricePerSeat'], 2); ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="view.php?id=<?php echo $ride_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const pricePerSeat = <?php echo $ride['PricePerSeat']; ?>;

document.getElementById('seats_requested').addEventListener('change', function() {
    const seats = parseInt(this.value);
    const total = (pricePerSeat * seats).toFixed(2);
    document.getElementById('totalCost').textContent = total;
});
</script>

<?php include '../includes/footer.php'; ?>

