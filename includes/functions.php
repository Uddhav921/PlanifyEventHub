<?php
// Start session if not already started
function session_check() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    session_check();
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    session_check();
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect if not admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: ../index.php");
        exit();
    }
}

// Get popular events
function get_popular_events($conn, $limit = 6) {
    $sql = "SELECT * FROM events WHERE is_popular = 1 ORDER BY event_date LIMIT $limit";
    $result = $conn->query($sql);
    
    $events = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    return $events;
}
// Get categories for display
function get_categories($conn) {
    $query = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($query);
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        // If no image is set, use a default
        if (empty($row['image'])) {
            $row['image'] = 'images/categories/default.jpg';
        }
        $categories[] = $row;
    }
    
    return $categories;
}

// Get events by category
function get_events_by_category($conn, $category_id, $limit = 10) {
    $sql = "SELECT * FROM events WHERE category = '$category_id' ORDER BY event_date LIMIT $limit";
    $result = $conn->query($sql);
    
    $events = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    return $events;
}


function get_upcoming_events($conn, $limit = 6) {
    $today = date('Y-m-d');
    $query = "SELECT e.*, c.name as category_name 
              FROM events e 
              LEFT JOIN categories c ON e.category_id = c.id 
              WHERE e.event_date >= '$today' 
              ORDER BY e.event_date ASC 
              LIMIT $limit";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}


function testFunction() {
    return "Functions file loaded!";
}


?>


