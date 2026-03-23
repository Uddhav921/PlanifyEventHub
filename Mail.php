<?php

$_phpmailer_autoload      = __DIR__ . '/vendor/autoload.php';
$_phpmailer_autoload_real = __DIR__ . '/vendor/composer/autoload_real.php';

if (file_exists($_phpmailer_autoload) && file_exists($_phpmailer_autoload_real)) {
    require_once $_phpmailer_autoload;

    class Mail {
        private $mailer;

        public function __construct() {
            $this->mailer = $this->configureMailer();
        }

        private function configureMailer() {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                $mail->SMTPDebug  = 0;
                $mail->Debugoutput = 'error_log';

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = getenv('SMTP_USERNAME');
                $mail->Password   = getenv('SMTP_PASSWORD');
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    ]
                ];

                $mail->CharSet = 'UTF-8';
                $mail->setFrom('planifyeventhub@gmail.com', 'Planify Event Hub');

                return $mail;
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                error_log("Mail configuration error: " . $e->getMessage());
                throw $e;
            }
        }

        public function sendRegistrationEmail($username, $email) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($email, $username);

                $this->mailer->isHTML(true);
                $this->mailer->Subject = 'Welcome to Planify: Your Event Management Hub!';
                $this->mailer->Body    = $this->getRegistrationEmailTemplate($username, $email);
                $this->mailer->AltBody = strip_tags($this->mailer->Body);

                return $this->mailer->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                error_log("Failed to send registration email: " . $e->getMessage());
                return false;
            }
        }

        private function getRegistrationEmailTemplate($username, $email) {
            $login_link   = "http://" . $_SERVER['HTTP_HOST'] . "/register/login.php";
            $website_link = "http://" . $_SERVER['HTTP_HOST'] . "/";

            return "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:20px;'>
                <h2 style='text-align:center;color:#333;'>Welcome to Planify!</h2>
                <p>Dear {$username},</p>
                <p>Thank you for registering. Your account details:</p>
                <p>Name: {$username}<br>Email: {$email}</p>
                <p><a href='{$login_link}' style='color:#007bff;'>Log in to your account</a></p>
                <p>Best Regards,<br>The Planify Team</p>
                <p><a href='{$website_link}' style='color:#007bff;'>Visit Website</a></p>
            </div>";
        }

        public function sendPasswordResetEmail($username, $userEmail, $resetLink) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($userEmail, $username);

                $this->mailer->isHTML(true);
                $this->mailer->Subject = 'Password Reset Request - Planify';
                $this->mailer->Body    = $this->getPasswordResetTemplate($username, $resetLink);
                $this->mailer->AltBody = strip_tags($this->mailer->Body);

                return $this->mailer->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                error_log("Password reset email error: " . $e->getMessage());
                return false;
            }
        }

        private function getPasswordResetTemplate($username, $resetLink) {
            return "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:20px;'>
                <h2 style='color:#2c3e50;text-align:center;'>Password Reset Request</h2>
                <p>Hello {$username},</p>
                <p>Click the button below to reset your password:</p>
                <div style='text-align:center;margin:30px 0;'>
                    <a href='{$resetLink}' style='background:#3498db;color:white;padding:12px 25px;text-decoration:none;border-radius:5px;'>
                        Reset Password
                    </a>
                </div>
                <p>Or paste this link: {$resetLink}</p>
                <p>This link expires in 1 hour.</p>
                <p>&copy; " . date('Y') . " Planify. All rights reserved.</p>
            </div>";
        }
    }
}
// If vendor/autoload.php doesn't exist, the Mail class simply won't be defined.
// Both register.php and forget_pass.php check class_exists('Mail') before using it.
?>
