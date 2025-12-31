<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$db = getDB();

$stmt = $db->query("SELECT c.*, p.name as package_name FROM customers c LEFT JOIN packages p ON c.package_id = p.id ORDER BY c.submitted_at DESC");
$customers = $stmt->fetchAll();

$filename = 'hajj_customers_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Add BOM for UTF-8
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, [
    'Form ID', 'Title', 'First Name', 'Middle Name 1', 'Middle Name 2', 'Middle Name 3', 'Last Name', 
    'Date of Birth', 'Place of Birth', 'Email', 'Mobile', 
    'Address', 'Town', 'District/County/Province', 'Postcode', 'Country', 'Package', 'Room Type',
    'Passport Number', 'Passport Country of Issue', 'Passport Issue Date', 'Passport Expiry Date',
    'Emergency Name', 'Emergency Address', 'Emergency Country', 'Emergency Contact', 'Emergency Relationship',
    'Travel Companions', 'Roommates', 'Status', 'Notes', 'Submitted Date', 'Booking Agent'
]);

// Data rows
foreach ($customers as $customer) {
    $companions = json_decode($customer['travel_companions'], true) ?: [];
    $roommates = json_decode($customer['roommates'], true) ?: [];
    
    fputcsv($output, [
        $customer['form_id'],
        $customer['title'],
        $customer['first_name'],
        $customer['middle_name1'] ?? '',
        $customer['middle_name2'] ?? '',
        $customer['middle_name3'] ?? '',
        $customer['last_name'],
        $customer['date_of_birth'],
        $customer['place_of_birth'],
        $customer['email'],
        $customer['mobile'],
        $customer['address'],
        $customer['town'],
        $customer['district'] ?? '',
        $customer['postcode'],
        $customer['country'] ?? '',
        $customer['package_name'],
        $customer['room_type'],
        $customer['passport_number'],
        $customer['passport_country_of_issue'] ?? $customer['passport_type'] ?? '',
        $customer['passport_issue_date'],
        $customer['passport_expiry_date'],
        $customer['emergency_name'],
        $customer['emergency_address'],
        $customer['emergency_country'] ?? '',
        $customer['emergency_contact'],
        $customer['emergency_relationship'],
        implode('; ', $companions),
        implode('; ', $roommates),
        $customer['status'],
        $customer['notes'] ?? '',
        $customer['submitted_at'],
        $customer['booking_agent']
    ]);
}

fclose($output);
exit;

