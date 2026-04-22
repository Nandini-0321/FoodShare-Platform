<?php
// Email Configuration for Notifications
// Configure your SMTP settings here

// SMTP Configuration (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // or 'ssl'

// IMPORTANT: Use App Password, not your regular Gmail password
// Steps to get App Password:
// 1. Go to your Google Account settings
// 2. Enable 2-Factor Authentication
// 3. Go to Security > App passwords
// 4. Generate a new app password for "Mail"
// 5. Use that 16-character password below

define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password-here'); // Your Gmail App Password

// Sender Information
define('SMTP_FROM_EMAIL', 'noreply@foodshare.com');
define('SMTP_FROM_NAME', 'FoodShare Platform');

// Email Settings
define('EMAIL_ENABLED', false); // Set to true once configured
define('EMAIL_DEBUG', false); // Set to true for debugging

// Notification Triggers
define('SEND_CHAT_EMAIL', true); // Send email on new chat messages
define('SEND_TRACKING_EMAIL', true); // Send email on tracking updates
define('SEND_DELIVERY_EMAIL', true); // Send email on delivery status
define('CHAT_EMAIL_DELAY', 60); // Seconds to wait before sending (to batch messages)

?>
