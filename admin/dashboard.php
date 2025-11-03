<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

// Get stats
// Count events
$events_query = "SELECT COUNT(*) as count FROM events";
$events_result = $conn->query($events_query);
$events_count = $events_result->fetch_assoc()['count'];

// Count users
$users_query = "SELECT COUNT(*) as count FROM users";
$users_result = $conn->query($users_query);
$users_count = $users_result->fetch_assoc()['count'];

// Count categories
$categories_query = "SELECT COUNT(*) as count FROM categories";
$categories_result = $conn->query($categories_query);
$categories_count = $categories_result->fetch_assoc()['count'];

// Count unread messages
$unread_messages_query = "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'";
$unread_messages_result = $conn->query($unread_messages_query);
$unread_count = $unread_messages_result->fetch_assoc()['count'];

// Count pending organizer requests
$pending_requests_query = "SELECT COUNT(*) as count FROM organizer_requests WHERE status = 'pending'";
$pending_requests_result = $conn->query($pending_requests_query);
$pending_count = $pending_requests_result->fetch_assoc()['count'];

// Get revenue stats
$revenue_query = "SELECT 
                  SUM(event_price) as total_event_price,
                  SUM(platform_fee_amount) as total_platform_fee,
                  COUNT(*) as total_events
                  FROM platform_revenue";
$revenue_result = $conn->query($revenue_query);
$revenue_stats = $revenue_result->fetch_assoc();

$total_event_price = $revenue_stats['total_event_price'] ?: 0;
$total_platform_fee = $revenue_stats['total_platform_fee'] ?: 0;
$total_events = $revenue_stats['total_events'] ?: 0;

// Set total_revenue to platform_fee since that's our actual revenue
$total_revenue = $total_platform_fee;



// Get recent events
$recent_events_query = "SELECT e.*, c.name as category_name 
                      FROM events e 
                      LEFT JOIN categories c ON e.category_id = c.id 
                      ORDER BY e.event_date DESC 
                      LIMIT 5";
$recent_events_result = $conn->query($recent_events_query);


// Get recent messages
$recent_messages_query = "SELECT m.*, u.username, u.email 
                        FROM contact_messages m 
                        JOIN users u ON m.user_id = u.id 
                        ORDER BY m.created_at DESC 
                        LIMIT 3";
$recent_messages_result = $conn->query($recent_messages_query);

// Get recent organizer requests
$recent_requests_query = "SELECT r.*, u.username, u.email 
                        FROM organizer_requests r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.status = 'pending'
                        ORDER BY r.created_at DESC 
                        LIMIT 3";
$recent_requests_result = $conn->query($recent_requests_query);

// Add this after existing revenue query
$user_revenue_query = "SELECT 
    SUM(amount_paid) as total_user_payments,
    SUM(platform_fee) as total_user_platform_fees,
    COUNT(DISTINCT user_id) as total_paying_users,
    COUNT(*) as total_transactions
    FROM user_revenue 
    WHERE payment_status = 'completed'";
$user_revenue_result = $conn->query($user_revenue_query);
$user_revenue_stats = $user_revenue_result->fetch_assoc();

$total_user_payments = $user_revenue_stats['total_user_payments'] ?: 0;
$total_user_platform_fees = $user_revenue_stats['total_user_platform_fees'] ?: 0;
$total_paying_users = $user_revenue_stats['total_paying_users'] ?: 0;
$total_transactions = $user_revenue_stats['total_transactions'] ?: 0;

// ...existing code...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Planify</title>
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
                <?php
                // Get current page filename
                $current_page = basename($_SERVER['PHP_SELF']);
                
                // Define menu items with their properties
                $menu_items = [
                    ['url' => 'dashboard.php', 'icon' => 'fa-tachometer-alt', 'text' => 'Dashboard'],
                    ['url' => 'manage_events.php', 'icon' => 'fa-calendar-alt', 'text' => 'Manage Events'],
                    ['url' => 'manage_categories.php', 'icon' => 'fa-list', 'text' => 'Categories'],
                    ['url' => 'messages.php', 'icon' => 'fa-envelope', 'text' => 'Messages', 'badge' => $unread_count],
                    ['url' => 'organizer_requests.php', 'icon' => 'fa-user-plus', 'text' => 'Organizer Requests', 'badge' => $pending_count],
                    ['url' => 'revenue.php', 'icon' => 'fa-chart-line', 'text' => 'Revenue'],
                    
                    ['url' => '../index.php', 'icon' => 'fa-globe', 'text' => 'View Website', 'target' => '_blank'],
                    ['url' => '../logout.php', 'icon' => 'fa-sign-out-alt', 'text' => 'Logout']
                ];

                // Output menu items
                foreach ($menu_items as $item) {
                    $is_active = ($current_page === $item['url']) ? 'class="active"' : '';
                    $target = isset($item['target']) ? 'target="' . $item['target'] . '"' : '';
                    echo '<li ' . $is_active . '>';
                    echo '<a href="' . $item['url'] . '" ' . $target . '>';
                    echo '<i class="fas ' . $item['icon'] . '"></i> ' . $item['text'];
                    
                    // Add badge if exists and count > 0
                    if (isset($item['badge']) && $item['badge'] > 0) {
                        echo '<span class="badge">' . $item['badge'] . '</span>';
                    }
                    
                    echo '</a></li>';
                }
                ?>
            </ul>
        </nav>
    </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h2>Admin Dashboard</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $events_count; ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $users_count; ?></h3>
                        <p>Registered Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $categories_count; ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
                
              <div class="stat-card">
    <div class="stat-icon">
        <i class="fas fa-money-bill-wave"></i>
    </div>
    <div class="stat-info">
        <h3>₹<?php echo number_format($total_revenue, 2); ?></h3>
        <p>Platform Organizers Revenue</p>
    </div>
</div>
            </div>
            <div class="stat-card">
    <div class="stat-icon">
        <i class="fas fa-user-check"></i>
    </div>
    <div class="stat-info">
        <h3>₹<?php echo number_format($total_user_platform_fees, 2); ?></h3>
        <p>User Platform Revenue</p>
    </div>
</div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Events</h3>
                        <a href="manage_events.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if ($recent_events_result->num_rows > 0): ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($event = $recent_events_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $event['title']; ?></td>
                                            <td><?php echo $event['category_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                            <td class="actions">
                                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn-small"><i class="fas fa-edit"></i></a>
                                                <a href="manage_events.php?delete=<?php echo $event['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this event?');"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No events found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Messages</h3>
                        <a href="messages.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if ($recent_messages_result->num_rows > 0): ?>
                            <div class="message-preview-list">
                                <?php while ($message = $recent_messages_result->fetch_assoc()): ?>
                                    <div class="message-preview <?php echo $message['status'] === 'unread' ? 'unread' : ''; ?>">
                                        <div class="message-preview-header">
                                            <span class="username"><?php echo $message['username']; ?></span>
                                            <span class="date"><?php echo date('M d', strtotime($message['created_at'])); ?></span>
                                        </div>
                                        <div class="message-preview-subject">
                                            <?php echo $message['subject']; ?>
                                        </div>
                                        <div class="message-preview-content">
                                            <?php echo substr($message['message'], 0, 70) . (strlen($message['message']) > 70 ? '...' : ''); ?>
                                        </div>
                                        <a href="messages.php?view=<?php echo $message['id']; ?>" class="btn-link">Read Message</a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No messages found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-card">
    <div class="card-header">
        <h3>Organizer Revenue Overview</h3>
        <a href="revenue.php" class="view-all">View Details</a>
    </div>
    <div class="card-content">
        <div class="revenue-overview">
            <div class="revenue-stat">
                <span class="revenue-label">Total Event Value</span>
                <span class="revenue-value">₹<?php echo number_format($total_event_price, 2); ?></span>
            </div>
            <div class="revenue-stat">
                <span class="revenue-label">Platform Revenue</span>
                <span class="revenue-value">₹<?php echo number_format($total_platform_fee, 2); ?></span>
            </div>
            <div class="revenue-stat">
                <span class="revenue-label">Average Platform Fee</span>
                <span class="revenue-value">
                    <?php 
                    if ($total_event_price > 0) {
                        echo number_format(($total_platform_fee / $total_event_price) * 100, 1) . '%';
                    } else {
                        echo '15%';
                    }
                    ?>
                </span>
            </div>
            <div class="revenue-stat">
            <span class="revenue-label">Total Events</span>
                <span class="revenue-value"><?php echo number_format($total_events); ?></span>
            </div>
        </div>
        <div class="cta-container">
            <a href="revenue.php" class="btn">Manage Revenue</a>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3>User Revenue Overview</h3>
        <a href="revenue.php#user-revenue" class="view-all">View Details</a>
    </div>
    <div class="card-content">
        <div class="revenue-overview">
            <div class="revenue-stat">
                <span class="revenue-label">Total User Payments</span>
                <span class="revenue-value">₹<?php echo number_format($total_user_payments, 2); ?></span>
            </div>
            <div class="revenue-stat">
                <span class="revenue-label">Platform Fees Collected</span>
                <span class="revenue-value">₹<?php echo number_format($total_user_platform_fees, 2); ?></span>
            </div>
            <div class="revenue-stat">
                <span class="revenue-label">Paying Users</span>
                <span class="revenue-value"><?php echo number_format($total_paying_users); ?></span>
            </div>
            <div class="revenue-stat">
                <span class="revenue-label">Total Transactions</span>
                <span class="revenue-value"><?php echo number_format($total_transactions); ?></span>
            </div>
        </div>
        <div class="stats-summary">
            <div class="summary-item">
                <small>Average Transaction Value</small>
                <strong>₹<?php echo $total_transactions > 0 ? 
                    number_format($total_user_payments / $total_transactions, 2) : '0.00'; ?></strong>
            </div>
            <div class="summary-item">
                <small>Average Platform Fee</small>
                <strong><?php echo $total_user_payments > 0 ? 
                    number_format(($total_user_platform_fees / $total_user_payments) * 100, 1) : '0'; ?>%</strong>
            </div>
        </div>
    </div>
</div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Pending Organizer Requests</h3>
                        <a href="organizer_requests.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if ($recent_requests_result->num_rows > 0): ?>
                            <div class="request-preview-list">
                                <?php while ($request = $recent_requests_result->fetch_assoc()): ?>
                                    <div class="request-preview">
                                        <div class="request-preview-header">
                                            <span class="organization"><?php echo $request['organization_name']; ?></span>
                                            <span class="date"><?php echo date('M d', strtotime($request['created_at'])); ?></span>
                                        </div>
                                        <div class="request-preview-content">
                                            <p><strong>From:</strong> <?php echo $request['username']; ?> (<?php echo $request['email']; ?>)</p>
                                            <p><?php echo substr($request['description'], 0, 100) . (strlen($request['description']) > 100 ? '...' : ''); ?></p>
                                        </div>
                                        <div class="request-preview-actions">
                                            <a href="organizer_requests.php?approve=<?php echo $request['id']; ?>" class="btn-small btn-success" onclick="return confirm('Are you sure you want to approve this organizer?');">Approve</a>
                                            <a href="organizer_requests.php?reject=<?php echo $request['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure you want to reject this organizer?');">Reject</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No pending organizer requests</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-quick-actions">
                <h3>Quick Actions</h3>
                <div class="quick-action-buttons">
                    <a href="add_event.php" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        <span>Add Event</span>
                    </a>
                    <a href="manage_categories.php" class="quick-action-btn">
                        <i class="fas fa-folder-plus"></i>
                        <span>Add Category</span>
                    </a>
                    <a href="messages.php" class="quick-action-btn">
                        <i class="fas fa-envelope"></i>
                        <span>View Messages</span>
                    </a>
                    <a href="revenue.php" class="quick-action-btn">
                        <i class="fas fa-chart-line"></i>
                        <span>Update Revenue</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Add active class to current sidebar link
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.admin-nav a');
    
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.parentElement.classList.add('active');
        }
    });

    // Responsive sidebar toggle
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.admin-header').prepend(menuToggle);

    menuToggle.addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('show');
    });
});
</script>

// Add before closing </body> tag
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add active class to current sidebar link
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.admin-nav a');
    
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.parentElement.classList.add('active');
        }
    });

    // Responsive sidebar toggle
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.admin-header').prepend(menuToggle);

    menuToggle.addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('show');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.menu-toggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !menuToggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });
});
</script>
</body>
</html>