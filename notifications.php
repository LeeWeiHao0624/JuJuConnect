<?php
/**
 * JuJuConnect - Notifications Page
 * Display and manage user notifications
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
require_login();

$page_title = 'Notifications';
$user_id = $_SESSION['user_id'];

// Handle AJAX requests for marking notifications as read/deleted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                $notification_id = filter_var($_POST['notification_id'] ?? 0, FILTER_VALIDATE_INT);
                if ($notification_id) {
                    $stmt = $pdo->prepare("
                        UPDATE Notifications 
                        SET IsRead = 1 
                        WHERE NotificationID = ? AND UserID = ?
                    ");
                    $stmt->execute([$notification_id, $user_id]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
                }
                exit;
                
            case 'mark_all_read':
                $stmt = $pdo->prepare("
                    UPDATE Notifications 
                    SET IsRead = 1 
                    WHERE UserID = ? AND IsRead = 0
                ");
                $stmt->execute([$user_id]);
                echo json_encode(['success' => true]);
                exit;
                
            case 'delete':
                $notification_id = filter_var($_POST['notification_id'] ?? 0, FILTER_VALIDATE_INT);
                if ($notification_id) {
                    $stmt = $pdo->prepare("
                        DELETE FROM Notifications 
                        WHERE NotificationID = ? AND UserID = ?
                    ");
                    $stmt->execute([$notification_id, $user_id]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
                }
                exit;
                
            case 'delete_all_read':
                $stmt = $pdo->prepare("
                    DELETE FROM Notifications 
                    WHERE UserID = ? AND IsRead = 1
                ");
                $stmt->execute([$user_id]);
                echo json_encode(['success' => true]);
                exit;
        }
    }
}

// Pagination
$items_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Filter by read status
$filter = $_GET['filter'] ?? 'all'; // all, unread, read

// Build query based on filter
$where_clause = "WHERE UserID = ?";
$params = [$user_id];

if ($filter === 'unread') {
    $where_clause .= " AND IsRead = 0";
} elseif ($filter === 'read') {
    $where_clause .= " AND IsRead = 1";
}

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications $where_clause");
$count_stmt->execute($params);
$total_notifications = $count_stmt->fetchColumn();

// Get notifications
$stmt = $pdo->prepare("
    SELECT * FROM Notifications 
    $where_clause
    ORDER BY CreatedAt DESC 
    LIMIT ? OFFSET ?
");
$params[] = $items_per_page;
$params[] = $offset;
$stmt->execute($params);
$notifications = $stmt->fetchAll();

// Calculate pagination
$total_pages = ceil($total_notifications / $items_per_page);

// Get unread count
$unread_count = get_unread_notifications_count($pdo, $user_id);

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-2">
                        <i class="fas fa-bell text-success"></i> Notifications
                    </h1>
                    <p class="text-muted mb-0">
                        <?php if ($unread_count > 0): ?>
                            You have <strong class="text-danger"><?php echo $unread_count; ?></strong> unread notification<?php echo $unread_count > 1 ? 's' : ''; ?>
                        <?php else: ?>
                            All caught up! No unread notifications.
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <?php if ($unread_count > 0): ?>
                        <button class="btn btn-outline-success" id="markAllReadBtn">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-outline-danger ms-2" id="deleteAllReadBtn">
                        <i class="fas fa-trash"></i> Clear Read
                    </button>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                       href="?filter=all">
                        <i class="fas fa-list"></i> All (<?php echo $total_notifications; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'unread' ? 'active' : ''; ?>" 
                       href="?filter=unread">
                        <i class="fas fa-envelope"></i> Unread (<?php echo $unread_count; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'read' ? 'active' : ''; ?>" 
                       href="?filter=read">
                        <i class="fas fa-envelope-open"></i> Read
                    </a>
                </li>
            </ul>

            <!-- Notifications List -->
            <?php if (count($notifications) > 0): ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                        // Determine icon and color based on notification type
                        $icon = 'fa-bell';
                        $color = 'primary';
                        
                        switch ($notification['Type']) {
                            case 'Ride Request':
                                $icon = 'fa-car';
                                $color = 'info';
                                break;
                            case 'Request Approved':
                                $icon = 'fa-check-circle';
                                $color = 'success';
                                break;
                            case 'Request Rejected':
                                $icon = 'fa-times-circle';
                                $color = 'warning';
                                break;
                            case 'Ride Cancelled':
                                $icon = 'fa-ban';
                                $color = 'danger';
                                break;
                            case 'New Rating':
                                $icon = 'fa-star';
                                $color = 'warning';
                                break;
                            case 'Achievement':
                                $icon = 'fa-trophy';
                                $color = 'success';
                                break;
                            case 'System':
                                $icon = 'fa-info-circle';
                                $color = 'secondary';
                                break;
                        }
                        
                        $is_unread = !$notification['IsRead'];
                        $time_ago = time_ago($notification['CreatedAt']);
                        ?>
                        
                        <div class="card notification-card mb-3 <?php echo $is_unread ? 'unread' : 'read'; ?>" 
                             data-notification-id="<?php echo $notification['NotificationID']; ?>">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Icon -->
                                    <div class="col-auto">
                                        <div class="notification-icon bg-<?php echo $color; ?>">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="col">
                                        <h5 class="mb-1">
                                            <?php echo e($notification['Title']); ?>
                                            <?php if ($is_unread): ?>
                                                <span class="badge bg-danger ms-2">New</span>
                                            <?php endif; ?>
                                        </h5>
                                        <p class="mb-2 text-muted">
                                            <?php echo nl2br(e($notification['Message'])); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="far fa-clock"></i> <?php echo $time_ago; ?>
                                        </small>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="col-auto">
                                        <?php if ($is_unread): ?>
                                            <button class="btn btn-sm btn-outline-success mark-read-btn me-1" 
                                                    title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger delete-notification-btn" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Notification pagination">
                        <ul class="pagination justify-content-center">
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
                <!-- No Notifications -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-bell-slash" style="font-size: 5rem; color: #e0e0e0;"></i>
                    </div>
                    <h3 class="text-muted mb-3">No Notifications</h3>
                    <p class="text-muted">
                        <?php if ($filter === 'unread'): ?>
                            You have no unread notifications.
                        <?php elseif ($filter === 'read'): ?>
                            You have no read notifications.
                        <?php else: ?>
                            You don't have any notifications yet.
                        <?php endif; ?>
                    </p>
                    <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-success mt-3">
                        <i class="fas fa-dashboard"></i> Go to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.notification-card {
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.notification-card.unread {
    background-color: #f8f9fa;
    border-left-color: #28a745;
}

.notification-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.notification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.notification-card .btn-sm {
    padding: 0.25rem 0.5rem;
}

.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #28a745;
    border-color: #dee2e6 #dee2e6 #fff;
}

.nav-tabs .nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark single notification as read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.notification-card');
            const notificationId = card.dataset.notificationId;
            
            markAsRead(notificationId, card);
        });
    });
    
    // Mark all notifications as read
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            if (confirm('Mark all notifications as read?')) {
                fetch('notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_all_read'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('All notifications marked as read', 'success');
                        setTimeout(() => location.reload(), 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to mark notifications as read', 'error');
                });
            }
        });
    }
    
    // Delete single notification
    document.querySelectorAll('.delete-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.notification-card');
            const notificationId = card.dataset.notificationId;
            
            if (confirm('Delete this notification?')) {
                deleteNotification(notificationId, card);
            }
        });
    });
    
    // Delete all read notifications
    const deleteAllReadBtn = document.getElementById('deleteAllReadBtn');
    if (deleteAllReadBtn) {
        deleteAllReadBtn.addEventListener('click', function() {
            if (confirm('Delete all read notifications? This action cannot be undone.')) {
                fetch('notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_all_read'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Read notifications deleted', 'success');
                        setTimeout(() => location.reload(), 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to delete notifications', 'error');
                });
            }
        });
    }
});

function markAsRead(notificationId, cardElement) {
    fetch('notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_read&notification_id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cardElement.classList.remove('unread');
            cardElement.classList.add('read');
            const badge = cardElement.querySelector('.badge');
            if (badge) badge.remove();
            const markBtn = cardElement.querySelector('.mark-read-btn');
            if (markBtn) markBtn.remove();
            
            showToast('Notification marked as read', 'success');
            
            // Update badge count in header
            updateNotificationBadge();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to mark notification as read', 'error');
    });
}

function deleteNotification(notificationId, cardElement) {
    fetch('notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&notification_id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fade out and remove
            cardElement.style.transition = 'opacity 0.3s ease';
            cardElement.style.opacity = '0';
            setTimeout(() => {
                cardElement.remove();
                
                // Check if no more notifications
                const remainingNotifications = document.querySelectorAll('.notification-card').length;
                if (remainingNotifications === 0) {
                    location.reload();
                }
            }, 300);
            
            showToast('Notification deleted', 'success');
            
            // Update badge count in header
            updateNotificationBadge();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to delete notification', 'error');
    });
}

function updateNotificationBadge() {
    // Reload the page to update the badge count (simple approach)
    // In production, you might want to fetch the count via AJAX
    setTimeout(() => {
        const badge = document.querySelector('#notificationDropdown .badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent);
            if (currentCount > 1) {
                badge.textContent = currentCount - 1;
            } else {
                badge.remove();
            }
        }
    }, 500);
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>

