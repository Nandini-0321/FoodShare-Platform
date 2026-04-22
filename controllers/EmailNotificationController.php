<?php
require_once __DIR__ . '/../config/email_config.php';
require_once __DIR__ . '/../config/database.php';

// Note: For production, we recommend using PHPMailer. 
// Since we are in a dev environment, we'll use a robust mail wrapper or native mail().
// If you have PHPMailer installed, uncomment the use statements.

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

class EmailNotificationController {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    // Send email notification
    public function sendEmail($to, $subject, $body, $isHtml = true) {
        if (!defined('EMAIL_ENABLED') || !EMAIL_ENABLED) {
            if (defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
                error_log("Email disabled. Would send to $to: $subject");
            }
            return false;
        }

        // Headers
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: ' . ($isHtml ? 'text/html; charset=iso-8859-1' : 'text/plain; charset=iso-8859-1');
        $headers[] = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>';
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        // Try sending using PHP's mail() function
        // Note: For Gmail SMTP on XAMPP, you usually need to configure sendmail.ini or use PHPMailer.
        // This is a basic implementation.
        
        try {
            $result = mail($to, $subject, $body, implode("\r\n", $headers));
            
            if (!$result && defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
                error_log("PHP mail() failed. Check your server configuration.");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    // Send Chat Notification
    public function sendChatNotification($recipient_id, $sender_name, $message_preview, $donation_title) {
        if (!defined('SEND_CHAT_EMAIL') || !SEND_CHAT_EMAIL) return false;
        
        $user = $this->getUser($recipient_id);
        if (!$user) return false;
        
        // Check preferences
        if (!$this->checkPreference($recipient_id, 'chat_notifications')) return false;
        
        $subject = "New message from $sender_name - FoodShare";
        $body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    .header { background-color: #ff6b6b; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { padding: 20px; }
                    .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
                    .btn { display: inline-block; background-color: #ff6b6b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>New Message</h2>
                    </div>
                    <div class='content'>
                        <p>Hello {$user['full_name']},</p>
                        <p>You have a new message regarding <strong>$donation_title</strong>.</p>
                        <p><strong>$sender_name:</strong> \"$message_preview\"</p>
                        <p><a href='" . SITE_URL . "/views/login.php' class='btn'>Reply Now</a></p>
                    </div>
                    <div class='footer'>
                        <p>FoodShare - Connecting Donors with Those in Need</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
    
    // Send Tracking Update Notification
    public function sendTrackingUpdate($recipient_id, $status, $donation_title) {
        if (!defined('SEND_TRACKING_EMAIL') || !SEND_TRACKING_EMAIL) return false;
        
        $user = $this->getUser($recipient_id);
        if (!$user) return false;
        
        if (!$this->checkPreference($recipient_id, 'tracking_notifications')) return false;
        
        $subject = "Delivery Update: $status - FoodShare";
        $body = "
            <html>
            <body>
                <h2>Delivery Update</h2>
                <p>Hello {$user['full_name']},</p>
                <p>The delivery status for <strong>$donation_title</strong> has been updated to: <strong>$status</strong>.</p>
                <p>You can track the live location in your dashboard.</p>
                <p><a href='" . SITE_URL . "/views/login.php'>Track Delivery</a></p>
            </body>
            </html>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
    
    // Helper: Get user details
    private function getUser($user_id) {
        $stmt = $this->conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Helper: Check notification preference
    private function checkPreference($user_id, $type) {
        // Default to true if no settings found
        $stmt = $this->conn->prepare("SELECT $type FROM email_notification_settings WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) return true;
        
        $row = $result->fetch_assoc();
        return (bool)$row[$type];
    }
}
?>
