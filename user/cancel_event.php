<?php
session_start();
include_once '../includes/db_connect.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to continue']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $user_id = $_SESSION['user_id'];
    $event_id = intval($_POST['event_id']);
    
    try {
        $conn->begin_transaction();
        
        // Get booking details
        $details_query = "SELECT ue.id as booking_id, ue.quantity
                         FROM user_events ue
                         WHERE ue.user_id = ? 
                         AND ue.event_id = ? 
                         AND ue.status = 'confirmed'
                         FOR UPDATE";
        
        $stmt = $conn->prepare($details_query);
        $stmt->bind_param('ii', $user_id, $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Event not found or already cancelled');
        }
        
        $booking_details = $result->fetch_assoc();
        
        // Update event status
        $update_query = "UPDATE user_events 
                        SET status = 'cancelled',
                            cancelled_at = NOW() 
                        WHERE user_id = ? 
                        AND event_id = ? 
                        AND status = 'confirmed'";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ii', $user_id, $event_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to cancel event');
        }
        
        // Return tickets to event inventory
        $update_inventory = "UPDATE events 
                           SET quantity = quantity + ? 
                           WHERE id = ?";
        
        $stmt = $conn->prepare($update_inventory);
        $stmt->bind_param('ii', $booking_details['quantity'], $event_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update event inventory');
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Event cancelled successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Event cancellation error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}