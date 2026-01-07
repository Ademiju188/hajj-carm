<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $db = getDB();
    
    // Validate required fields
    $required = ['agent', 'title', 'firstName', 'lastName', 'dob', 'pob', 'email', 'mobile', 
                 'address', 'town', 'district', 'postcode', 'country', 'emergencyName', 'emergencyAddress', 
                 'emergencyContact', 'emergencyCountry', 'emergencyRelationship', 'passportNumber', 'issueDate', 
                 'expiryDate', 'passportCountryOfIssue', 'package', 'roomSize'];
    
    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    
    // Validate file uploads
    if (empty($_FILES['passportPicture']['name'])) {
        $errors[] = 'Passport picture is required';
    }
    
    if (empty($_FILES['passportDocument']['name'])) {
        $errors[] = 'Passport document is required';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['error' => implode(', ', $errors)]);
        exit;
    }
    
    // Handle file uploads with specific size limits
    // Passport picture: Unrestricted (limit set high to 50MB) (was 18KB)
    // Passport document: Unrestricted (limit set high to 50MB) (was 1MB)
    $passportPictureTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $passportDocumentTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    
    $passportPicture = uploadFile($_FILES['passportPicture'], 'passport_pictures', 50 * 1024 * 1024, $passportPictureTypes); 
    $passportDocument = uploadFile($_FILES['passportDocument'], 'passport_documents', 50 * 1024 * 1024, $passportDocumentTypes);
    
    if (!$passportPicture || !$passportDocument) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload failed']);
        exit;
    }
    
    // Get package ID
    $packageStmt = $db->prepare("SELECT id FROM packages WHERE name = ?");
    $packageStmt->execute([$_POST['package']]);
    $package = $packageStmt->fetch();
    
    if (!$package) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid package selected']);
        exit;
    }
    
    // Collect travel companions
    $companions = [];
    for ($i = 1; $i <= 10; $i++) {
        if (!empty($_POST["companion$i"])) {
            $companions[] = $_POST["companion$i"];
        }
    }
    
    // Collect roommates
    $roommates = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["roommate$i"])) {
            $roommates[] = $_POST["roommate$i"];
        }
    }
    
    // Generate form ID
    $formId = 'HAJJ-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Insert customer data
    $passportCountryOfIssue = $_POST['passportCountryOfIssue'] ?? null;
    
    $stmt = $db->prepare("
        INSERT INTO customers (
            form_id, booking_agent, title, first_name, middle_name1, middle_name2, middle_name3,
            last_name, date_of_birth, place_of_birth, email, mobile, address, town, district, postcode, country,
            emergency_name, emergency_address, emergency_country, emergency_contact, emergency_relationship,
            passport_number, passport_issue_date, passport_expiry_date, passport_type, passport_country_of_issue,
            passport_picture, passport_document, package_id, room_type, travel_companions, roommates
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");
    
    $stmt->execute([
        $formId,
        $_POST['agent'],
        $_POST['title'],
        $_POST['firstName'],
        $_POST['middleName1'] ?? null,
        $_POST['middleName2'] ?? null,
        $_POST['middleName3'] ?? null,
        $_POST['lastName'],
        $_POST['dob'],
        $_POST['pob'],
        $_POST['email'],
        $_POST['mobile'],
        $_POST['address'],
        $_POST['town'],
        $_POST['district'],
        $_POST['postcode'],
        $_POST['country'],
        $_POST['emergencyName'],
        $_POST['emergencyAddress'],
        $_POST['emergencyCountry'],
        $_POST['emergencyContact'],
        $_POST['emergencyRelationship'],
        $_POST['passportNumber'],
        $_POST['issueDate'],
        $_POST['expiryDate'],
        $passportCountryOfIssue, // passport_type (for backward compatibility)
        $passportCountryOfIssue, // passport_country_of_issue (new field)
        $passportPicture,
        $passportDocument,
        $package['id'],
        $_POST['roomSize'],
        json_encode($companions),
        json_encode($roommates)
    ]);
    
    $customerId = $db->lastInsertId();
    
    // Send confirmation email
    sendRegistrationEmail($_POST['email'], [
        'form_id' => $formId,
        'name' => $_POST['title'] . ' ' . $_POST['firstName'] . ' ' . $_POST['lastName']
    ]);
    
    // Send notification email to admin
    sendAdminNotification($customerId, $formId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration submitted successfully! You will receive a confirmation email shortly.',
        'form_id' => $formId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    error_log($e->getMessage());
}

