<?php
require_once 'config/config.php';

$page_title = "Leaderboard";

// Get time period filter
$period = $_GET['period'] ?? 'all';
$valid_periods = ['all', 'month', 'week'];
if (!in_array($period, $valid_periods)) {
    $period = 'all';
}

// Build query based on period
$where_clause = "WHERE u.Status = 'Active'";
$join_clause = "";

if ($period === 'month') {
    $join_clause = "LEFT JOIN RideHistory rh ON u.UserID = rh.UserID AND rh.CompletedAt >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $order_clause = "ORDER BY period_points DESC, u.EcoPoints DESC";
    $select_points = "COALESCE(SUM(rh.EcoPointsEarned), 0) as period_points";
} elseif ($period === 'week') {
    $join_clause = "LEFT JOIN RideHistory rh ON u.UserID = rh.UserID AND rh.CompletedAt >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    $order_clause = "ORDER BY period_points DESC, u.EcoPoints DESC";
    $select_points = "COALESCE(SUM(rh.EcoPointsEarned), 0) as period_points";
} else {
    $order_clause = "ORDER BY u.EcoPoints DESC";
    $select_points = "u.EcoPoints as period_points";
}

// Get leaderboard data
$query = "
    SELECT 
        u.UserID,
        u.FullName,
        u.ProfilePicture,
        u.Role,
        u.EcoPoints,
        u.Rating,
        $select_points,
        (SELECT COUNT(DISTINCT RideID) FROM RideHistory WHERE UserID = u.UserID) as total_rides
    FROM Users u
    $join_clause
    $where_clause
";

if ($period !== 'all') {
    $query .= " GROUP BY u.UserID";
}

$query .= " $order_clause LIMIT " . LEADERBOARD_LIMIT;

$stmt = $pdo->query($query);
$leaderboard = $stmt->fetchAll();

// Get current user's position if logged in
$my_position = null;
$my_points = null;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $count_query = str_replace("SELECT", "SELECT COUNT(*) + 1 as position FROM (SELECT", $query);
    $count_query .= ") as ranked WHERE period_points > (SELECT $select_points FROM Users u $join_clause WHERE u.UserID = $user_id";
    if ($period !== 'all') {
        $count_query .= " GROUP BY u.UserID";
    }
    $count_query .= ")";
    
    $my_position = array_search($user_id, array_column($leaderboard, 'UserID'));
    if ($my_position !== false) {
        $my_position = $my_position + 1;
        $my_points = $leaderboard[$my_position - 1]['period_points'];
    }
}

// Get overall statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_users,
        COALESCE(SUM(EcoPoints), 0) as total_points,
        COALESCE(SUM((SELECT SUM(CO2Saved) FROM RideHistory WHERE UserID = Users.UserID)), 0) as total_co2
    FROM Users
    WHERE Status = 'Active'
";
$stmt = $pdo->query($stats_query);
$stats = $stmt->fetch();
?>

<?php include 'includes/header.php'; ?>

<div class="container my-4">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="display-4"><i class="fas fa-trophy text-warning me-3"></i>Leaderboard</h1>
        <p class="lead text-muted">Compete and climb the ranks for sustainable transportation!</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo number_format($stats['total_users']); ?></h4>
                    <small class="text-muted">Active Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo number_format($stats['total_points']); ?></h4>
                    <small class="text-muted">Total Eco-Points</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-leaf fa-2x text-success mb-2"></i>
                    <h4 class="fw-bold mb-0"><?php echo number_format($stats['total_co2'], 1); ?> kg</h4>
                    <small class="text-muted">CO₂ Saved</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Period Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter by Period</h5>
                <div class="btn-group" role="group">
                    <a href="?period=all" class="btn btn-outline-primary <?php echo $period === 'all' ? 'active' : ''; ?>">
                        All Time
                    </a>
                    <a href="?period=month" class="btn btn-outline-primary <?php echo $period === 'month' ? 'active' : ''; ?>">
                        This Month
                    </a>
                    <a href="?period=week" class="btn btn-outline-primary <?php echo $period === 'week' ? 'active' : ''; ?>">
                        This Week
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User's Position (if logged in) -->
    <?php if (is_logged_in() && $my_position): ?>
        <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-user-circle fa-2x me-3"></i>
            <div>
                <strong>Your Position:</strong> #<?php echo $my_position; ?> 
                <span class="ms-3"><i class="fas fa-trophy me-1"></i><?php echo number_format($my_points); ?> points</span>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Leaderboard Table -->
    <div class="card shadow-lg border-0">
        <div class="card-header bg-gradient-success text-white">
            <h4 class="mb-0">
                <i class="fas fa-ranking-star me-2"></i>
                Top <?php echo count($leaderboard); ?> Contributors
            </h4>
        </div>
        <div class="card-body p-0">
            <?php if (empty($leaderboard)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No users found</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="80">Rank</th>
                                <th>User</th>
                                <th class="text-center">Role</th>
                                <th class="text-center">Rating</th>
                                <th class="text-center">Rides</th>
                                <th class="text-center">
                                    <?php echo $period === 'all' ? 'Total Points' : 'Period Points'; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $index => $user): ?>
                                <?php
                                $rank = $index + 1;
                                $rank_class = '';
                                
                                if ($rank === 1) {
                                    $rank_class = 'text-warning fw-bold';
                                } elseif ($rank === 2) {
                                    $rank_class = 'text-secondary fw-bold';
                                } elseif ($rank === 3) {
                                    $rank_class = 'text-danger fw-bold';
                                }
                                
                                $is_current_user = (is_logged_in() && $user['UserID'] == $_SESSION['user_id']);
                                $row_class = $is_current_user ? 'table-primary' : '';
                                ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td class="<?php echo $rank_class; ?> text-center fs-4">
                                        #<?php echo $rank; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo get_profile_picture($user['ProfilePicture']); ?>" 
                                                 class="rounded-circle me-3" width="50" height="50" alt="User">
                                            <div>
                                                <h6 class="mb-0">
                                                    <?php echo e($user['FullName']); ?>
                                                    <?php if ($is_current_user): ?>
                                                        <span class="badge bg-primary ms-2">You</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-trophy me-1"></i><?php echo number_format($user['EcoPoints']); ?> total points
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $user['Role'] === 'Driver' ? 'success' : 'info'; ?>">
                                            <?php echo e($user['Role']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="small">
                                            <?php echo display_star_rating($user['Rating']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <strong><?php echo number_format($user['total_rides']); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <h5 class="mb-0 text-success">
                                            <i class="fas fa-trophy me-1"></i>
                                            <?php echo number_format($user['period_points']); ?>
                                        </h5>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Call to Action -->
    <?php if (is_logged_in()): ?>
        <div class="text-center mt-4">
            <div class="card border-success">
                <div class="card-body">
                    <h5><i class="fas fa-rocket me-2"></i>Climb the Leaderboard!</h5>
                    <p class="text-muted mb-3">Complete more rides to earn eco-points and improve your ranking</p>
                    <a href="rides/search.php" class="btn btn-success me-2">
                        <i class="fas fa-search me-2"></i>Find Rides
                    </a>
                    <?php if (has_role('Driver')): ?>
                        <a href="rides/create.php" class="btn btn-outline-success">
                            <i class="fas fa-plus-circle me-2"></i>Offer a Ride
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center mt-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h5><i class="fas fa-sign-in-alt me-2"></i>Join the Competition!</h5>
                    <p class="text-muted mb-3">Sign up now to start earning eco-points and compete on the leaderboard</p>
                    <a href="auth/register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Sign Up Now
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

