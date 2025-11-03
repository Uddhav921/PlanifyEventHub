<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

// Process approval/rejection
if (isset($_GET['approve']) && !empty($_GET['approve'])) {
    $request_id = intval($_GET['approve']);
    $update_query = "UPDATE organizer_requests SET status = 'approved' WHERE id = $request_id";
    
    if ($conn->query($update_query) === TRUE) {
        // Get user_id from request
        $user_query = "SELECT user_id FROM organizer_requests WHERE id = $request_id";
        $user_result = $conn->query($user_query);
        
        if ($user_result->num_rows > 0) {
            $user_id = $user_result->fetch_assoc()['user_id'];
            
            // Update user type to organizer
            $update_user_query = "UPDATE users SET user_type = 'organizer' WHERE id = $user_id";
            $conn->query($update_user_query);
        }
        
        $message = '<div class="alert alert-success">Request approved successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error approving request: ' . $conn->error . '</div>';
    }
}

if (isset($_GET['reject']) && !empty($_GET['reject'])) {
    $request_id = intval($_GET['reject']);
    $update_query = "UPDATE organizer_requests SET status = 'rejected' WHERE id = $request_id";
    
    if ($conn->query($update_query) === TRUE) {
        $message = '<div class="alert alert-success">Request rejected successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error rejecting request: ' . $conn->error . '</div>';
    }
}

// Get all organizer requests with user details
$requests_query = "SELECT r.*, u.username, u.email 
                  FROM organizer_requests r 
                  JOIN users u ON r.user_id = u.id 
                  ORDER BY 
                    CASE 
                        WHEN r.status = 'pending' THEN 1
                        WHEN r.status = 'approved' THEN 2
                        ELSE 3
                    END,
                    r.created_at DESC";
$requests_result = $conn->query($requests_query);

// Count pending requests
$pending_query = "SELECT COUNT(*) as count FROM organizer_requests WHERE status = 'pending'";
$pending_result = $conn->query($pending_query);
$pending_count = $pending_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Requests | Planify</title>
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
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li class="active"><a href="organizer_requests.php"><i class="fas fa-user-plus"></i> Organizer Requests
                        <?php if ($pending_count > 0): ?>
                            <span class="badge"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="revenue.php"><i class="fas fa-chart-line"></i> Revenue</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h2>Organizer Requests</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <?php if (isset($message)) echo $message; ?>
            
            <div class="admin-content-box">
                <div class="organizer-requests">
                    <?php if ($requests_result->num_rows > 0): ?>
                        <?php while ($request = $requests_result->fetch_assoc()): ?>
                            <div class="request-item <?php echo $request['status']; ?>">
                                <div class="request-header">
                                    <div class="request-info">
                                        <h3><?php echo $request['organization_name']; ?></h3>
                                        <span class="request-from">From: <?php echo $request['username']; ?> (<?php echo $request['email']; ?>)</span>
                                        <span class="request-date"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></span>
                                    </div>
                                    <div class="request-status">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php elseif ($request['status'] === 'approved'): ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Rejected</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="request-details">
                                    <div class="detail-item">
                                        <strong>Description:</strong>
                                        <p><?php echo nl2br($request['description']); ?></p>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <strong>Phone:</strong>
                                            <p><?php echo $request['phone']; ?></p>
                                        </div>
                                        
                                        <?php if (!empty($request['website'])): ?>
                                        <div class="detail-item">
                                            <strong>Website:</strong>
                                            <p><a href="<?php echo $request['website']; ?>" target="_blank"><?php echo $request['website']; ?></a></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($request['status'] === 'pending'): ?>
                                <div class="request-actions">
                                    <a href="organizer_requests.php?approve=<?php echo $request['id']; ?>" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this organizer request?');">Approve</a>
                                    <a href="organizer_requests.php?reject=<?php echo $request['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this organizer request?');">Reject</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-requests">
                            <i class="fas fa-user-plus"></i>
                            <h3>No Requests</h3>
                            <p>There are no organizer requests yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>