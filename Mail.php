<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    // PHPMailer not installed — mail functions will be unavailable
    // Pages will still load; email sending will silently return false
    return;
}
require_once $autoload;

class Mail {
    private $mailer;
    
    public function __construct() {
        $this->mailer = $this->configureMailer();
    }
    
    private function configureMailer() {
        try {
            $mail = new PHPMailer(true);
            
            // Debug settings
            $mail->SMTPDebug = 0; // Set to SMTP::DEBUG_SERVER for debugging
            $mail->Debugoutput = 'error_log';
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USERNAME'); // Use environment variables
            $mail->Password = getenv('SMTP_PASSWORD'); // Use environment variables
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // SSL/TLS Settings
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Charset
            $mail->CharSet = 'UTF-8';
            
            // Default sender
            $mail->setFrom('planifyeventhub@gmail.com', 'Planify Event Hub');
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mail configuration error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function sendRegistrationEmail($username, $email) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $username);
            
            // Email content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to Planify: Your Event Management Hub! 🎉';
            $this->mailer->Body = $this->getRegistrationEmailTemplate($username, $email);
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $this->mailer->Body));
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send registration email: " . $e->getMessage());
            return false;
        }
    }
    
    private function getRegistrationEmailTemplate($username, $email) {
        $login_link = "http://" . $_SERVER['HTTP_HOST'] . "/Fresh/login.php";
        $website_link = "http://" . $_SERVER['HTTP_HOST'] . "/Fresh";
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px;'>
            <h2 style='text-align: center; color: #333;'>Welcome to Planify: Your Event Management Hub! 🎉</h2>
            <p>Dear {$username},</p>
            <p>Thank you for registering on Planify: The Event Hub!</p>
            <p><strong>Your Registered Details:</strong></p>
            <p>Name: {$username}</p>
            <p>Email: {$email}</p>
            <p>🔹 <a href='{$login_link}' style='color: #007bff;'>Log in to your account</a></p>
            <p>🚀 Let's make event planning easy and exciting!</p>
            <p>Best Regards,<br>The Planify Team</p>
            <p><a href='{$website_link}' style='color: #007bff;'>Visit Website</a></p>
        </div>";
    }

    public function sendPasswordResetEmail($username, $userEmail, $resetLink) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $username);
            
            // Email settings
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request - Planify';
            $this->mailer->Body = $this->getPasswordResetTemplate($username, $resetLink);
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $this->mailer->Body));
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Password reset email error: " . $e->getMessage());
            return false;
        }
    }

    private function getPasswordResetTemplate($username, $resetLink) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px;'>
            <h2 style='color: #2c3e50; text-align: center;'>Password Reset Request</h2>
            <p>Hello {$username},</p>
            <p>To reset your password, click the button below:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' 
                   style='background: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;'>
                    Reset Password
                </a>
            </div>
            <p>Or copy and paste this link in your browser:</p>
            <p style='background: #fff; padding: 10px; border-radius: 5px;'>{$resetLink}</p>
            <p>This link will expire in 1 hour.</p>
            <p>© " . date('Y') . " Planify. All rights reserved.</p>
        </div>";
    }
}
?>
