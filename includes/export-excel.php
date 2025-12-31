<?php
function exportCustomersToExcel($db) {
    $stmt = $db->query("SELECT c.*, p.name as package_name FROM customers c LEFT JOIN packages p ON c.package_id = p.id ORDER BY c.submitted_at DESC");
    $customers = $stmt->fetchAll();
    
    $filename = 'hajj_customers_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, [
        'Form ID', 'Title', 'First Name', 'Middle Names', 'Last Name', 
        'Date of Birth', 'Place of Birth', 'Email', 'Mobile', 
        'Address', 'Town', 'Postcode', 'Package', 'Room Type',
        'Passport Number', 'Passport Type', 'Issue Date', 'Expiry Date',
        'Travel Companions', 'Roommates', 'Status', 'Submitted Date'
    ]);
    
    // Data rows
    foreach ($customers as $customer) {
        $middleNames = trim(($customer['middle_name1'] ?? '') . ' ' . 
                         ($customer['middle_name2'] ?? '') . ' ' . 
                         ($customer['middle_name3'] ?? ''));
        
        $companions = json_decode($customer['travel_companions'], true) ?: [];
        $roommates = json_decode($customer['roommates'], true) ?: [];
        
        fputcsv($output, [
            $customer['form_id'],
            $customer['title'],
            $customer['first_name'],
            $middleNames,
            $customer['last_name'],
            $customer['date_of_birth'],
            $customer['place_of_birth'],
            $customer['email'],
            $customer['mobile'],
            $customer['address'],
            $customer['town'],
            $customer['postcode'],
            $customer['package_name'],
            $customer['room_type'],
            $customer['passport_number'],
            $customer['passport_type'],
            $customer['passport_issue_date'],
            $customer['passport_expiry_date'],
            implode('; ', $companions),
            implode('; ', $roommates),
            $customer['status'],
            $customer['submitted_at']
        ]);
    }
    
    fclose($output);
    exit;
}

