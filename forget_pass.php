<?php
session_start();
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
require_once 'Mail.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        try {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete any existing reset tokens for this user
                $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $delete_stmt->bind_param("i", $user['id']);
                $delete_stmt->execute();
                
                // Store new token
                $insert_stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("iss", $user['id'], $token_hash, $expiry);
                
                if ($insert_stmt->execute()) {
                    // Send reset email
                    $mail = new Mail();
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Fresh/reset_password.php?token=" . $token;
                    
                    $emailSent = $mail->sendPasswordResetEmail(
                        $user['username'],
                        $email,
                        $reset_link
                    );
                    
                    if ($emailSent) {
                        $success = "Password reset instructions have been sent to your email";
                    } else {
                        $error = "Failed to send reset email. Please try again later.";
                    }
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
            } else {
                // To prevent email enumeration, show the same message
                $success = "If an account exists with this email, password reset instructions have been sent";
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}

include_once 'includes/header.php';
?>

<div class="forget-pass-container">
    <div class="form-wrapper">
        <h2>Reset Your Password</h2>
        <p class="form-description">Enter your email address and we'll send you instructions to reset your password.</p>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="forget_pass.php" method="post" class="reset-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       placeholder="Enter your registered email">
            </div>
            
            <button type="submit" class="btn-reset">Send Reset Link</button>
            
            <div class="form-footer">
                <a href="login.php" class="back-to-login">← Back to Login</a>
            </div>
        </form>
    </div>
</div>

<style>
.forget-pass-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    padding: 20px;
}

.form-wrapper {
    background: #ffffff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}

.form-wrapper h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 20px;
}

.form-description {
    color: #666;
    text-align: center;
    margin-bottom: 30px;
    font-size: 14px;
    line-height: 1.6;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    border-color: #3498db;
    outline: none;
}

.btn-reset {
    width: 100%;
    padding: 12px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-reset:hover {
    background: #2980b9;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 14px;
    text-align: center;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 14px;
    text-align: center;
}

.form-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.back-to-login {
    color: #3498db;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.back-to-login:hover {
    color: #2980b9;
}

@media (max-width: 480px) {
    .form-wrapper {
        padding: 20px;
    }
    
    .form-wrapper h2 {
        font-size: 1.5em;
    }
    
    .btn-reset {
        font-size: 14px;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?>