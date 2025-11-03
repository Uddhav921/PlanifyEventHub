<?php
require_once 'Mail.php';

try {
    $mail = new Mail();
    $result = $mail->sendRegistrationEmail('Test User', 'your-test-email@gmail.com');
    
    if ($result) {
        echo "Test email sent successfully!";
    } else {
        echo "Failed to send test email. Check error log for details.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}