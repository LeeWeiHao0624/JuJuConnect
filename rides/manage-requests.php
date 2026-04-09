<?php
/**
 * JuJuConnect - Manage Ride Requests
 * Handle approve/reject actions for ride requests
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
require_login();

$request_id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate input
if (!$request_id || !in_array($action, ['approve', 'reject'])) {
    set_flash_message('error', 'Invalid request.');
    redirect(SITE_URL . '/dashboard.php');
}

try {
    // Get request details
    $stmt = $pdo->prepare("
        SELECT rr.*, r.DriverID, r.AvailableSeats, r.OriginLocation, r.DestinationLocation,
               u.FullName as PassengerName
        FROM RideRequests rr
        JOIN Rides r ON rr.RideID = r.RideID
        JOIN Users u ON rr.PassengerID = u.UserID
        WHERE rr.RequestID = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();
    
    if (!$request) {
        set_flash_message('error', 'Request not found.');
        redirect(SITE_URL . '/dashboard.php');
    }
    
    // Check if current user is the driver
    if ($request['DriverID'] != $user_id) {
        set_flash_message('error', 'You are not authorized to manage this request.');
        redirect(SITE_URL . '/dashboard.php');
    }
    
    // Check if request is still pending
    if ($request['Status'] !== 'Pending') {
        set_flash_message('warning', 'This request has already been processed.');
        redirect(SITE_URL . '/rides/view.php?id=' . $request['RideID']);
    }
    
    if ($action === 'approve') {
        // Check if enough seats available
        if ($request['AvailableSeats'] < $request['SeatsRequested']) {
            set_flash_message('error', 'Not enough seats available for this request.');
            redirect(SITE_URL . '/rides/view.php?id=' . $request['RideID']);
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Update request status
            $stmt = $pdo->prepare("
                UPDATE RideRequests 
                SET Status = 'Approved', RespondedAt = NOW() 
                WHERE RequestID = ?
            ");
            $stmt->execute([$request_id]);
            
            // Update available seats (trigger will handle this, but we can do it manually too)
            $stmt = $pdo->prepare("
                UPDATE Rides 
                SET AvailableSeats = AvailableSeats - ? 
                WHERE RideID = ?
            ");
            $stmt->execute([$request['SeatsRequested'], $request['RideID']]);
            
            // Check if ride is now full
            $stmt = $pdo->prepare("SELECT AvailableSeats FROM Rides WHERE RideID = ?");
            $stmt->execute([$request['RideID']]);
            $remaining_seats = $stmt->fetchColumn();
            
            if ($remaining_seats <= 0) {
                $stmt = $pdo->prepare("UPDATE Rides SET Status = 'Full' WHERE RideID = ?");
                $stmt->execute([$request['RideID']]);
                
                // Auto-reject all other pending requests
                $stmt = $pdo->prepare("
                    SELECT rr.RequestID, rr.PassengerID, u.FullName
                    FROM RideRequests rr
                    JOIN Users u ON rr.PassengerID = u.UserID
                    WHERE rr.RideID = ? AND rr.Status = 'Pending'
                ");
                $stmt->execute([$request['RideID']]);
                $pending_requests = $stmt->fetchAll();
                
                // Reject each pending request and notify
                foreach ($pending_requests as $pending) {
                    $stmt = $pdo->prepare("
                        UPDATE RideRequests 
                        SET Status = 'Rejected', RespondedAt = NOW() 
                        WHERE RequestID = ?
                    ");
                    $stmt->execute([$pending['RequestID']]);
                    
                    // Notify passenger
                    create_notification(
                        $pdo, 
                        $pending['PassengerID'], 
                        'Request Rejected', 
                        'Ride Request - Ride Full',
                        "Unfortunately, the ride from {$request['OriginLocation']} to {$request['DestinationLocation']} is now full. Your request has been automatically rejected.",
                        $request['RideID']
                    );
                }
            }
            
            // Create notification for passenger
            create_notification(
                $pdo, 
                $request['PassengerID'], 
                'Request Approved', 
                'Ride Request Approved!',
                "Your request for the ride from {$request['OriginLocation']} to {$request['DestinationLocation']} has been approved!",
                $request['RideID']
            );
            
            // Log activity
            log_activity($pdo, $user_id, 'approve_request', 'RideRequest', $request_id);
            
            $pdo->commit();
            
            set_flash_message('success', "Request from {$request['PassengerName']} has been approved!");
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Approve request error: " . $e->getMessage());
            set_flash_message('error', 'Failed to approve request. Please try again.');
        }
        
    } elseif ($action === 'reject') {
        // Update request status
        $stmt = $pdo->prepare("
            UPDATE RideRequests 
            SET Status = 'Rejected', RespondedAt = NOW() 
            WHERE RequestID = ?
        ");
        $stmt->execute([$request_id]);
        
        // Create notification for passenger
        create_notification(
            $pdo, 
            $request['PassengerID'], 
            'Request Rejected', 
            'Ride Request Not Approved',
            "Unfortunately, your request for the ride from {$request['OriginLocation']} to {$request['DestinationLocation']} was not approved.",
            $request['RideID']
        );
        
        // Log activity
        log_activity($pdo, $user_id, 'reject_request', 'RideRequest', $request_id);
        
        set_flash_message('info', "Request from {$request['PassengerName']} has been rejected.");
    }
    
} catch (PDOException $e) {
    error_log("Manage request error: " . $e->getMessage());
    set_flash_message('error', 'Database error occurred. Please try again.');
}

// Redirect back
$redirect_to = $_GET['redirect'] ?? 'dashboard';
if ($redirect_to === 'ride' && isset($request['RideID'])) {
    redirect(SITE_URL . '/rides/view.php?id=' . $request['RideID']);
} else {
    redirect(SITE_URL . '/dashboard.php');
}
?>

