<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

// Mark message as read if viewing
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $message_id = intval($_GET['view']);
    $update_query = "UPDATE contact_messages SET status = 'read' WHERE id = $message_id";
    $conn->query($update_query);
}

// Get all messages with user details
$messages_query = "SELECT m.*, u.username, u.email 
                  FROM contact_messages m 
                  JOIN users u ON m.user_id = u.id 
                  ORDER BY m.created_at DESC";
$messages_result = $conn->query($messages_query);

// Count unread messages
$unread_query = "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'";
$unread_result = $conn->query($unread_query);
$unread_count = $unread_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages | Planify</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="logo">
                <h1>Planify</h1>
                <p>Admin Panel</p>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                    <li><a href="manage_categories.php"><i class="fas fa-list"></i> Categories</a></li>
                    <li class="active"><a href="messages.php"><i class="fas fa-envelope"></i> Messages
                        <?php if ($unread_count > 0): ?>
                            <span class="badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="organizer_requests.php"><i class="fas fa-user-plus"></i> Organizer Requests</a></li>
                    <li><a href="revenue.php"><i class="fas fa-chart-line"></i> Revenue</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h2>Contact Messages</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <div class="admin-content-box">
                <div class="message-list">
                    <?php if ($messages_result->num_rows > 0): ?>
                        <?php while ($message = $messages_result->fetch_assoc()): ?>
                            <div class="message-item <?php echo $message['status'] === 'unread' ? 'unread' : ''; ?>">
                                <div class="message-header">
                                    <div class="message-info">
                                        <span class="message-from">From: <?php echo $message['username']; ?> (<?php echo $message['email']; ?>)</span>
                                        <span class="message-date"><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></span>
                                    </div>
                                    <?php if ($message['status'] === 'unread'): ?>
                                        <span class="badge">New</span>
                                    <?php endif; ?>
                                </div>
                                <div class="message-subject">
                                    <h4><?php echo $message['subject']; ?></h4>
                                </div>
                                <div class="message-content">
                                    <p><?php echo nl2br($message['message']); ?></p>
                                </div>
                                <div class="message-actions">
                                    <a href="reply_message.php?id=<?php echo $message['id']; ?>" class="btn-small"><i class="fas fa-reply"></i> Reply</a>
                                    <a href="messages.php?delete=<?php echo $message['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this message?');"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-messages">
                            <i class="fas fa-envelope-open"></i>
                            <h3>No Messages</h3>
                            <p>You have no contact messages yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>