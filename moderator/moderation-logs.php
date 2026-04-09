<?php
/**
 * JuJuConnect - Moderation Logs
 * View history of all moderation actions
 */

require_once '../config/config.php';

// Check if user is moderator
require_role('Moderator');

$page_title = 'Moderation Logs';

// Pagination
$items_per_page = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Filters
$filter_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$filter_action = $_GET['action'] ?? 'all';

// Build query
$where_clauses = ["Action LIKE 'moderator_%' OR Action LIKE 'admin_%'"];
$params = [];

if ($filter_user > 0) {
    $where_clauses[] = "EntityType = 'User' AND EntityID = ?";
    $params[] = $filter_user;
}

if ($filter_action !== 'all') {
    $where_clauses[] = "Action LIKE ?";
    $params[] = "%{$filter_action}%";
}

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ActivityLogs $where_sql");
$count_stmt->execute($params);
$total_logs = $count_stmt->fetchColumn();

// Get logs
$params[] = $items_per_page;
$params[] = $offset;
$stmt = $pdo->prepare("
    SELECT al.*, u.FullName as ModeratorName
    FROM ActivityLogs al
    LEFT JOIN Users u ON al.UserID = u.UserID
    $where_sql
    ORDER BY al.CreatedAt DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$logs = $stmt->fetchAll();

$total_pages = ceil($total_logs / $items_per_page);

// Get user info if filtering by specific user
$filtered_user = null;
if ($filter_user > 0) {
    $filtered_user = get_user_by_id($pdo, $filter_user);
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-history text-secondary"></i> Moderation Logs</h1>
            <p class="text-muted mb-0">
                <?php if ($filtered_user): ?>
                    Viewing actions for: <strong><?php echo e($filtered_user['FullName']); ?></strong>
                <?php else: ?>
                    All moderation and admin actions
                <?php endif; ?>
            </p>
        </div>
        <div>
            <?php if ($filter_user > 0): ?>
                <a href="moderation-logs.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times"></i> Clear Filter
                </a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">User ID (to filter by specific user)</label>
                    <input type="number" class="form-control" name="user_id" 
                           value="<?php echo $filter_user > 0 ? $filter_user : ''; ?>" 
                           placeholder="Enter User ID...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Action Type</label>
                    <select class="form-select" name="action">
                        <option value="all" <?php echo $filter_action === 'all' ? 'selected' : ''; ?>>All Actions</option>
                        <option value="suspend" <?php echo $filter_action === 'suspend' ? 'selected' : ''; ?>>Suspend</option>
                        <option value="ban" <?php echo $filter_action === 'ban' ? 'selected' : ''; ?>>Ban</option>
                        <option value="activate" <?php echo $filter_action === 'activate' ? 'selected' : ''; ?>>Activate</option>
                        <option value="cancel" <?php echo $filter_action === 'cancel' ? 'selected' : ''; ?>>Cancel Ride</option>
                        <option value="resolve" <?php echo $filter_action === 'resolve' ? 'selected' : ''; ?>>Resolve Report</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>Moderator/Admin</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            // Determine action badge color
                            $badge_class = 'secondary';
                            if (str_contains($log['Action'], 'suspend')) $badge_class = 'warning';
                            if (str_contains($log['Action'], 'ban')) $badge_class = 'danger';
                            if (str_contains($log['Action'], 'activate')) $badge_class = 'success';
                            if (str_contains($log['Action'], 'resolve')) $badge_class = 'success';
                            if (str_contains($log['Action'], 'cancel')) $badge_class = 'danger';
                            if (str_contains($log['Action'], 'flag')) $badge_class = 'warning';
                            ?>
                            <tr>
                                <td><small class="text-muted">#<?php echo $log['LogID']; ?></small></td>
                                <td>
                                    <small>
                                        <?php echo date('M d, Y', strtotime($log['CreatedAt'])); ?><br>
                                        <span class="text-muted"><?php echo date('h:i A', strtotime($log['CreatedAt'])); ?></span>
                                    </small>
                                </td>
                                <td>
                                    <?php echo e($log['ModeratorName'] ?? 'System'); ?>
                                    <?php if (str_contains($log['Action'], 'admin_')): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php elseif (str_contains($log['Action'], 'moderator_')): ?>
                                        <span class="badge bg-info">Moderator</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <?php echo str_replace(['moderator_', 'admin_', '_'], ['', '', ' '], $log['Action']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?php echo e($log['EntityType']); ?> 
                                        <?php if ($log['EntityID']): ?>
                                            #<?php echo $log['EntityID']; ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php if ($log['EntityType'] === 'User' && $log['EntityID']): ?>
                                            <a href="<?php echo SITE_URL; ?>/profile/view.php?id=<?php echo $log['EntityID']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-user"></i> View User
                                            </a>
                                        <?php elseif ($log['EntityType'] === 'Ride' && $log['EntityID']): ?>
                                            <a href="<?php echo SITE_URL; ?>/rides/view.php?id=<?php echo $log['EntityID']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-car"></i> View Ride
                                            </a>
                                        <?php elseif ($log['EntityType'] === 'Report' && $log['EntityID']): ?>
                                            <a href="review-reports.php" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-flag"></i> View Reports
                                            </a>
                                        <?php endif; ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (empty($logs)): ?>
        <div class="text-center py-5">
            <i class="fas fa-history fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Logs Found</h4>
            <p class="text-muted">No moderation actions match your filter.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&user_id=<?php echo $filter_user; ?>&action=<?php echo $filter_action; ?>">
                        Previous
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === 1 || $i === $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&user_id=<?php echo $filter_user; ?>&action=<?php echo $filter_action; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php elseif ($i === $page - 3 || $i === $page + 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&user_id=<?php echo $filter_user; ?>&action=<?php echo $filter_action; ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Summary Card -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>About Moderation Logs</h6>
            <p class="mb-0 small text-muted">
                This page shows all actions performed by moderators and administrators on the platform. 
                Use the filters above to narrow down specific actions or users. All actions are logged 
                for accountability and audit purposes.
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

