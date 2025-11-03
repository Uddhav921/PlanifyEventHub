<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

// Get past events with category names
$query = "SELECT pe.*, c.name as category_name 
          FROM past_events pe
          LEFT JOIN categories c ON pe.category_id = c.id
          ORDER BY pe.expired_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Events | Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .past-events-container {
            padding: 20px;
        }

        .past-event-card {
            position: relative;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            overflow: hidden;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(255, 0, 0, 0.1);
            font-weight: bold;
            pointer-events: none;
            white-space: nowrap;
            z-index: 1;
        }

        .past-event-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .past-event-title {
            font-size: 1.2em;
            color: #2c3e50;
            margin: 0;
        }

        .past-event-date {
            color: #e74c3c;
            font-size: 0.9em;
        }

        .past-event-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 1em;
            color: #2c3e50;
        }

        .past-event-actions {
            display: flex;
            gap: 10px;
            position: relative;
            z-index: 2;
        }

        .btn-view {
            background: #3498db;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            transition: background 0.3s ease;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            transition: background 0.3s ease;
        }

        .btn-view:hover { background: #2980b9; }
        .btn-delete:hover { background: #c0392b; }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        @media (max-width: 768px) {
            .past-event-details {
                grid-template-columns: 1fr;
            }
            
            .watermark {
                font-size: 40px;
            }
        }

        
    </style>
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
            <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
            <li><a href="organizer_requests.php"><i class="fas fa-user-plus"></i> Organizer Requests</a></li>
            <li><a href="revenue.php"><i class="fas fa-chart-line"></i> Revenue</a></li>
            <li class="active"><a href="pastEvents.php"><i class="fas fa-history"></i> Past Events</a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</div>
    
    <div class="main-content">
        <header class="admin-header">
            <h2>Past Events</h2>
            <div class="header-actions">
               
            </div>
        </header>

        <div class="past-events-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <div class="past-event-card">
                        <div class="watermark">EXPIRED</div>
                        
                        <div class="past-event-header">
                            <h3 class="past-event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <span class="past-event-date">
                                Expired on: <?php echo date('M d, Y', strtotime($event['expired_at'])); ?>
                            </span>
                        </div>

                        <div class="past-event-details">
                            <div class="detail-item">
                                <span class="detail-label">Category</span>
                                <span class="detail-value"><?php echo htmlspecialchars($event['category_name']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Location</span>
                                <span class="detail-value"><?php echo htmlspecialchars($event['location']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Original Date</span>
                                <span class="detail-value">
                                    <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                    at <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                </span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Price</span>
                                <span class="detail-value">₹<?php echo number_format($event['price'], 2); ?></span>
                            </div>
                        </div>

                        <div class="past-event-actions">
                            <a href="view_past_event.php?id=<?php echo $event['id']; ?>" class="btn-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="javascript:void(0)" onclick="deletePastEvent(<?php echo $event['id']; ?>)" class="btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times fa-3x"></i>
                    <p>No past events found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deletePastEvent(eventId) {
    if (confirm('Are you sure you want to permanently delete this event?')) {
        fetch('delete_past_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting event: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the event');
        });
    }
}

function exportToCsv() {
    window.location.href = 'export_past_events.php';
}
</script>
</body>
</html>