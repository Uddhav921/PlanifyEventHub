<?php
session_start();
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch upcoming events
$upcoming_query = "SELECT e.*, ue.payment_id, ue.amount, c.name as category_name 
                  FROM user_events ue
                  JOIN events e ON ue.event_id = e.id
                  JOIN categories c ON e.category_id = c.id
                  WHERE ue.user_id = ? 
                  AND e.event_date >= CURDATE()
                  AND ue.status = 'confirmed'
                  ORDER BY e.event_date ASC";
$stmt = $conn->prepare($upcoming_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$upcoming_result = $stmt->get_result();

// Fetch past events
$past_query = "SELECT e.*, ue.payment_id, ue.amount, c.name as category_name 
               FROM user_events ue
               JOIN events e ON ue.event_id = e.id
               JOIN categories c ON e.category_id = c.id
               WHERE ue.user_id = ? 
               AND e.event_date < CURDATE()
               AND ue.status = 'confirmed'
               ORDER BY e.event_date DESC";
$stmt = $conn->prepare($past_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$past_result = $stmt->get_result();

// Fetch cancelled events
$cancelled_query = "SELECT e.*, ue.payment_id, ue.amount, c.name as category_name 
                   FROM user_events ue
                   JOIN events e ON ue.event_id = e.id
                   JOIN categories c ON e.category_id = c.id
                   WHERE ue.user_id = ? 
                   AND ue.status = 'cancelled'
                   ORDER BY e.event_date DESC";
$stmt = $conn->prepare($cancelled_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cancelled_result = $stmt->get_result();

// Replace the existing spending calculation code

// Calculate total spending and category breakdown
$spending_query = "SELECT 
    c.name as category, 
    COUNT(ue.id) as event_count,
    SUM(ue.amount) as total_amount,
    MAX(ue.amount) as highest_amount
FROM user_events ue
JOIN events e ON ue.event_id = e.id
JOIN categories c ON e.category_id = c.id
WHERE ue.user_id = ? 
AND ue.status = 'confirmed'
GROUP BY c.id, c.name";  // Added c.name to GROUP BY

$stmt = $conn->prepare($spending_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$spending_result = $stmt->get_result();

$total_spent = 0;
$highest_expense = 0;
$category_expenses = [];

while ($row = $spending_result->fetch_assoc()) {
    $total_spent += floatval($row['total_amount']);
    $highest_expense = max($highest_expense, floatval($row['highest_amount']));
    $category_expenses[$row['category']] = [
        'count' => intval($row['event_count']),
        'amount' => floatval($row['total_amount']),
        'highest' => floatval($row['highest_amount'])
    ];
}

// Ensure we have some data for the chart
if (empty($category_expenses)) {
    $category_expenses['No Events'] = [
        'count' => 0,
        'amount' => 0,
        'highest' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Planify</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Add your existing styles here */

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .event-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-outline {
            color: #3498db;
            border: 1px solid #3498db;
            background: transparent;
        }

        .btn-cancel {
            background: #e74c3c;
            color: #fff;
            border: none;
        }

        .btn-outline:hover {
            background: #3498db;
            color: #fff;
        }

        .btn-cancel:hover {
            background: #c0392b;
        }

        .cancelled-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 3em;
            color: rgba(231, 76, 60, 0.7);
            font-weight: bold;
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 5px;
            z-index: 1;
        }


        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Manual Header Styles */
        .main-header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo h1 {
            margin: 0;
            font-size: 1.8em;
        }

        .logo a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .main-nav ul {
            display: flex;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main-nav a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .main-nav a:hover,
        .main-nav a.active {
            color: #3498db;
            background: rgba(52,152,219,0.1);
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }

            .main-nav ul {
                flex-direction: column;
                margin-top: 1rem;
            }

            .logo {
                margin-bottom: 1rem;
            }
        }
        
        /* Dashboard Layout */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Summary Cards */
        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .summary-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card i {
            font-size: 2.5em;
            color: #3498db;
            margin-bottom: 15px;
        }

        .summary-info h3 {
            color: #666;
            font-size: 1em;
            margin-bottom: 5px;
        }

        .summary-info p {
            color: #333;
            font-size: 1.8em;
            font-weight: bold;
            margin: 0;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .event-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-image {
            position: relative;
            height: 180px;
            overflow: hidden;
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-date {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #3498db;
            color: #fff;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .event-status {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #e74c3c;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .event-details {
            padding: 20px;
        }

        .event-details h3 {
            margin: 0 0 10px;
            font-size: 1.2em;
            color: #333;
        }

        .event-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            color: #666;
            font-size: 0.9em;
        }

        .event-category {
            color: #3498db;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .event-price {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 15px;
        }

        .cancelled-price span {
            color: #e74c3c;
        }

        /* Expense Analytics */
        .dashboard-section {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .expense-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .expense-card {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .expense-card.total { background: #e3f2fd; }
        .expense-card.average { background: #f3e5f5; }
        .expense-card.highest { background: #e8f5e9; }

        .expense-chart {
            max-width: 500px;
            margin: 30px auto;
        }

        .category-list {
            margin-top: 20px;
        }

        .category-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .category-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .category-percentage {
            color: #3498db;
            font-size: 0.9em;
        }

        .progress-bar {
            height: 6px;
            background: #eee;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: #3498db;
            transition: width 0.3s ease;
        }

        .no-events {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }

            .dashboard-summary,
            .expense-cards {
                grid-template-columns: 1fr;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .event-image {
                height: 150px;
            }

            .category-info {
                flex-direction: column;
                gap: 5px;
            }
        }

        /* Add this at the top of your existing CSS in dashboard.php */
        header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
        }

        .logo h1 {
            margin: 0;
            font-size: 1.8em;
        }

        .logo a {
            color: #3498db;
            text-decoration: none;
        }

        nav ul {
            display: flex;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        nav a:hover {
            color: #3498db;
            background: rgba(52,152,219,0.1);
        }

        footer {
            background: #2c3e50;
            color: #fff;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        footer h3 {
            color: #fff;
            margin-bottom: 20px;
        }

        footer p {
            margin: 10px 0;
            color: rgba(255,255,255,0.7);
        }

        /* Add this at the end of your media queries */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                padding: 10px;
            }

            nav ul {
                flex-direction: column;
                text-align: center;
                gap: 10px;
                margin-top: 15px;
            }

            .logo h1 {
                margin-bottom: 10px;
            }
        }

        /* Add to your existing CSS in dashboard.php */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .cancelled-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 2.5em;
            font-weight: bold;
            color: rgba(231, 76, 60, 0.8);
            text-transform: uppercase;
            letter-spacing: 2px;
            pointer-events: none;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .event-card.cancelled .event-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.1);
        }

        .cancelled-price {
            color: #e74c3c;
        }

       /* Add to your existing CSS in dashboard.php */
.event-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 0.9em;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
}

.btn-outline {
    border: 1px solid #3498db;
    color: #3498db;
    background: transparent;
}

.btn-cancel {
    background: #e74c3c;
    color: #fff;
}

.btn-outline:hover {
    background: #3498db;
    color: #fff;
}

.btn-cancel:hover {
    background: #c0392b;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn i {
    font-size: 0.9em;
}

/* Footer Styles */
.main-footer {
    background: #2c3e50;
    color: #fff;
    padding: 60px 0 20px;
    margin-top: 50px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section h3 {
    color: #fff;
    font-size: 1.2em;
    margin-bottom: 20px;
    position: relative;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -8px;
    width: 50px;
    height: 2px;
    background: #3498db;
}

.footer-section p {
    color: rgba(255,255,255,0.7);
    margin: 10px 0;
    line-height: 1.6;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section ul li a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section ul li a:hover {
    color: #3498db;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-links a {
    color: #fff;
    font-size: 1.2em;
    transition: color 0.3s ease;
}

.social-links a:hover {
    color: #3498db;
}

.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.footer-bottom p {
    color: rgba(255,255,255,0.7);
    font-size: 0.9em;
}

/* Footer Responsive */
@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .footer-section h3::after {
        left: 50%;
        transform: translateX(-50%);
    }

    .social-links {
        justify-content: center;
    }
}

/* Update container spacing */
.dashboard-container {
    padding-bottom: 0;
}

.welcome-user {
    background-color: #fff;
    padding: 8px 20px;
    margin-left: 20px;
    border: 2px solid #3498db;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    color: #3498db;
    display: flex;
    align-items: center;
    gap: 8px;
}

.theme-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    color: #3498db;
    font-size: 16px;
}

@media (max-width: 768px) {
    .main-nav {
        flex-direction: column;
    }

    .main-nav ul {
        flex-direction: column;
        width: 100%;
    }

    .main-nav ul li {
        margin: 10px 0;
    }

    .welcome-user {
        margin: 10px 0;
        justify-content: center;
        width: 100%;
        text-align: center;
    }
}
    </style>
</head>
<body>
    <!-- Manual Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <h1><a href="../index.php">Planify the Event Hub</a></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../aboutUs.php">About Us</a></li>
                    <li><a href="../events.php">Events</a></li>
                    <li><a href="#contact" class="contact-link">Contact</a></li>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="../logout.php" class="logout-btn">Logout</a></li>
                    <?php if (is_logged_in()): ?>
                    <div class="welcome-user">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    </div>
                <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

<div class="dashboard-container">
    <h1>My Events Dashboard</h1>

    <!-- Dashboard Summary -->
    <div class="dashboard-summary">
        <div class="summary-card">
            <i class="fas fa-calendar-alt"></i>
            <div class="summary-info">
                <h3>Upcoming Events</h3>
                <p><?php echo $upcoming_result->num_rows; ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <i class="fas fa-history"></i>
            <div class="summary-info">
                <h3>Past Events</h3>
                <p><?php echo $past_result->num_rows; ?></p>
            </div>
        </div>

        <div class="summary-card">
            <i class="fas fa-ban"></i>
            <div class="summary-info">
                <h3>Cancelled</h3>
                <p><?php echo $cancelled_result->num_rows; ?></p>
            </div>
        </div>

        <div class="summary-card">
            <i class="fas fa-wallet"></i>
            <div class="summary-info">
                <h3>Total Spent</h3>
                <p>Rs <?php echo number_format($total_spent, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Upcoming Events Section -->
   
<section class="dashboard-section">
    <h2><i class="fas fa-calendar-alt"></i> Upcoming Events</h2>
    <?php if ($upcoming_result->num_rows > 0): ?>
        <div class="events-grid">
            <?php while ($event = $upcoming_result->fetch_assoc()): ?>
                <div class="event-card">
                    <div class="event-image">
                        <img src="../<?php echo htmlspecialchars($event['image']); ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <span class="event-date">
                            <?php echo date('M d', strtotime($event['event_date'])); ?>
                        </span>
                    </div>
                    <div class="event-details">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p class="event-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                            <span><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                        </p>
                        <p class="event-category"><?php echo htmlspecialchars($event['category_name']); ?></p>
                        <div class="event-price">
                            <span>Paid: Rs <?php echo number_format($event['amount'], 2); ?></span>
                            <small>Payment ID: <?php echo $event['payment_id']; ?></small>
                        </div>
                        <div class="event-actions">
                            <a href="../event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-outline">View Details</a>
                            <button type="button" 
                                    class="btn btn-cancel" 
                                    onclick="cancelEvent(<?php echo $event['id']; ?>, '<?php echo addslashes($event['title']); ?>')"
                                    data-event-id="<?php echo $event['id']; ?>">
                                <i class="fas fa-ban"></i> Cancel Event
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="no-events">No upcoming events booked.</p>
    <?php endif; ?>
</section>

    <!-- Past Events Section -->
    <section class="dashboard-section">
        <h2><i class="fas fa-history"></i> Past Events</h2>
        <?php if ($past_result->num_rows > 0): ?>
            <div class="events-grid">
                <?php while ($event = $past_result->fetch_assoc()): ?>
                    <div class="event-card past">
                        <div class="event-image">
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <span class="event-date">
                                <?php echo date('M d', strtotime($event['event_date'])); ?>
                            </span>
                        </div>
                        <div class="event-details">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                            </p>
                            <p class="event-category"><?php echo htmlspecialchars($event['category_name']); ?></p>
                            <div class="event-price">
                                <span>Paid: Rs <?php echo number_format($event['amount'], 2); ?></span>
                                <small>Payment ID: <?php echo $event['payment_id']; ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-events">No past events found.</p>
        <?php endif; ?>
    </section>

    <!-- Cancelled Events Section -->
    <section class="dashboard-section">
        <h2><i class="fas fa-ban"></i> Cancelled Events</h2>
        <?php if ($cancelled_result->num_rows > 0): ?>
            <div class="events-grid">
                <?php while ($event = $cancelled_result->fetch_assoc()): ?>
                    <div class="event-card cancelled">
                        <div class="event-image">
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <span class="cancelled-watermark">CANCELLED</span>
                            <span class="event-date">
                                <?php echo date('M d', strtotime($event['event_date'])); ?>
                            </span>
                        </div>
                        <div class="event-details">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                            </p>
                            <p class="event-category"><?php echo htmlspecialchars($event['category_name']); ?></p>
                            <div class="event-price cancelled-price">
                                <span>Refunded: Rs <?php echo number_format($event['amount'], 2); ?></span>
                                <small>Payment ID: <?php echo $event['payment_id']; ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-events">No cancelled events.</p>
        <?php endif; ?>
    </section>

    <!-- Expense Tracker Section -->
    <section class="dashboard-section">
        <h2><i class="fas fa-chart-pie"></i> Expense Analytics</h2>
        <div class="expense-summary">
            <div class="expense-cards">
                <div class="expense-card total">
                    <h3>Total Spent</h3>
                    <p>Rs <?php echo number_format($total_spent, 2); ?></p>
                </div>
                <div class="expense-card average">
                    <h3>Average per Event</h3>
                    <p>Rs <?php 
                        $total_events = array_sum(array_column($category_expenses, 'count'));
                        echo $total_events > 0 ? number_format($total_spent / $total_events, 2) : '0.00'; 
                    ?></p>
                </div>
                <div class="expense-card highest">
                    <h3>Highest Expense</h3>
                    <p>Rs <?php echo number_format($highest_expense, 2); ?></p>
                </div>
            </div>

            <div class="expense-chart">
                <canvas id="expenseChart"></canvas>
            </div>

           

<div class="category-breakdown">
    <h3>Category Breakdown</h3>
    <div class="category-list">
        <?php if ($total_spent > 0): ?>
            <?php foreach ($category_expenses as $category => $data): ?>
                <div class="category-item">
                    <div class="category-info">
                        <span class="category-name">
                            <?php echo htmlspecialchars($category); ?>
                            <span class="category-percentage">
                                (<?php echo $total_spent > 0 ? 
                                    number_format(($data['amount'] / $total_spent) * 100, 1) : 
                                    '0'; ?>%)
                            </span>
                        </span>
                        <div class="category-stats">
                            <span class="event-count"><?php echo $data['count']; ?> events</span>
                            <span class="amount">Rs <?php echo number_format($data['amount'], 2); ?></span>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo $total_spent > 0 ? 
                            ($data['amount'] / $total_spent) * 100 : 0; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-events">No expense data available.</p>
        <?php endif; ?>
    </div>
</div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Replace the existing chart initialization code

// Replace the chart initialization code

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('expenseChart').getContext('2d');
    const chartData = {
        labels: [<?php echo "'" . implode("','", array_keys($category_expenses)) . "'"; ?>],
        datasets: [{
            data: [<?php echo implode(',', array_map(function($cat) { 
                return number_format($cat['amount'], 2, '.', ''); 
            }, $category_expenses)); ?>],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
            ]
        }]
    };

    new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => +a + +b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: Rs ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
// Replace the existing cancelEvent function with this:

function cancelEvent(eventId, eventTitle) {
    if (confirm(`Are you sure you want to cancel "${eventTitle}"? This action cannot be undone.`)) {
        // Find the specific cancel button
        const button = document.querySelector(`button[data-event-id="${eventId}"]`);
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';

        $.ajax({
            url: 'cancel_event.php',
            type: 'POST',
            data: {
                event_id: eventId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const alertDiv = $('<div>', {
                        class: 'alert alert-success',
                        text: response.message || 'Event cancelled successfully'
                    });
                    
                    $('.dashboard-container').prepend(alertDiv);
                    
                    // Reload page after delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert(response.message || 'Failed to cancel event');
                    // Reset button state
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-ban"></i> Cancel Event';
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('Server error occurred. Please try again.');
                // Reset button state
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-ban"></i> Cancel Event';
            }
        });
    }
}
</script>
 </body>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-section">
                <h3>Planify: The Event Hub</h3>
                <p>Making event planning and booking easier for everyone.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../events.php">Events</a></li>
                    <li><a href="../aboutUs.php">About Us</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p><i class="fas fa-envelope"></i> planifyeventhub@gmail.com</p>
                <p><i class="fas fa-phone"></i> +91 9022954025</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Planify. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
<?php 
$stmt->close();

?>
