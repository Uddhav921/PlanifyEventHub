<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

// Function to handle expired events
function moveExpiredEvents($conn) {
    $conn->begin_transaction();
    
    try {
        // Insert expired events into past_events table
        $move_expired = "INSERT INTO past_events (
            event_id, title, description, image, location, 
            event_date, event_time, category_id, quantity, 
            price, created_at, expired_at
        )
        SELECT 
            id, title, description, image, location,
            event_date, event_time, category_id, quantity,
            price, created_at, NOW()
        FROM events 
        WHERE CONCAT(event_date, ' ', event_time) < NOW()
        AND id NOT IN (SELECT event_id FROM past_events)";
        
        $conn->query($move_expired);

        // Delete expired events
        $delete_expired = "DELETE FROM events 
                          WHERE CONCAT(event_date, ' ', event_time) < NOW()";
        
        $conn->query($delete_expired);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error handling expired events: " . $e->getMessage());
        return false;
    }
}

// Move expired events
moveExpiredEvents($conn);

// Get all active events
$events_query = "SELECT e.*, c.name as category_name 
                FROM events e 
                JOIN categories c ON e.category_id = c.id 
                WHERE CONCAT(e.event_date, ' ', e.event_time) > NOW()
                ORDER BY e.event_date ASC";
$events_result = $conn->query($events_query);

// Handle deletion if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    
    $conn->begin_transaction();
    
    try {
        // Delete from payment_history first
        $stmt = $conn->prepare("DELETE FROM payment_history WHERE event_id = ?");
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        
        // Delete from user_events next
        $stmt = $conn->prepare("DELETE FROM user_events WHERE event_id = ?");
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        
        // Finally delete the event
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        
        $conn->commit();
        $delete_message = "Event deleted successfully";
        
        // Refresh events list
        $events_result = $conn->query($events_query);
        
    } catch (Exception $e) {
        $conn->rollback();
        $delete_error = "Error deleting event: " . $e->getMessage();
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Planify</title>
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
                    <li><a href="manage_categories.php"><i class="fas fa-list"></i> Categories</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="organizer_requests.php"><i class="fas fa-user-plus"></i> Organizer Requests</a></li>
                    <li><a href="revenue.php"><i class="fas fa-chart-line"></i> Revenue</a></li>
                    
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h2>Manage Events</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <?php if (isset($delete_message)): ?>
                <div class="alert alert-success"><?php echo $delete_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($delete_error)): ?>
                <div class="alert alert-danger"><?php echo $delete_error; ?></div>
            <?php endif; ?>
            
            <div class="admin-actions">
                <a href="add_event.php" class="btn"><i class="fas fa-plus"></i> Add New Event</a>
            </div>
            
            <div class="admin-content-box">
                <h3>All Events</h3>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Popular</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($events_result->num_rows > 0): ?>
                            <?php while ($event = $events_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $event['id']; ?></td>
                                    <td>
                                        <img src="../<?php echo $event['image']; ?>" alt="<?php echo $event['title']; ?>" width="50">
                                    </td>
                                    <td><?php echo $event['title']; ?></td>
                                    <td><?php echo $event['category_name']; ?></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($event['event_date'])); ?><br>
                                        <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                    </td>
                                    <td><?php echo $event['location']; ?></td>
                                    <td>₹<?php echo number_format($event['price'], 2); ?></td>
                                    <td>
                                        <?php echo $event['is_popular'] ? '<span class="badge-success">Yes</span>' : '<span class="badge-gray">No</span>'; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $event_datetime = strtotime($event['event_date'] . ' ' . $event['event_time']);
                                        $now = time();
                                        $time_remaining = $event_datetime - $now;
                                        
                                        if ($time_remaining > 0) {
                                            $days = floor($time_remaining / (60 * 60 * 24));
                                            $hours = floor(($time_remaining % (60 * 60 * 24)) / (60 * 60));
                                            
                                            if ($days > 0) {
                                                echo "<span class='badge-success'>{$days}d {$hours}h remaining</span>";
                                            } else if ($hours > 0) {
                                                echo "<span class='badge-warning'>{$hours}h remaining</span>";
                                            } else {
                                                echo "<span class='badge-danger'>Ending soon</span>";
                                            }
                                        } else {
                                            echo "<span class='badge-danger'>Expired</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="actions">
                                        <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn-small">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="manage_events.php?delete=<?php echo $event['id']; ?>" 
                                           class="btn-small btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this event?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">No events found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>