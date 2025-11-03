<?php
session_start();
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Get event ID with validation
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("Location: events.php");
    exit();
}

// Update the event query to include available tickets
$event_query = "SELECT e.*, c.name as category_name, u.username as organizer_name, 
                (e.quantity - COALESCE((
                    SELECT SUM(ue.quantity) 
                    FROM user_events ue 
                    WHERE ue.event_id = e.id AND ue.status = 'confirmed'
                ), 0)) as available_tickets 
                FROM events e 
                JOIN categories c ON e.category_id = c.id 
                LEFT JOIN users u ON e.organizer_id = u.id
                WHERE e.id = ?";
$stmt = $conn->prepare($event_query);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event_result = $stmt->get_result();

if ($event_result->num_rows == 0) {
    $stmt->close();
    header("Location: events.php");
    exit();
}

$event = $event_result->fetch_assoc();
$stmt->close();

// Get similar events
$similar_events_query = "SELECT e.*, c.name as category_name,
                        (e.quantity - COALESCE((
                            SELECT SUM(ue.quantity) 
                            FROM user_events ue 
                            WHERE ue.event_id = e.id AND ue.status = 'confirmed'
                        ), 0)) as available_tickets 
                        FROM events e
                        JOIN categories c ON e.category_id = c.id
                        WHERE e.category_id = ? AND e.id != ?
                        AND e.event_date >= CURDATE()
                        HAVING available_tickets > 0
                        ORDER BY e.event_date 
                        LIMIT 3";
$stmt2 = $conn->prepare($similar_events_query);
$stmt2->bind_param('ii', $event['category_id'], $event_id);
$stmt2->execute();
$similar_events_result = $stmt2->get_result();

include_once 'includes/header.php';
?>

<!-- Event Header Section -->
<section class="event-detail-header" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo htmlspecialchars($event['image']); ?>')">
    <div class="container">
        <div class="event-detail-info">
            <h1><?php echo htmlspecialchars($event['title']); ?></h1>
            <div class="event-meta">
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('F d, Y', strtotime($event['event_date'])); ?></span>
                <span><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['category_name']); ?></span>
            </div>
            <div class="event-price-badge">
                <span>Rs <?php echo number_format($event['price'], 2); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Event Content Section -->
<section class="event-detail-content">
    <div class="container">
        <div class="event-detail-main">
            <div class="event-detail-description">
                <h2>About This Event</h2>
                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
            
            <div class="event-detail-info-box">
                <h3>Event Information</h3>
                <ul>
                    <li><strong><i class="fas fa-calendar-alt"></i> Date:</strong> <?php echo date('F d, Y', strtotime($event['event_date'])); ?></li>
                    <li><strong><i class="far fa-clock"></i> Time:</strong> <?php echo date('g:i A', strtotime($event['event_time'])); ?></li>
                    <li><strong><i class="fas fa-map-marker-alt"></i> Location:</strong> <?php echo htmlspecialchars($event['location']); ?></li>
                    <li><strong><i class="fas fa-tag"></i> Category:</strong> <?php echo htmlspecialchars($event['category_name']); ?></li>
                    <li><strong><i class="fas fa-user"></i> Organizer:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></li>
                    <li><strong><i class="fas fa-rupee-sign"></i> Price:</strong> Rs <?php echo number_format($event['price'], 2); ?></li>
                    <li><strong><i class="fas fa-ticket-alt"></i> Available Tickets:</strong> <?php echo $event['available_tickets']; ?></li>
                </ul>
            </div>
            
            <div class="ticket-quantity-selector">
                <h3>Select Tickets</h3>
                <?php if ($event['available_tickets'] > 0): ?>
                    <p class="tickets-available">
                        <i class="fas fa-ticket-alt"></i> 
                        Available Tickets: <span id="available-tickets"><?php echo $event['available_tickets']; ?></span>
                    </p>
                    <div class="quantity-controls">
                        <button type="button" class="qty-btn minus" onclick="updateQuantity(-1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="ticket-quantity" value="1" 
                               min="1" max="<?php echo min(10, $event['available_tickets']); ?>" readonly>
                        <button type="button" class="qty-btn plus" onclick="updateQuantity(1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="price-summary">
                        <p>Price per ticket: <span>Rs <?php echo number_format($event['price'], 2); ?></span></p>
                        <p class="total-price">Total: <span>Rs <span id="total-price"><?php echo number_format($event['price'], 2); ?></span></span></p>
                    </div>
                <?php else: ?>
                    <div class="sold-out-message">
                        <i class="fas fa-times-circle"></i>
                        <p>Sorry, this event is sold out!</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($event['available_tickets'] > 0): ?>
                <div class="event-actions">
                    <button id="rzp-button1" class="btn btn-primary btn-large">Proceed to Pay</button>
                </div>
            <?php endif; ?>

            <?php if ($similar_events_result->num_rows > 0): ?>
            <div class="similar-events">
                <h3>Similar Events You Might Like</h3>
                <div class="events-grid">
                    <?php while ($similar = $similar_events_result->fetch_assoc()): ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars($similar['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($similar['title']); ?>">
                                <div class="event-date">
                                    <?php echo date('M d', strtotime($similar['event_date'])); ?>
                                </div>
                            </div>
                            <div class="event-info">
                                <h4><?php echo htmlspecialchars($similar['title']); ?></h4>
                                <p class="event-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($similar['location']); ?></span>
                                    <span><i class="fas fa-rupee-sign"></i> <?php echo number_format($similar['price'], 2); ?></span>
                                </p>
                                <p class="tickets-left">
                                    <i class="fas fa-ticket-alt"></i> <?php echo $similar['available_tickets']; ?> tickets left
                                </p>
                                <a href="event_details.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
let basePrice = <?php echo $event['price']; ?>;
let maxTickets = <?php echo min(10, $event['available_tickets']); ?>;
let rzp1;

function updateQuantity(change) {
    try {
        const input = document.getElementById('ticket-quantity');
        if (!input) return;

        const currentQty = parseInt(input.value) || 1;
        const newQty = Math.min(Math.max(currentQty + change, 1), maxTickets);
        
        input.value = newQty;
        
        // Update total price display
        const totalPrice = basePrice * newQty;
        const totalPriceElement = document.getElementById('total-price');
        if (totalPriceElement) {
            totalPriceElement.textContent = totalPrice.toFixed(2);
        }
        
        // Update Razorpay options
        if (typeof options !== 'undefined') {
            options.amount = totalPrice * 100;
            options.notes = {
                ...options.notes,
                quantity: newQty
            };
            if (typeof rzp1 !== 'undefined') {
                rzp1 = new Razorpay(options);
            }
        }
        
        // Update button states
        const minusBtn = document.querySelector('.qty-btn.minus');
        const plusBtn = document.querySelector('.qty-btn.plus');
        if (minusBtn) minusBtn.disabled = newQty <= 1;
        if (plusBtn) plusBtn.disabled = newQty >= maxTickets;
    } catch (error) {
        console.error('Error updating quantity:', error);
    }
}

// Initialize quantity controls
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('ticket-quantity');
    if (input) {
        input.value = 1;
        updateQuantity(0);
    }
});
</script>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "rzp_test_RU4Dbct7tQgcn",
    "amount": "<?php echo $event['price'] * 100; ?>",
    "currency": "INR",
    "name": "Planify: The Event Hub",
    "description": "Payment for <?php echo htmlspecialchars($event['title']); ?>",
    "image": "<?php echo htmlspecialchars($event['image']); ?>",
    "notes": {
        "quantity": 1
    },
    "handler": function (response) {
        const quantity = document.getElementById('ticket-quantity').value;
        const totalAmount = basePrice * quantity;
        
        // Show loading indicator
        const payButton = document.getElementById('rzp-button1');
        payButton.disabled = true;
        payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Send payment details to backend
        fetch('process_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                payment_id: response.razorpay_payment_id,
                event_id: <?php echo $event_id; ?>,
                amount: totalAmount,
                quantity: quantity,
                user_id: <?php echo $_SESSION['user_id']; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Payment successful! Redirecting to dashboard...");
                window.location.href = "user/dashboard.php";
            } else {
                throw new Error(data.message || 'Payment failed');
            }
        })
        .catch(error => {
            alert("Payment failed: " + error.message);
            payButton.disabled = false;
            payButton.innerHTML = 'Proceed to Pay';
        });
    },
    "prefill": {
        "name": "<?php echo htmlspecialchars($_SESSION['username']); ?>",
        "email": "<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>"
    },
    "theme": {
        "color": "#3399cc"
    }
};

// Initialize Razorpay
rzp1 = new Razorpay(options);

document.getElementById('rzp-button1').onclick = function(e) {
    rzp1.open();
    e.preventDefault();
}
</script>

<?php 
$stmt2->close();
$conn->close();
include_once 'includes/footer.php'; 
?>
