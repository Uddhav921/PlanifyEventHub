<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

$message = '';
$revenue = null;

// Get revenue ID from either GET or POST
$revenue_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['revenue_id']) ? intval($_POST['revenue_id']) : 0);

if ($revenue_id) {
    // Define fetch query
    $fetch_query = "SELECT r.*, e.title, e.event_date 
                   FROM platform_revenue r 
                   JOIN events e ON r.event_id = e.id 
                   WHERE r.id = $revenue_id";
    
    // Fetch revenue record
    $result = $conn->query($fetch_query);
    if ($result && $result->num_rows > 0) {
        $revenue = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_revenue'])) {
    if (!$revenue_id) {
        $message = '<div class="alert alert-danger">Invalid revenue record ID.</div>';
    } else {
        $event_price = floatval($_POST['event_price']);
        $platform_fee_percentage = floatval($_POST['platform_fee_percentage']);
        $platform_fee_amount = ($event_price * $platform_fee_percentage) / 100;
        $payment_status = $conn->real_escape_string($_POST['payment_status']);
        $payment_method = $conn->real_escape_string($_POST['payment_method']);
        $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
        $notes = $conn->real_escape_string($_POST['notes']);
        
        $update_query = "UPDATE platform_revenue SET 
                        event_price = $event_price,
                        platform_fee_percentage = $platform_fee_percentage,
                        platform_fee_amount = $platform_fee_amount,
                        payment_status = '$payment_status',
                        payment_method = '$payment_method',
                        transaction_id = '$transaction_id',
                        notes = '$notes',
                        updated_at = NOW()
                        WHERE id = $revenue_id";
        
        if ($conn->query($update_query)) {
            // Re-fetch the updated record
            $result = $conn->query($fetch_query);
            if ($result && $result->num_rows > 0) {
                $revenue = $result->fetch_assoc();
                $message = '<div class="alert alert-success">Revenue record updated successfully!</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Error updating record: ' . $conn->error . '</div>';
        }
    }
}
?>

<!-- Keep the rest of your HTML code the same -->
<!-- Rest of your HTML code remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Revenue Record | Planify</title>
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
                <h2>Edit Revenue Record</h2>
            </header>
            
            <?php echo $message; ?>
            
            <?php if ($revenue): ?>
                <div class="admin-content-box">
                    <form action="edit_revenue.php" method="post" class="admin-form">
                        <input type="hidden" name="revenue_id" value="<?php echo $revenue['id']; ?>">
                        
                        <div class="form-group">
                            <label>Event</label>
                            <input type="text" value="<?php echo htmlspecialchars($revenue['title']); ?>" readonly disabled>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="event_price">Event Price (₹)</label>
                                <input type="number" id="event_price" name="event_price" 
                                       value="<?php echo $revenue['event_price']; ?>" 
                                       step="0.01" min="0" required>
                            </div>
                            
                            <div class="form-group half">
                                <label for="platform_fee_percentage">Platform Fee (%)</label>
                                <input type="number" id="platform_fee_percentage" name="platform_fee_percentage" 
                                       value="<?php echo $revenue['platform_fee_percentage']; ?>" 
                                       step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="payment_method">Payment Method</label>
                                <input type="text" id="payment_method" name="payment_method" 
                                       value="<?php echo htmlspecialchars($revenue['payment_method']); ?>">
                            </div>
                            
                            <div class="form-group half">
                                <label for="payment_status">Payment Status</label>
                                <select id="payment_status" name="payment_status" required>
                                    <option value="pending" <?php echo $revenue['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $revenue['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="refunded" <?php echo $revenue['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="transaction_id">Transaction ID</label>
                            <input type="text" id="transaction_id" name="transaction_id" 
                                   value="<?php echo htmlspecialchars($revenue['transaction_id']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($revenue['notes']); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_revenue" class="btn">Update Revenue Record</button>
                            <a href="revenue.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">Revenue record not found.</div>
                <a href="revenue.php" class="btn btn-secondary">Back to Revenue Management</a>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>