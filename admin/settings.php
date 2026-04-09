<?php
/**
 * JuJuConnect - Admin System Settings
 * Configure system-wide settings
 */

require_once '../config/config.php';

// Check if user is admin
require_role('Admin');

$page_title = 'System Settings';

// Get current settings
$stmt = $pdo->query("SELECT * FROM SystemSettings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['SettingKey']] = $row['SettingValue'];
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token') {
                $stmt = $pdo->prepare("
                    INSERT INTO SystemSettings (SettingKey, SettingValue) 
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE SettingValue = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
        }
        
        log_activity($pdo, $_SESSION['user_id'], 'update_settings', 'SystemSettings', 0);
        set_flash_message('success', 'Settings updated successfully!');
        redirect(SITE_URL . '/admin/settings.php');
        
    } catch (PDOException $e) {
        error_log("Settings update error: " . $e->getMessage());
        set_flash_message('error', 'Failed to update settings.');
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <?php display_flash_message(); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-wrench text-secondary"></i> System Settings</h1>
            <p class="text-muted mb-0">Configure platform-wide settings</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST">
                <?php echo csrf_field(); ?>
                
                <!-- General Settings -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Site Name</label>
                            <input type="text" class="form-control" name="site_name" 
                                   value="<?php echo e($settings['site_name'] ?? 'JuJuConnect'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Description</label>
                            <textarea class="form-control" name="site_description" rows="3"><?php echo e($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="email" class="form-control" name="contact_email" 
                                   value="<?php echo e($settings['contact_email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Eco-Points Settings -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-leaf me-2"></i>Eco-Points Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Base Points per Ride</label>
                            <input type="number" class="form-control" name="base_points_per_ride" 
                                   value="<?php echo e($settings['base_points_per_ride'] ?? '10'); ?>">
                            <small class="text-muted">Points awarded for completing a ride</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Points per Kilometer</label>
                            <input type="number" step="0.1" class="form-control" name="points_per_km" 
                                   value="<?php echo e($settings['points_per_km'] ?? '1'); ?>">
                            <small class="text-muted">Additional points per kilometer traveled</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Driver Bonus per Passenger</label>
                            <input type="number" class="form-control" name="driver_bonus_per_passenger" 
                                   value="<?php echo e($settings['driver_bonus_per_passenger'] ?? '5'); ?>">
                            <small class="text-muted">Extra points for drivers per passenger</small>
                        </div>
                    </div>
                </div>

                <!-- Ride Settings -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-car me-2"></i>Ride Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Maximum Seats per Ride</label>
                            <input type="number" class="form-control" name="max_seats_per_ride" 
                                   value="<?php echo e($settings['max_seats_per_ride'] ?? '7'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Minimum Price per Seat (RM)</label>
                            <input type="number" step="0.01" class="form-control" name="min_price_per_seat" 
                                   value="<?php echo e($settings['min_price_per_seat'] ?? '1.00'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maximum Price per Seat (RM)</label>
                            <input type="number" step="0.01" class="form-control" name="max_price_per_seat" 
                                   value="<?php echo e($settings['max_price_per_seat'] ?? '100.00'); ?>">
                        </div>
                    </div>
                </div>

                <!-- Feature Toggles -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Feature Toggles</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="enable_registration" value="1" 
                                   <?php echo ($settings['enable_registration'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Enable User Registration</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="enable_notifications" value="1" 
                                   <?php echo ($settings['enable_notifications'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Enable Notifications</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" 
                                   <?php echo ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Maintenance Mode</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <!-- System Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    <p><strong>Database:</strong> MySQL</p>
                    <p><strong>Platform:</strong> JuJuConnect v1.0</p>
                    <p class="mb-0"><strong>Last Updated:</strong> <?php echo date('M d, Y'); ?></p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Manage Users
                    </a>
                    <a href="rides.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-car me-2"></i>Manage Rides
                    </a>
                    <a href="reports.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-flag me-2"></i>View Reports
                    </a>
                    <a href="<?php echo SITE_URL; ?>/leaderboard.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-trophy me-2"></i>Leaderboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

