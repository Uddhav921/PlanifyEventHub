<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

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

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, password, user_type FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Redirect based on user type
            if ($user['user_type'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}

include_once 'includes/header.php';
?>

<section class="login-section">
    <div class="container">
        <div class="form-container">
            <h2>Login to Planify</h2>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                
                <div class="form-group">
                    <label for="user_type">Login As</label>
                    <select id="user_type" name="user_type">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-block">Login</button>

                
            </form>
            <div class="form-group">
    
    
    <a href="forget_pass.php" class="forgot-password">Forgot Password?</a>
</div>
            <div class="form-footer">

                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>