<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Check if admin is logged in
require_admin();

$message = '';
$category = null;

if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);
    $query = "SELECT * FROM categories WHERE id = $category_id";
    $result = $conn->query($query);
    $category = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $category_id = intval($_POST['category_id']);
    
    $update_query = "UPDATE categories SET name = '$name'";
    
    // Handle new image upload if provided
    if (!empty($_FILES['category_image']['name'])) {
        $target_dir = "../uploads/categories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_name = time() . '_' . basename($_FILES["category_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        if(in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            if(move_uploaded_file($_FILES["category_image"]["tmp_name"], $target_file)) {
                // Delete old image
                if (!empty($category['image'])) {
                    unlink("../" . $category['image']);
                }
                
                $image_path = "uploads/categories/" . $image_name;
                $update_query .= ", image = '$image_path'";
            }
        }
    }
    
    $update_query .= " WHERE id = $category_id";
    
    if ($conn->query($update_query) === TRUE) {
        $message = '<div class="alert alert-success">Category updated successfully!</div>';
        // Refresh category data
        $result = $conn->query("SELECT * FROM categories WHERE id = $category_id");
        $category = $result->fetch_assoc();
    } else {
        $message = '<div class="alert alert-danger">Error updating category: ' . $conn->error . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | Planify</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .category-thumbnail {
            max-width: 200px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .preview-image {
            max-width: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
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
                <h2>Edit Category</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>
            
            <?php echo $message; ?>
            
            <?php if ($category): ?>
                <div class="admin-content-box">
                    <form action="edit_category.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                        
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Current Image</label>
                            <?php if (!empty($category['image'])): ?>
                                <img src="<?php echo '../' . $category['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                     class="category-thumbnail">
                            <?php else: ?>
                                <p>No image uploaded</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label>New Image (leave empty to keep current image)</label>
                            <input type="file" name="category_image" accept="image/*" onchange="previewImage(this)">
                            <div class="image-preview">
                                <img id="preview" class="preview-image">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_category" class="btn">Update Category</button>
                            <a href="manage_categories.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">Category not found.</div>
            <?php endif; ?>
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