<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
    $user_id = $_SESSION['user_id'];
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    $query = "INSERT INTO contact_messages (user_id, subject, message) VALUES ($user_id, '$subject', '$message')";
    
    if ($conn->query($query) === TRUE) {
        header("Location: index.php?message_sent=success#contact");
    } else {
        header("Location: index.php?message_sent=error#contact");
    }
    exit();
}
?>