<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'Mail.php';

session_check();

// Check if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

$error = "";
$success = "";

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'] == 'admin' ? 'admin' : 'user';
    
    // Validate form
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if username already exists
        $check_username = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_username);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Check if email already exists
            $check_email = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_email);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user using prepared statement
                $sql = "INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $username, $email, $hashed_password, $user_type);
                
                if ($stmt->execute()) {
                    // Send registration email (only if PHPMailer is available)
                    $emailSent = false;
                    if (class_exists('Mail')) {
                        $mail = new Mail();
                        $emailSent = $mail->sendRegistrationEmail($username, $email);
                    }
                    
                    if ($emailSent) {
                        $success = "Registration successful! Please check your email for confirmation.";
                    } else {
                        $success = "Registration successful! You can now login.";
                        error_log("Failed to send registration email to: $email");
                    }
                } else {
                    $error = "Registration failed. Please try again.";
                    error_log("Registration Error: " . $stmt->error);
                }
            }
        }
        $stmt->close();
    }
}

include_once 'includes/header.php';
?>

<section class="login-section">
    <div class="container">
        <div class="form-container">
            <h2>Register with Planify</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <div class="form-footer">
                    <p>You can now <a href="login.php">Login here</a></p>
                </div>
            <?php else: ?>
                <form action="register.php" method="post" class="registration-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required pattern="[A-Za-z0-9_]{3,20}"
                               title="Username must be between 3 and 20 characters, and can only contain letters, numbers, and underscores">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" 
                               required minlength="6"
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                               title="Must contain at least one number, one uppercase and lowercase letter, and at least 6 characters">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="user_type">Register As</label>
                        <select id="user_type" name="user_type">
                            <option value="user" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'user') ? 'selected' : ''; ?>>User</option>
                             <option value="admin">Admin</option>
                          
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-block">Register</button>
                </form>
                
                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php 
$conn->close();
include_once 'includes/footer.php'; 
?>