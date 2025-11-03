<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

$message = "";

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $category_id = intval($_POST['category']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $location = $conn->real_escape_string($_POST['location']);
    $price = floatval($_POST['price']);
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    
    // Get current user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Handle image upload
    $image = "images/event_default.jpg"; // Default image
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = "images/" . $new_filename;
        }
    }
    $quantity = intval($_POST['quantity']);
    // Insert event into database - ADDED user_id to the SQL query
    $sql = "INSERT INTO events (title, description, image, category_id, event_date, event_time, location, price, is_popular, user_id, quantity) 
        VALUES ('$title', '$description', '$image', $category_id, '$event_date', '$event_time', '$location', $price, $is_popular, $user_id, $quantity)";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Event created successfully";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event | Planify</title>
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
                <h2>Add New Event</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, "Error") !== false ? "alert-danger" : "alert-success"; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form action="add_event.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Event Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <?php 
                            // Reset pointer to beginning of result set
                            if ($categories_result->num_rows > 0) {
                                $categories_result->data_seek(0);
                                while ($category = $categories_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php 
                                endwhile; 
                            } else {
                                echo '<option value="">No categories found</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="event_date">Event Date</label>
                            <input type="date" id="event_date" name="event_date" required>
                        </div>
                        
                        <div class="form-group half">
                            <label for="event_time">Event Time</label>
                            <input type="time" id="event_time" name="event_time" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (Rs)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    
<div class="form-group">
    <label for="quantity">Available Tickets</label>
    <input type="number" id="quantity" name="quantity" min="1" required>
    <small class="form-text">Enter the total number of tickets available for this event</small>
</div>
                    <div class="form-group checkbox">
                        <input type="checkbox" id="is_popular" name="is_popular">
                        <label for="is_popular">Mark as Popular Event</label>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn">Add Event</button>
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>