<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

// Get event ID
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if event exists
$event_query = "SELECT * FROM events WHERE id = $event_id";
$event_result = $conn->query($event_query);

if ($event_result->num_rows == 0) {
    // Event doesn't exist, redirect back to manage events
    header("Location: manage_events.php?error=Event not found");
    exit();
}

// Get event details for confirmation
$event = $event_result->fetch_assoc();

// Process delete confirmation
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    // Delete the event
    $delete_query = "DELETE FROM events WHERE id = $event_id";
    
    if ($conn->query($delete_query) === TRUE) {
        // Redirect with success message
        header("Location: manage_events.php?success=Event deleted successfully");
        exit();
    } else {
        // Error in deletion
        $error = "Error deleting event: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Event | Planify</title>
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
                    <li class="active"><a href="manage_events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                    <li><a href="add_event.php"><i class="fas fa-plus-circle"></i> Add New Event</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h2>Delete Event</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="admin-content-box">
                <h3>Confirm Deletion</h3>
                
                <div class="delete-confirmation">
                    <p>Are you sure you want to delete the following event?</p>
                    
                    <div class="event-to-delete">
                        <div class="event-info">
                            <h4><?php echo $event['title']; ?></h4>
                            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($event['event_date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($event['event_time'])); ?></p>
                            <p><strong>Location:</strong> <?php echo $event['location']; ?></p>
                        </div>
                        
                        <?php if (!empty($event['image'])): ?>
                        <div class="event-image-preview">
                            <img src="../<?php echo $event['image']; ?>" alt="<?php echo $event['title']; ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="warning-message">
                        <p><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</p>
                    </div>
                    
                    <div class="action-buttons">
                        <form action="delete_event.php?id=<?php echo $event_id; ?>" method="post">
                            <input type="hidden" name="confirm_delete" value="yes">
                            <button type="submit" class="btn btn-danger">Yes, Delete Event</button>
                            <a href="manage_events.php" class="btn btn-outline">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>