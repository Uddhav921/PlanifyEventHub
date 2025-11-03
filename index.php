<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/header.php';

?>

<link rel="stylesheet" href="css/style.css"> 
<script src="js/slider.js"></script>
<section class="hero-slider">
    <div class="slider-container">
        <div class="slider">
            <div class="slide active">
                <img src="images/image4.jpeg" alt="Event 1">
                <div class="slide-content">
                    <h2>Discover Amazing Events</h2>
                    <p>Find and book tickets for the best events in your area</p>
                    <a href="events.php" class="btn">Browse Events</a>
                </div>
            </div>
            <div class="slide">
                <img src="images/slide2.jpg" alt="Event 2">
                <div class="slide-content">
                    <h2>Music Festivals</h2>
                    <p>Experience the best music festivals around the world</p>
                    <a href="events.php?category=music" class="btn">View Festivals</a>
                </div>
            </div>
            <div class="slide">
                <img src="images/image4.jpeg" alt="book">
                <div class="slide-content">
                    <h2>Business Events</h2>
                    <p>Network with professionals at top business events</p>
                    <a href="events.php?category=business" class="btn">Business Event</a>
                </div>
            </div>
        </div>
        <div class="slider-controls">
            <button class="prev-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="next-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="slider-indicators">
            <span class="indicator active"></span>
            <span class="indicator"></span>
            <span class="indicator"></span>
        </div>
    </div>
</section>


<!-- Browse by Category Section -->
<section class="categories">
    <div class="container">
        <h2 class="section-title">Browse by Category</h2>
        <div class="category-grid">
            <?php
            $categories = get_categories($conn);
            foreach ($categories as $category) {
                echo '<a href="events.php?category=' . $category['id'] . '" class="category-card">';
                echo '<div class="category-image">';
                echo '<img src="' . $category['image'] . '" alt="' . $category['name'] . '">';
                echo '</div>';
                echo '<h3>' . $category['name'] . '</h3>';
                echo '</a>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Upcoming Events Section -->
<section class="upcoming-events">
    <div class="container">
        <h2 class="section-title">Upcoming Events</h2>
        <div class="events-slider">
            <?php
            $upcoming_events = get_upcoming_events($conn, 6);
            foreach ($upcoming_events as $event) {
                echo '<div class="event-slide">';
                echo '<div class="event-card">';
                echo '<div class="event-image">';
                echo '<img src="' . $event['image'] . '" alt="' . $event['title'] . '">';
                echo '<div class="event-date">' . date('M d', strtotime($event['event_date'])) . '</div>';
                echo '</div>';
                echo '<div class="event-details">';
                echo '<h3>' . $event['title'] . '</h3>';
                echo '<p class="event-location"><i class="fas fa-map-marker-alt"></i> ' . $event['location'] . '</p>';
                echo '<div class="event-meta">';
                echo '<span class="event-time"><i class="far fa-clock"></i> ' . date('g:i A', strtotime($event['event_time'])) . '</span>';
                echo '<span class="event-price">' . number_format($event['price'], 2) . '</span>';
                echo '</div>';
                echo '<a href="event_details.php?id=' . $event['id'] . '" class="btn">View Details</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>


<!-- Popular Events Section -->
<section class="popular-events">
    <div class="container">
        <h2 class="section-title">Popular Events</h2>
        <div class="events-grid">
            <?php
            $popular_events = get_popular_events($conn);
            foreach ($popular_events as $event) {
                echo '<div class="event-card">';
                echo '<div class="event-image">';
                echo '<img src="' . $event['image'] . '" alt="' . $event['title'] . '">';
                echo '<div class="event-date">' . date('M d', strtotime($event['event_date'])) . '</div>';
                echo '</div>';
                echo '<div class="event-details">';
                echo '<h3>' . $event['title'] . '</h3>';
                echo '<p class="event-location"><i class="fas fa-map-marker-alt"></i> ' . $event['location'] . '</p>';
                echo '<p class="event-time"><i class="far fa-clock"></i> ' . date('g:i A', strtotime($event['event_time'])) . '</p>';
                echo '<div class="event-price">' . number_format($event['price'], 2) . '</div>';
                echo '<a href="event_details.php?id=' . $event['id'] . '" class="btn">View Details</a>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
        <div class="view-all">
            <a href="events.php" class="btn btn-outline">View All Events</a>
        </div>
    </div>
</section>







<!-- Contact Section -->
<section class="contact-section" id="contact">
    <div class="container">
        <h2 class="section-title">Contact Us</h2>
        <p class="section-description">Have questions about events or want to become an organizer?</p>
        
        <?php if (isset($_GET['message_sent']) && $_GET['message_sent'] == 'success'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Your message has been sent successfully! We'll get back to you soon.
            </div>
        <?php elseif (isset($_GET['message_sent']) && $_GET['message_sent'] == 'error'): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> There was an error sending your message. Please try again.
            </div>
        <?php endif; ?>
        
        <div class="contact-container">
            <div class="contact-info">
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Our Location</h3>
                    <p>Pune Maharashtra India</p>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <h3>Email Us</h3>
                    <p>planifyeventhub.com</p>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-phone-alt"></i>
                    <h3>Call Us</h3>
                    <p>+91 9022954025</p>
                </div>
            </div>
            
            <div class="contact-form">
                <?php if (is_logged_in()): ?>
                    <form action="send_message.php" method="post">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" name="contact" class="btn">Send Message</button>
                        </div>
                    </form>
                    
                    <div class="organizer-cta">
                        <h4>Want to organize events with us?</h4>
                        <a href="become_organizer.php" class="btn btn-outline">Become an Organizer</a>
                    </div>
                <?php else: ?>
                    <div class="login-required">
                        <i class="fas fa-lock"></i>
                        <h3>Login Required</h3>
                        <p>You must be logged in to contact us or become an organizer.</p>
                        <a href="login.php" class="btn">Login Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
// Let's add the required functions to functions.php
// These functions are needed for the index page to work properly

// Add these to your includes/functions.php file if they don't exist:
// 
// function get_categories($conn) {
//     $query = "SELECT * FROM categories ORDER BY name";
//     $result = $conn->query($query);
//     $categories = [];
//     
//     while ($row = $result->fetch_assoc()) {
//         // If no image is set, use a default
//         if (empty($row['image'])) {
//             $row['image'] = 'images/categories/default.jpg';
//         }
//         $categories[] = $row;
//     }
//     
//     return $categories;
// }
// 
// function get_popular_events($conn, $limit = 6) {
//     $query = "SELECT e.*, c.name as category_name 
//               FROM events e 
//               LEFT JOIN categories c ON e.category_id = c.id 
//               ORDER BY e.views DESC 
//               LIMIT $limit";
//     $result = $conn->query($query);
//     $events = [];
//     
//     while ($row = $result->fetch_assoc()) {
//         $events[] = $row;
//     }
//     
//     return $events;
// }
// 
// function get_upcoming_events($conn, $limit = 6) {
//     $today = date('Y-m-d');
//     $query = "SELECT e.*, c.name as category_name 
//               FROM events e 
//               LEFT JOIN categories c ON e.category_id = c.id 
//               WHERE e.event_date >= '$today' 
//               ORDER BY e.event_date ASC 
//               LIMIT $limit";
//     $result = $conn->query($query);
//     $events = [];
//     
//     while ($row = $result->fetch_assoc()) {
//         $events[] = $row;
//     }
//     
//     return $events;
// }
?>

<?php include_once 'includes/footer.php'; ?>