<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/header.php';

// Get filter parameters with proper validation
$category_filter = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
$date_filter = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING) ?: '';
$price_min = filter_input(INPUT_GET, 'price_min', FILTER_VALIDATE_FLOAT) ?: 0;
$price_max = filter_input(INPUT_GET, 'price_max', FILTER_VALIDATE_FLOAT) ?: 999999;
$location_filter = filter_input(INPUT_GET, 'location', FILTER_SANITIZE_STRING) ?: '';
$search_query = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?: '';

// Build query conditions
$where_conditions = [];
$params = [];
$types = '';

if ($category_filter > 0) {
    $where_conditions[] = "e.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

if ($date_filter) {
    $where_conditions[] = "DATE(e.event_date) = ?";
    $params[] = $date_filter;
    $types .= 's';
}

if ($price_min > 0) {
    $where_conditions[] = "e.price >= ?";
    $params[] = $price_min;
    $types .= 'd';
}

if ($price_max < 999999) {
    $where_conditions[] = "e.price <= ?";
    $params[] = $price_max;
    $types .= 'd';
}

if ($location_filter) {
    $where_conditions[] = "e.location LIKE ?";
    $params[] = "%$location_filter%";
    $types .= 's';
}

if ($search_query) {
    $where_conditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'ss';
}

// Build and execute main query
$events_query = "SELECT e.*, c.name as category_name, u.username as organizer_name 
                FROM events e 
                JOIN categories c ON e.category_id = c.id
                LEFT JOIN users u ON e.organizer_id = u.id";

if (!empty($where_conditions)) {
    $events_query .= " WHERE " . implode(" AND ", $where_conditions);
}

$events_query .= " ORDER BY e.event_date ASC";

// Debug query if needed
// error_log("Query: " . $events_query);
// error_log("Params: " . print_r($params, true));

$stmt = $conn->prepare($events_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events_result = $stmt->get_result();

// Get categories for filter
$categories_stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
$categories_stmt->execute();
$all_categories_result = $categories_stmt->get_result();

// Get locations for filter
$locations_stmt = $conn->prepare("SELECT DISTINCT location FROM events ORDER BY location");
$locations_stmt->execute();
$locations_result = $locations_stmt->get_result();

// Get category name if filter is active
$category_name = '';
if ($category_filter > 0) {
    $cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->bind_param('i', $category_filter);
    $cat_stmt->execute();
    $category_result = $cat_stmt->get_result();
    if ($category = $category_result->fetch_assoc()) {
        $category_name = $category['name'];
    }
}
?>

<section class="page-header">
    <div class="container">
        <h1>Discover Events</h1>
        <p>Find the perfect event for you</p>
    </div>
</section>

<section class="events-section">
    <div class="container">
        <!-- Filter Form -->
        <div class="events-filter">
            <form action="events.php" method="GET" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-item search-bar">
                        <input type="text" name="search" 
                               placeholder="Search events..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>

                    <div class="filter-item">
                        <label>Category</label>
                        <select name="category">
                            <option value="0">All Categories</option>
                            <?php while ($cat = $all_categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label>Event Date</label>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>

                    <div class="filter-item price-range">
                        <label>Price Range (Rs)</label>
                        <div class="price-inputs">
                            <input type="number" name="price_min" min="0" step="0.01" 
                                   placeholder="Min" value="<?php echo $price_min ?: ''; ?>">
                            <span>to</span>
                            <input type="number" name="price_max" min="0" step="0.01" 
                                   placeholder="Max" value="<?php echo $price_max < 999999 ? $price_max : ''; ?>">
                        </div>
                    </div>

                    <div class="filter-item">
                        <label>Location</label>
                        <select name="location">
                            <option value="">All Locations</option>
                            <?php while ($loc = $locations_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($loc['location']); ?>"
                                        <?php echo $location_filter == $loc['location'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc['location']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-item filter-buttons">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="events.php" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Active Filters Display -->
        <?php if ($category_filter || $date_filter || $price_min > 0 || $price_max < 999999 || $location_filter || $search_query): ?>
            <div class="active-filters">
                <h4>Active Filters:</h4>
                <div class="filter-tags">
                    <?php if ($search_query): ?>
                        <span class="filter-tag">
                            Search: <?php echo htmlspecialchars($search_query); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($category_filter && $category_name): ?>
                        <span class="filter-tag">
                            Category: <?php echo htmlspecialchars($category_name); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($date_filter): ?>
                        <span class="filter-tag">
                            Date: <?php echo date('M d, Y', strtotime($date_filter)); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($price_min > 0 || $price_max < 999999): ?>
                        <span class="filter-tag">
                            Price: Rs <?php echo number_format($price_min, 2); ?> - 
                            Rs <?php echo number_format($price_max, 2); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($location_filter): ?>
                        <span class="filter-tag">
                            Location: <?php echo htmlspecialchars($location_filter); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Events List -->
        <div class="events-list">
            <?php if ($events_result->num_rows > 0): ?>
                <?php while ($event = $events_result->fetch_assoc()): ?>
                    <div class="event-card horizontal">
                        <div class="event-image">
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <div class="event-date">
                                <?php echo date('M d', strtotime($event['event_date'])); ?>
                            </div>
                        </div>
                        <div class="event-details">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-category">
                                <?php echo htmlspecialchars($event['category_name']); ?>
                            </p>
                            <p class="event-organizer">
                                <i class="fas fa-user"></i> 
                                By <?php echo htmlspecialchars($event['organizer_name']); ?>
                            </p>
                            <p class="event-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            <p class="event-time">
                                <i class="far fa-clock"></i> 
                                <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                            </p>
                            <p class="event-description">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...
                            </p>
                            <div class="event-footer">
                                <div class="event-price">
                                    Rs <?php echo number_format($event['price'], 2); ?>
                                </div>
                                <?php if (is_logged_in()): ?>
                                    <a href="event_details.php?id=<?php echo $event['id']; ?>" 
                                       class="btn">View Details</a>
                                <?php else: ?>
                                    <a href="login.php?redirect=events.php" 
                                       class="btn btn-secondary">Login to View</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-events">
                    <i class="far fa-calendar-times"></i>
                    <p>No events found matching your criteria. Please try different filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php 
// Close all prepared statements
$stmt->close();
$categories_stmt->close();
$locations_stmt->close();
if (isset($cat_stmt)) $cat_stmt->close();

include_once 'includes/footer.php'; 
?>