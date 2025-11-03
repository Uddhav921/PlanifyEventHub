<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

// Process form submission for adding revenue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_revenue'])) {
    $event_id = intval($_POST['event_id']);
    $event_price = floatval($_POST['event_price']);
    $platform_fee_percentage = floatval($_POST['platform_fee_percentage']);
    $platform_fee_amount = ($event_price * $platform_fee_percentage) / 100;
    $payment_status = $conn->real_escape_string($_POST['payment_status']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $query = "INSERT INTO platform_revenue (
                event_id, 
                event_price, 
                platform_fee_percentage, 
                platform_fee_amount,
                payment_status,
                payment_method,
                transaction_id,
                payment_date,
                notes
              ) VALUES (
                $event_id, 
                $event_price, 
                $platform_fee_percentage, 
                $platform_fee_amount,
                '$payment_status',
                '$payment_method',
                '$transaction_id',
                NOW(),
                '$notes'
              )";
    
    if ($conn->query($query) === TRUE) {
        $message = '<div class="alert alert-success">Revenue data added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding revenue data: ' . $conn->error . '</div>';
    }
}

// Get revenue statistics
$stats_query = "SELECT 
                COUNT(*) as total_events,
                SUM(platform_fee_amount) as total_revenue,
                SUM(CASE WHEN payment_status = 'completed' THEN platform_fee_amount ELSE 0 END) as collected_revenue,
                SUM(CASE WHEN payment_status = 'pending' THEN platform_fee_amount ELSE 0 END) as pending_revenue
                FROM platform_revenue";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Replace the existing events query with this:
$events_query = "SELECT e.* 
                FROM events e 
                LEFT JOIN platform_revenue r ON e.id = r.event_id 
                WHERE r.id IS NULL 
                AND CONCAT(e.event_date, ' ', e.event_time) > NOW()
                ORDER BY e.event_date ASC";
$events_result = $conn->query($events_query);

// Add error checking
if (!$events_result) {
    echo '<div class="alert alert-danger">Error fetching events: ' . $conn->error . '</div>';
}

// In the form section, add data attributes for price:

// Get revenue history
$revenue_query = "SELECT r.*, e.title, e.event_date 
                 FROM platform_revenue r 
                 JOIN events e ON r.event_id = e.id 
                 ORDER BY r.created_at DESC";
$revenue_result = $conn->query($revenue_query);

$past_records_query = "SELECT p.*, u.username as deleted_by_user 
                      FROM past_revenue_records p
                      LEFT JOIN users u ON p.deleted_by = u.id 
                      ORDER BY p.deleted_at DESC";
$past_records_result = $conn->query($past_records_query);

$user_revenue_query = "SELECT ur.*, 
                             u.username, 
                             u.email,
                             e.title as event_title,
                             e.event_date,
                             ue.quantity as tickets_bought
                      FROM user_revenue ur
                      JOIN users u ON ur.user_id = u.id
                      JOIN events e ON ur.event_id = e.id
                      JOIN user_events ue ON ur.booking_id = ue.id
                      ORDER BY ur.payment_date DESC";
$user_revenue_result = $conn->query($user_revenue_query);


// Replace the existing delete operation with:

// Handle Delete Operation
if (isset($_GET['delete'])) {
    $revenue_id = intval($_GET['delete']);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First get the record to archive
        $get_record = "SELECT r.*, e.title as event_title, e.event_date 
                      FROM platform_revenue r 
                      JOIN events e ON r.event_id = e.id 
                      WHERE r.id = ?";
        $stmt = $conn->prepare($get_record);
        $stmt->bind_param('i', $revenue_id);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
        
        if ($record) {
            // Insert into past records
            $archive_query = "INSERT INTO past_revenue_records (
                event_id, event_title, event_date, event_price, 
                platform_fee_amount, platform_fee_percentage, 
                payment_status, payment_method, transaction_id, 
                payment_date, notes, deleted_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($archive_query);
            $stmt->bind_param('issdddsssssi', 
                $record['event_id'],
                $record['title'],
                $record['event_date'],
                $record['event_price'],
                $record['platform_fee_amount'],
                $record['platform_fee_percentage'],
                $record['payment_status'],
                $record['payment_method'],
                $record['transaction_id'],
                $record['payment_date'],
                $record['notes'],
                $_SESSION['user_id']
            );
            $stmt->execute();
            
            // Delete from current records
            $delete_query = "DELETE FROM platform_revenue WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param('i', $revenue_id);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = '<div class="alert alert-success">Revenue record moved to past records.</div>';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
    
    header('Location: revenue.php');
    exit();
}

// Display session message if exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Revenue | Planify</title>
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
                <h2>Platform Revenue Management</h2>
            </header>
            
            <?php if (isset($message)) echo $message; ?>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_events']); ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Platform Fees</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($stats['collected_revenue'], 2); ?></h3>
                        <p>Collected Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($stats['pending_revenue'], 2); ?></h3>
                        <p>Pending Revenue</p>
                    </div>
                </div>
            </div>
            
            

               

            <div class="admin-content-box">
                <div class="admin-section">
                    <h3>Add Revenue Entry</h3>
                    <?php if ($events_result->num_rows > 0): ?>
                        <form action="revenue.php" method="post" class="admin-form">
                            <div class="form-row">
                                <div class="form-group half">
                                    <label for="event_id">Select Event</label>
                                    <select id="event_id" name="event_id" required>
    <option value="">Select an event</option>
    <?php 
    if ($events_result && $events_result->num_rows > 0):
        while ($event = $events_result->fetch_assoc()): 
    ?>
        <option value="<?php echo $event['id']; ?>" 
                data-price="<?php echo $event['price']; ?>">
            <?php echo htmlspecialchars($event['title']); ?> 
            (₹<?php echo number_format($event['price'], 2); ?>) - 
            <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
        </option>
    <?php 
        endwhile;
    endif;
    ?>
</select>
                                </div>
                                
                                <div class="form-group half">
                                    <label for="event_price">Event Price (₹)</label>
                                    <input type="number" id="event_price" name="event_price" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half">
                                    <label for="platform_fee_percentage">Platform Fee (%)</label>
                                    <input type="number" id="platform_fee_percentage" name="platform_fee_percentage" 
                                           value="15" step="0.01" min="0" max="100" required>
                                </div>
                                
                                <div class="form-group half">
                                    <label for="payment_status">Payment Status</label>
                                    <select id="payment_status" name="payment_status" required>
                                        <option value="pending">Pending</option>
                                        <option value="completed">Completed</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half">
                                    <label for="payment_method">Payment Method</label>
                                    <input type="text" id="payment_method" name="payment_method">
                                </div>
                                
                                <div class="form-group half">
                                    <label for="transaction_id">Transaction ID</label>
                                    <input type="text" id="transaction_id" name="transaction_id">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea id="notes" name="notes" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" name="add_revenue" class="btn">Add Revenue Entry</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">All events have revenue data recorded.</div>
                    <?php endif; ?>
                </div>
                
                <div class="admin-section">
                    <h3>Organizers Revenue Section </h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Event Date</th>
                                <th>Event Price</th>
                                <th>Platform Fee</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($revenue_result->num_rows > 0): ?>
                                <?php while ($revenue = $revenue_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($revenue['title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($revenue['event_date'])); ?></td>
                                        <td>₹<?php echo number_format($revenue['event_price'], 2); ?></td>
                                        <td>₹<?php echo number_format($revenue['platform_fee_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $revenue['payment_status']; ?>">
                                                <?php echo ucfirst($revenue['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $revenue['payment_date'] ? date('M d, Y', strtotime($revenue['payment_date'])) : '-'; ?></td>
                                        <td class="actions">
                                            <a href="edit_revenue.php?id=<?php echo $revenue['id']; ?>" class="btn-small">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
<a href="revenue.php?delete=<?php echo $revenue['id']; ?>" 
   class="btn-small btn-danger delete-revenue" 
   data-id="<?php echo $revenue['id']; ?>"
   onclick="return confirmDelete(event)">
    <i class="fas fa-trash"></i>
</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No revenue data found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-section user-revenue">
    <h3><i class="fas fa-users"></i> User Revenue & Platform Fees</h3>
    <div class="revenue-summary">
        <div class="summary-card">
            <h4>Total Platform Fees Collected</h4>
            <p class="amount">₹<?php 
                $total_fees = 0;
                if ($user_revenue_result->num_rows > 0) {
                    $user_revenue_result->data_seek(0);
                    while($row = $user_revenue_result->fetch_assoc()) {
                        $total_fees += $row['platform_fee'];
                    }
                }
                echo number_format($total_fees, 2);
            ?></p>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Event</th>
                <th>Tickets</th>
                <th>Amount Paid</th>
                <th>Platform Fee (15%)</th>
                <th>Payment ID</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($user_revenue_result->num_rows > 0):
                $user_revenue_result->data_seek(0);
                while($revenue = $user_revenue_result->fetch_assoc()): 
            ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <span class="username"><?php echo htmlspecialchars($revenue['username']); ?></span>
                            <span class="email"><?php echo htmlspecialchars($revenue['email']); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="event-info">
                            <span class="event-title"><?php echo htmlspecialchars($revenue['event_title']); ?></span>
                            <span class="event-date"><?php echo date('M d, Y', strtotime($revenue['event_date'])); ?></span>
                        </div>
                    </td>
                    <td class="text-center"><?php echo $revenue['tickets_bought']; ?></td>
                    <td class="amount">₹<?php echo number_format($revenue['amount_paid'], 2); ?></td>
                    <td class="platform-fee">₹<?php echo number_format($revenue['platform_fee'], 2); ?></td>
                    <td class="payment-id"><?php echo htmlspecialchars($revenue['payment_id']); ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($revenue['payment_status']); ?>">
                            <?php echo ucfirst($revenue['payment_status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y H:i', strtotime($revenue['payment_date'])); ?></td>
                </tr>
            <?php 
                endwhile;
            else: 
            ?>
                <tr>
                    <td colspan="8" class="text-center">No user revenue data found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


                <div class="admin-section past-records">
                    <h3><i class="fas fa-history"></i> Past Revenue Records</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Event Date</th>
                                <th>Event Price</th>
                                <th>Platform Fee</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th>Deleted By</th>
                                <th>Deleted On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($past_records_result && $past_records_result->num_rows > 0): ?>
                                <?php while ($record = $past_records_result->fetch_assoc()): ?>
                                    <tr class="past-record">
                                        <td>
                                            <div class="watermark">Past Record</div>
                                            <?php echo htmlspecialchars($record['event_title']); ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($record['event_date'])); ?></td>
                                        <td>₹<?php echo number_format($record['event_price'], 2); ?></td>
                                        <td>₹<?php echo number_format($record['platform_fee_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $record['payment_status']; ?>">
                                                <?php echo ucfirst($record['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($record['payment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($record['deleted_by_user']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($record['deleted_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No past records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
document.getElementById('event_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    document.getElementById('event_price').value = price || '';
});
</script>
    <script src="../js/script.js"></script>
</body>
</html>