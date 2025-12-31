<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getSettings($db) {
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        error_log('Error loading settings: ' . $e->getMessage());
        return [];
    }
}

function sendEmail($to, $subject, $body, $isHTML = true) {
    global $mailerError;
    $mailerError = '';
    
    try {
        $db = getDB();
        $settings = getSettings($db);
        
        $mail = new PHPMailer(true);
        
        // Enable verbose debug output for testing (disable in production)
        $mail->SMTPDebug = 0; // Set to 2 for detailed debug output
        $mail->Debugoutput = function($str, $level) {
            global $mailerError;
            $mailerError .= $str . "\n";
        };
        
        // Server settings
        if (!empty($settings['smtp_enabled']) && $settings['smtp_enabled'] == '1') {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $settings['smtp_username'] ?? '';
            $mail->Password = $settings['smtp_password'] ?? '';
            $mail->SMTPSecure = $settings['smtp_secure'] ?? 'tls';
            $mail->Port = intval($settings['smtp_port'] ?? 587);
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        } else {
            // Use PHP mail() function
            $mail->isMail();
        }
        
        // Recipients
        $fromEmail = $settings['smtp_from_email'] ?? 'awaisitours@gmail.com';
        $fromName = $settings['smtp_from_name'] ?? 'Awaisi Tours';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->CharSet = 'UTF-8';
        
        if (!$mail->send()) {
            $error = $mail->ErrorInfo;
            error_log('Mailer Error: ' . $error);
            $mailerError = $error;
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log('Mailer Exception: ' . $error);
        $mailerError = $error;
        return false;
    }
}

function getMailerError() {
    global $mailerError;
    return $mailerError ?? 'Unknown error';
}

function sendRegistrationEmail($to, $data) {
    $db = getDB();
    $settings = getSettings($db);
    $appName = $settings['app_name'] ?? 'Hajj Registration CRM';
    
    $subject = "Hajj 2026 Registration Confirmation - " . $data['form_id'];
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(to right, #1a5f7a, #2d8b9c); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .info-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2d8b9c; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Hajj 2026 Registration Confirmed</h1>
            </div>
            <div class='content'>
                <p>Dear {$data['name']},</p>
                <p>Thank you for completing your Hajj 2026 registration with Awaisi Tours.</p>
                
                <div class='info-box'>
                    <strong>Your Registration ID:</strong> {$data['form_id']}
                </div>
                
                <p>We have received your registration and will review it shortly. You will receive another email once your registration has been processed.</p>
                
                <p>If you have any questions, please contact us at " . ($settings['smtp_from_email'] ?? 'awaisitours@gmail.com') . "</p>
                
                <p>Best regards,<br>{$appName} Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $message, true);
}

function sendAdminNotification($customerId, $formId) {
    $db = getDB();
    $settings = getSettings($db);
    $to = $settings['notification_email'] ?? $settings['smtp_from_email'] ?? 'awaisitours@gmail.com';
    
    $subject = "New Hajj Registration: " . $formId;
    
    $appUrl = $settings['app_url'] ?? 'http://localhost/mini-crm';
    
    $message = "A new Hajj registration has been submitted.\n\n";
    $message .= "Registration ID: $formId\n";
    $message .= "Customer ID: $customerId\n";
    $message .= "View details in admin panel: $appUrl/admin/customer-details.php?id=$customerId\n";
    
    return sendEmail($to, $subject, $message, false);
}

