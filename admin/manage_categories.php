<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

$message = '';

// Process form submission for adding category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    
    // Handle image upload
    $image_path = '';
    if(isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $target_dir = "../uploads/categories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_name = time() . '_' . basename($_FILES["category_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is valid
        $valid_types = array("jpg", "jpeg", "png", "gif");
        if(in_array($imageFileType, $valid_types)) {
            if(move_uploaded_file($_FILES["category_image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/categories/" . $image_name;
            } else {
                $message = '<div class="alert alert-danger">Error uploading image.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.</div>';
        }
    }
    
    $insert_query = "INSERT INTO categories (name, image) VALUES ('$name', '$image_path')";
    
    if ($conn->query($insert_query) === TRUE) {
        $message = '<div class="alert alert-success">Category added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding category: ' . $conn->error . '</div>';
        // Delete uploaded image if database insert fails
        if(!empty($image_path)) {
            unlink("../" . $image_path);
        }
    }
}

// Process category deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Get category image path
    $image_query = "SELECT image FROM categories WHERE id = $category_id";
    $image_result = $conn->query($image_query);
    $category_image = $image_result->fetch_assoc()['image'];
    
    // Check if category has events
    $check_query = "SELECT COUNT(*) as count FROM events WHERE category_id = $category_id";
    $check_result = $conn->query($check_query);
    $has_events = $check_result->fetch_assoc()['count'] > 0;
    
    if ($has_events) {
        $message = '<div class="alert alert-danger">Cannot delete category because it has events assigned to it.</div>';
    } else {
        $delete_query = "DELETE FROM categories WHERE id = $category_id";
        
        if ($conn->query($delete_query) === TRUE) {
            // Delete category image if exists
            if(!empty($category_image)) {
                unlink("../" . $category_image);
            }
            $message = '<div class="alert alert-success">Category deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting category: ' . $conn->error . '</div>';
        }
    }
}

// Get all categories
$categories_query = "SELECT c.*, COUNT(e.id) as event_count 
                    FROM categories c 
                    LEFT JOIN events e ON c.id = e.category_id 
                    GROUP BY c.id 
                    ORDER BY c.name";
$categories_result = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Planify</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .category-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .preview-image {
            max-width: 100px;
            margin-top: 10px;
            display: none;
        }
        .image-preview {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- ... existing sidebar code ... -->
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
                <h2>Manage Categories</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <?php echo $message; ?>
            
            <div class="admin-content-box">
                <div class="admin-section">
                    <h3>Add New Category</h3>
                    <form action="manage_categories.php" method="post" class="inline-form" enctype="multipart/form-data">
                        <div class="form-group inline-form-group">
                            <input type="text" name="name" placeholder="Category Name" required>
                        </div>
                        <div class="form-group inline-form-group">
                            <input type="file" name="category_image" accept="image/*" required onchange="previewImage(this)">
                            <div class="image-preview">
                                <img id="preview" class="preview-image">
                            </div>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-sm">Add Category</button>
                    </form>
                </div>
                
                <div class="admin-section">
                    <h3>All Categories</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Events</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories_result->num_rows > 0): ?>
                                <?php while ($category = $categories_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td>
                                            <?php if (!empty($category['image'])): ?>
                                                <img src="<?php echo '../' . $category['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                                     class="category-thumbnail">
                                            <?php else: ?>
                                                <span class="no-image">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo $category['event_count']; ?></td>
                                        <td class="actions">
                                            <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn-small">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_categories.php?delete=<?php echo $category['id']; ?>" 
                                               class="btn-small btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this category? This cannot be undone.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
    <script src="../js/script.js"></script>
</body>
</html>