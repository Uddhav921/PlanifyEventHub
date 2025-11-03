<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $organization_name = $conn->real_escape_string($_POST['organization_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $description = $conn->real_escape_string($_POST['description']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $website = $conn->real_escape_string($_POST['website']);
    
    $query = "INSERT INTO organizer_requests (user_id, organization_name, email, description, phone, website) 
              VALUES ($user_id, '$organization_name', '$email', '$description', '$phone', '$website')";
    
    if ($conn->query($query) === TRUE) {
        $message = '<div class="alert alert-success">Your organizer request has been submitted successfully. We will review it and get back to you soon.</div>';
    } else {
        $message = '<div class="alert alert-danger">Error submitting request: ' . $conn->error . '</div>';
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Become an Organizer</h1>
    </div>
</div>

<section class="organizer-request-section">
    <div class="container">
        <?php echo $message; ?>
        
        <div class="content-box">
            <div class="organizer-info">
                <h2>Why Become an Organizer?</h2>
                <ul>
                    <li><i class="fas fa-check"></i> Create and manage your own events</li>
                    <li><i class="fas fa-check"></i> Reach thousands of potential attendees</li>
                    <li><i class="fas fa-check"></i> Manage ticket sales and registrations</li>
                    <li><i class="fas fa-check"></i> Get detailed analytics and insights</li>
                    <li><i class="fas fa-check"></i> Professional support from our team</li>
                </ul>
            </div>
            
            <div class="organizer-form">
                <h3>Submit Your Application</h3>
                <form action="become_organizer.php" method="post">
                    <div class="form-group">
                        <label for="organization_name">Organization Name</label>
                        <input type="text" id="organization_name" name="organization_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Business Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Contact Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Tell us about your organization</label>
                        <textarea id="description" name="description" rows="5" required></textarea>
                    </div>
                    
                   
                    <div class="form-group">
                        <label for="website">Website (optional)</label>
                        <input type="url" id="website" name="website">
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>