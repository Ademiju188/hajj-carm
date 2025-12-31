<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$db = getDB();
$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT c.*, p.name as package_name FROM customers c LEFT JOIN packages p ON c.package_id = p.id WHERE c.id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: customers.php');
    exit;
}

// Parse JSON fields
$travelCompanions = json_decode($customer['travel_companions'], true) ?: [];
$roommates = json_decode($customer['roommates'], true) ?: [];

// Extract mobile number and dialing code (stored as "+44 1234 567890")
$mobileParts = explode(' ', $customer['mobile'], 2);
$mobileCountryCode = !empty($mobileParts[0]) ? $mobileParts[0] : '';
$mobileNumber = !empty($mobileParts[1]) ? $mobileParts[1] : $customer['mobile'];

$emergencyParts = explode(' ', $customer['emergency_contact'], 2);
$emergencyCountryCode = !empty($emergencyParts[0]) ? $emergencyParts[0] : '';
$emergencyContactNumber = !empty($emergencyParts[1]) ? $emergencyParts[1] : $customer['emergency_contact'];

// Load countries JSON
$countriesJsonPath = __DIR__ . '/../template/assets/vendor/country.json';
$countries = [];
if (file_exists($countriesJsonPath)) {
    $countriesData = json_decode(file_get_contents($countriesJsonPath), true);
    if ($countriesData) {
        usort($countriesData, function($a, $b) {
            if ($a['name'] === 'United Kingdom') return -1;
            if ($b['name'] === 'United Kingdom') return 1;
            return strcmp($a['name'], $b['name']);
        });
        $countries = $countriesData;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration Form - <?php echo htmlspecialchars($customer['form_id']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php 
        // Include CSS from index.html (lines 12-317)
        $cssStart = 12;
        $cssEnd = 317;
        $lines = file(__DIR__ . '/../index.html');
        for ($i = $cssStart - 1; $i < $cssEnd; $i++) {
            if (isset($lines[$i])) {
                echo $lines[$i];
            }
        }
        ?>
        input[readonly], select[disabled], textarea[readonly] {
            background-color: #f7fafc !important;
            cursor: not-allowed;
        }
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            color: #667eea;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .back-btn:hover {
            background: #f7fafc;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <a href="customers.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Customers
    </a>
    
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-kaaba"></i>
            </div>
            <h1>Hajj 2026 Registration Form</h1>
            <p class="subtitle">Awaisi Tours • Form ID: <?php echo htmlspecialchars($customer['form_id']); ?></p>
        </header>
        
        <div class="form-container">
            <?php include __DIR__ . '/customer-form-fields.inc.php'; ?>
        </div>
    </div>
</body>
</html>

