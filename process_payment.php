<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function for JSON responses
function sendJsonResponse($message, $success = true, $data = []) {
    ob_clean();
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// Validate session
if (!is_logged_in()) {
    sendJsonResponse('User not logged in', false);
}

// Validate and sanitize input
$payment_id = filter_input(INPUT_POST, 'payment_id', FILTER_SANITIZE_STRING);
$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'] ?? null;

// Input validation
if (!$payment_id || !$event_id || !$amount || !$quantity || !$user_id) {
    sendJsonResponse('Missing or invalid input data', false);
}

try {
    $conn->begin_transaction();

    // Get event details with error checking
    $stmt = $conn->prepare("SELECT quantity, price, title FROM events WHERE id = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception('Database error: Failed to prepare event query');
    }
    
    $stmt->bind_param('i', $event_id);
    if (!$stmt->execute()) {
        throw new Exception('Database error: Failed to execute event query');
    }
    
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if (!$event) {
        throw new Exception('Event not found');
    }
    
    if ($event['quantity'] < $quantity) {
        throw new Exception('Not enough tickets available');
    }

    // Calculate amounts
    $total_amount = $amount * $quantity;
    $platform_fee = round($total_amount * 0.15, 2);

    // Update event quantity with error checking
    $stmt = $conn->prepare("UPDATE events SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
    if (!$stmt || !$stmt->bind_param('iii', $quantity, $event_id, $quantity)) {
        throw new Exception('Database error: Failed to update event quantity');
    }
    
    if (!$stmt->execute() || $stmt->affected_rows === 0) {
        throw new Exception('Failed to update ticket quantity');
    }

    // Insert booking record
    $stmt = $conn->prepare("INSERT INTO user_events (user_id, event_id, payment_id, amount, quantity, status) 
                           VALUES (?, ?, ?, ?, ?, 'confirmed')");
    if (!$stmt || !$stmt->bind_param('iisdi', $user_id, $event_id, $payment_id, $total_amount, $quantity)) {
        throw new Exception('Database error: Failed to create booking');
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create booking');
    }
    
    $booking_id = $conn->insert_id;

    // ...existing code...

    // Insert revenue record
    $stmt = $conn->prepare("INSERT INTO user_revenue (
        user_id, 
        event_id, 
        booking_id, 
        amount_paid, 
        platform_fee, 
        payment_status, 
        payment_id
    ) VALUES (?, ?, ?, ?, ?, 'completed', ?)");
    
    if (!$stmt || !$stmt->bind_param('iiidds', 
        $user_id, 
        $event_id, 
        $booking_id, 
        $total_amount, 
        $platform_fee, 
        $payment_id
    )) {
        throw new Exception('Database error: Failed to record revenue');
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to record revenue: ' . $stmt->error);
    }

    $conn->commit();
    
    // Send success response with data
    sendJsonResponse('Payment processed successfully', true, [
        'booking_id' => $booking_id,
        'platform_fee' => $platform_fee,
        'total_amount' => $total_amount,
        'event_title' => $event['title']
    ]);

// ...existing code...

} catch (Exception $e) {
    $conn->rollback();
    error_log("Payment Error: " . $e->getMessage());
    sendJsonResponse("Payment failed: " . $e->getMessage(), false);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}