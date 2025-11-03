<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

$message = "";
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if event exists
$event_query = "SELECT e.*, c.name as category_name 
                FROM events e 
                LEFT JOIN categories c ON e.category_id = c.id 
                WHERE e.id = $event_id";
$event_result = $conn->query($event_query);

if ($event_result->num_rows == 0) {
    header("Location: manage_events.php");
    exit();
}

$event = $event_result->fetch_assoc();

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Process form submission
// Update the form processing section where the SQL query is

// ...existing code...

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $category_id = intval($_POST['category']); // Changed variable name for clarity
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $location = $conn->real_escape_string($_POST['location']);
    $price = floatval($_POST['price']);
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    
    // Handle image upload
    $image = $event['image']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/";
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Delete old image if it exists and is not the default
            if ($event['image'] && file_exists("../" . $event['image'])) {
                unlink("../" . $event['image']);
            }
            $image = "images/" . $new_filename;
        }
    }
    
    // Update event in database with corrected column name
    $sql = "UPDATE events SET 
            title = '$title', 
            description = '$description', 
            image = '$image', 
            category_id = $category_id,  # Changed from category to category_id
            event_date = '$event_date', 
            event_time = '$event_time', 
            location = '$location', 
            price = $price, 
            is_popular = $is_popular 
            WHERE id = $event_id";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Event updated successfully";
        // Refresh event data
        $event_result = $conn->query($event_query);
        $event = $event_result->fetch_assoc();
    } else {
        $message = "Error: " . $conn->error;
    }
}

// ...existing code...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event | Planify</title>
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
                    <li><a href="add_event.php"><i class="fas fa-plus-circle"></i> Add New Event</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h2>Edit Event</h2>
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
                <form action="edit_event.php?id=<?php echo $event_id; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input type="text" id="title" name="title" value="<?php echo $event['title']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required><?php echo $event['description']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Event Image</label>
                        <div class="current-image">
                            <img src="../<?php echo $event['image']; ?>" alt="Current Image" width="100">
                            <p>Current image: <?php echo $event['image']; ?></p>
                        </div>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Leave empty to keep current image</small>
                    </div>
                    
                    // Update the category selection part in the HTML form

<div class="form-group">
    <label for="category">Category</label>
    <select id="category" name="category" required>
        <?php 
        $categories_result->data_seek(0);
        while ($category = $categories_result->fetch_assoc()): 
        ?>
            <option value="<?php echo $category['id']; ?>" 
                <?php echo $event['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                <?php echo $category['name']; ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="event_date">Event Date</label>
                            <input type="date" id="event_date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                        </div>
                        
                        <div class="form-group half">
                            <label for="event_time">Event Time</label>
                            <input type="time" id="event_time" name="event_time" value="<?php echo $event['event_time']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo $event['location']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $event['price']; ?>" required>
                    </div>
                    
                    <div class="form-group checkbox">
                        <input type="checkbox" id="is_popular" name="is_popular" <?php echo $event['is_popular'] ? 'checked' : ''; ?>>
                        <label for="is_popular">Mark as Popular Event</label>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn">Update Event</button>
                        <a href="manage_events.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>