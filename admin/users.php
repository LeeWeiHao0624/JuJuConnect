<?php
/**
 * JuJuConnect - Admin User Management
 * Manage all users on the platform
 */

require_once '../config/config.php';

// Check if user is admin
require_role('Admin');

$page_title = 'Manage Users';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($user_id > 0) {
        try {
            switch ($action) {
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE Users SET Status = 'Active' WHERE UserID = ?");
                    $stmt->execute([$user_id]);
                    set_flash_message('success', 'User activated successfully.');
                    break;
                    
                case 'suspend':
                    $stmt = $pdo->prepare("UPDATE Users SET Status = 'Suspended' WHERE UserID = ?");
                    $stmt->execute([$user_id]);
                    set_flash_message('success', 'User suspended successfully.');
                    break;
                    
                case 'ban':
                    $stmt = $pdo->prepare("UPDATE Users SET Status = 'Banned' WHERE UserID = ?");
                    $stmt->execute([$user_id]);
                    set_flash_message('success', 'User banned successfully.');
                    break;
                    
                case 'delete':
                    // Note: This will cascade delete related records
                    $stmt = $pdo->prepare("DELETE FROM Users WHERE UserID = ?");
                    $stmt->execute([$user_id]);
                    set_flash_message('success', 'User deleted successfully.');
                    break;
            }
            
            // Log activity
            log_activity($pdo, $_SESSION['user_id'], "admin_{$action}_user", 'User', $user_id);
            
        } catch (PDOException $e) {
            error_log("Admin user action error: " . $e->getMessage());
            set_flash_message('error', 'Failed to perform action. Please try again.');
        }
    }
    
    redirect(SITE_URL . '/admin/users.php');
}

// Pagination and filtering
$items_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

$filter_role = $_GET['role'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where_clauses = [];
$params = [];

if ($filter_role !== 'all') {
    $where_clauses[] = "Role = ?";
    $params[] = $filter_role;
}

if ($filter_status !== 'all') {
    $where_clauses[] = "Status = ?";
    $params[] = $filter_status;
}

if (!empty($search)) {
    $where_clauses[] = "(FullName LIKE ? OR Email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM Users $where_sql");
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();

// Get users
$params[] = $items_per_page;
$params[] = $offset;
$stmt = $pdo->prepare("
    SELECT * FROM Users 
    $where_sql
    ORDER BY CreatedAt DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$users = $stmt->fetchAll();

$total_pages = ceil($total_users / $items_per_page);

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN Status = 'Active' THEN 1 END) as active,
        COUNT(CASE WHEN Status = 'Suspended' THEN 1 END) as suspended,
        COUNT(CASE WHEN Status = 'Banned' THEN 1 END) as banned,
        COUNT(CASE WHEN Role = 'Driver' THEN 1 END) as drivers,
        COUNT(CASE WHEN Role = 'Passenger' THEN 1 END) as passengers,
        COUNT(CASE WHEN Role = 'Admin' THEN 1 END) as admins,
        COUNT(CASE WHEN Role = 'Moderator' THEN 1 END) as moderators
    FROM Users
");
$stats = $stats_stmt->fetch();

include '../includes/header.php';
?>

<div class="container my-5">
    <?php display_flash_message(); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-users text-primary"></i> Manage Users</h1>
            <p class="text-muted mb-0">Total: <?php echo number_format($total_users); ?> users</p>
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
            <div class="card text-center border-success">
                <div class="card-body">
                    <h4 class="mb-0 text-success"><?php echo $stats['active']; ?></h4>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h4 class="mb-0 text-warning"><?php echo $stats['suspended']; ?></h4>
                    <small class="text-muted">Suspended</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h4 class="mb-0 text-danger"><?php echo $stats['banned']; ?></h4>
                    <small class="text-muted">Banned</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h4 class="mb-0 text-info"><?php echo $stats['drivers']; ?></h4>
                    <small class="text-muted">Drivers</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-secondary">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo $stats['passengers']; ?></h4>
                    <small class="text-muted">Passengers</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo e($search); ?>" placeholder="Name or email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <option value="all" <?php echo $filter_role === 'all' ? 'selected' : ''; ?>>All Roles</option>
                        <option value="Driver" <?php echo $filter_role === 'Driver' ? 'selected' : ''; ?>>Driver</option>
                        <option value="Passenger" <?php echo $filter_role === 'Passenger' ? 'selected' : ''; ?>>Passenger</option>
                        <option value="Admin" <?php echo $filter_role === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Moderator" <?php echo $filter_role === 'Moderator' ? 'selected' : ''; ?>>Moderator</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="Active" <?php echo $filter_status === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Suspended" <?php echo $filter_status === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                        <option value="Banned" <?php echo $filter_status === 'Banned' ? 'selected' : ''; ?>>Banned</option>
                        <option value="Pending" <?php echo $filter_status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Eco Points</th>
                            <th>Rating</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo get_profile_picture($user['ProfilePicture']); ?>" 
                                             class="rounded-circle me-2" width="40" height="40" alt="User">
                                        <div>
                                            <div class="fw-bold"><?php echo e($user['FullName']); ?></div>
                                            <small class="text-muted"><?php echo e($user['Email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo e($user['Role']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch ($user['Status']) {
                                        case 'Active': $status_class = 'success'; break;
                                        case 'Suspended': $status_class = 'warning'; break;
                                        case 'Banned': $status_class = 'danger'; break;
                                        default: $status_class = 'secondary';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo e($user['Status']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($user['EcoPoints']); ?></td>
                                <td><?php echo display_star_rating($user['Rating']); ?></td>
                                <td><?php echo format_date($user['CreatedAt'], 'M d, Y'); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- View Button -->
                                        <a href="<?php echo SITE_URL; ?>/profile/view.php?id=<?php echo $user['UserID']; ?>" 
                                           class="btn btn-sm btn-primary" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if ($user['Status'] !== 'Active'): ?>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="dropdown-item text-success" 
                                                                onclick="return confirm('Activate this user?');">
                                                            <i class="fas fa-check me-2"></i>Activate
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($user['Status'] !== 'Suspended'): ?>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                                        <input type="hidden" name="action" value="suspend">
                                                        <button type="submit" class="dropdown-item text-warning" 
                                                                onclick="return confirm('Suspend this user?');">
                                                            <i class="fas fa-pause me-2"></i>Suspend
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($user['Status'] !== 'Banned' && $user['Role'] !== 'Admin'): ?>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                                        <input type="hidden" name="action" value="ban">
                                                        <button type="submit" class="dropdown-item text-danger" 
                                                                onclick="return confirm('Ban this user? This is a serious action.');">
                                                            <i class="fas fa-ban me-2"></i>Ban
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($user['Role'] !== 'Admin'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="dropdown-item text-danger" 
                                                                onclick="return confirm('DELETE this user permanently? This cannot be undone!');">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&role=<?php echo $filter_role; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">
                        Previous
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === 1 || $i === $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $filter_role; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php elseif ($i === $page - 3 || $i === $page + 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&role=<?php echo $filter_role; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

