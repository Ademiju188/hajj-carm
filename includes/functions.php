<?php
// Helper Functions

function uploadFile($file, $subfolder = '', $maxSize = null, $allowedTypes = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validate file size (use custom maxSize if provided, otherwise use default)
    $maxFileSize = $maxSize ?? MAX_FILE_SIZE;
    if ($file['size'] > $maxFileSize) {
        return false;
    }
    
    // Validate file type (use custom allowedTypes if provided)
    $defaultAllowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
    $fileAllowedTypes = $allowedTypes ?? $defaultAllowedTypes;
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $fileAllowedTypes)) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $uploadPath = UPLOAD_DIR . $subfolder . '/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadPath . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $subfolder . '/' . $filename;
    }
    
    return false;
}

function sendRegistrationEmail_OLD($to, $data) {
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
                
                <p>If you have any questions, please contact us at awaisitours@gmail.com</p>
                
                <p>Best regards,<br>Awaisi Tours Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function sendAdminNotification_OLD($customerId, $formId) {
    $to = SMTP_FROM_EMAIL;
    $subject = "New Hajj Registration: " . $formId;
    
    $message = "A new Hajj registration has been submitted.\n\n";
    $message .= "Registration ID: $formId\n";
    $message .= "Customer ID: $customerId\n";
    $message .= "View details in admin panel: " . APP_URL . "/admin/customers.php?id=$customerId\n";
    
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function formatDate($date) {
    if (empty($date)) return 'N/A';
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    if (empty($datetime)) return 'N/A';
    return date('d M Y, h:i A', strtotime($datetime));
}

function isLoggedIn() {
    if (!isset($_SESSION)) session_start();
    return isset($_SESSION[ADMIN_SESSION_KEY]) && isset($_SESSION['admin_user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function getAdminUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, full_name, role, is_active, last_login, created_at, updated_at FROM admin_users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch();
}

