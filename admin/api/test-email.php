<?php
// Start output buffering to prevent any output before headers
ob_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/mailer.php';

// Check login without redirecting (for API)
if (!isLoggedIn()) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Clear any output
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get email from POST data
    $email = $_POST['email'] ?? '';
    
    // Also try getting from JSON body if POST data is empty
    if (empty($email)) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $email = $data['email'] ?? '';
    }
    
    $db = getDB();
    $settings = getSettings($db);
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    $subject = "Test Email from " . ($settings['app_name'] ?? 'Hajj Registration CRM');
    $body = "<h2>Test Email</h2><p>This is a test email from your Hajj Registration CRM system.</p><p>If you received this email, your SMTP configuration is working correctly.</p>";
    
    try {
        if (sendEmail($email, $subject, $body, true)) {
            echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
        } else {
            $error = getMailerError();
            $errorMsg = !empty($error) ? htmlspecialchars($error) : 'Failed to send test email. Check your SMTP settings and ensure SMTP is enabled.';
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . htmlspecialchars($e->getMessage())]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
}
