<?php
$pageTitle = 'Custom Reports';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Available fields for reports
$availableFields = [
    'name' => 'Full Name',
    'town' => 'Town/City',
    'contact_number' => 'Contact Number (Mobile)',
    'email' => 'Email',
    'address' => 'Address',
    'district' => 'District/County/Province',
    'country' => 'Country',
    'agent' => 'Booking Agent',
    'package' => 'Package',
    'room_type' => 'Room Type',
    'traveling_with' => 'Travel Companions',
    'roommates' => 'Room Sharing With',
    'status' => 'Status',
    'form_id' => 'Form ID',
    'dob' => 'Date of Birth',
    'place_of_birth' => 'Place of Birth'
];

// Handle report generation
$selectedFields = $_GET['fields'] ?? [];
$reportData = [];

if (!empty($selectedFields) && is_array($selectedFields)) {
    // Validate selected fields
    $validFields = array_intersect($selectedFields, array_keys($availableFields));
    
    if (!empty($validFields)) {
        // Build query
        // Base expressions without aliases; aliases are added dynamically below
        $fieldMappings = [
            'name' => "CONCAT(c.title, ' ', c.first_name, IFNULL(CONCAT(' ', c.middle_name1), ''), IFNULL(CONCAT(' ', c.middle_name2), ''), IFNULL(CONCAT(' ', c.middle_name3), ''), ' ', c.last_name)",
            'town' => 'c.town',
            'contact_number' => 'c.mobile',
            'email' => 'c.email',
            'address' => 'c.address',
            'district' => 'c.district',
            'country' => 'c.country',
            'agent' => 'c.booking_agent',
            'package' => 'p.name',
            'room_type' => 'c.room_type',
            'traveling_with' => 'c.travel_companions',
            'roommates' => 'c.roommates',
            'status' => 'c.status',
            'form_id' => 'c.form_id',
            'dob' => 'c.date_of_birth',
            'place_of_birth' => 'c.place_of_birth'
        ];
        
        $selectFields = [];
        foreach ($validFields as $field) {
            $selectFields[] = $fieldMappings[$field] . ' as ' . $field;
        }
        
        $query = "SELECT " . implode(', ', $selectFields) . " 
                  FROM customers c 
                  LEFT JOIN packages p ON c.package_id = p.id 
                  ORDER BY c.submitted_at DESC";
        
        $stmt = $db->query($query);
        $reportData = $stmt->fetchAll();
    }
}

// Handle export
if (isset($_GET['export']) && !empty($selectedFields) && !empty($reportData)) {
    $filename = 'custom_report_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Headers
    $headers = [];
    foreach ($selectedFields as $field) {
        if (isset($availableFields[$field])) {
            $headers[] = $availableFields[$field];
        }
    }
    fputcsv($output, $headers);
    
    // Data rows
    foreach ($reportData as $row) {
        $csvRow = [];
        foreach ($selectedFields as $field) {
            if ($field === 'traveling_with' || $field === 'roommates') {
                $data = json_decode($row[$field], true) ?: [];
                $csvRow[] = implode('; ', $data);
            } else {
                $csvRow[] = $row[$field] ?? '';
            }
        }
        fputcsv($output, $csvRow);
    }
    
    fclose($output);
    exit;
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Reports /</span> Custom Reports
            </h4>
        </div>
    </div>
    
    <div class="row">
        <!-- Field Selection -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:filter"></i> Select Fields</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="reportForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Available Fields:</label>
                            <div class="border rounded p-3" style="max-height: 500px; overflow-y: auto;">
                                <?php foreach ($availableFields as $key => $label): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="fields[]" 
                                               value="<?php echo $key; ?>" 
                                               id="field_<?php echo $key; ?>"
                                               <?php echo in_array($key, $selectedFields) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="field_<?php echo $key; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="iconify" data-icon="mdi:refresh"></i> Generate Report
                        </button>
                        <?php if (!empty($reportData)): ?>
                            <a href="?<?php echo http_build_query(['fields' => $selectedFields, 'export' => 1]); ?>" 
                               class="btn btn-success w-100">
                                <i class="iconify" data-icon="mdi:file-excel"></i> Export to Excel
                            </a>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Quick Presets -->
                    <div class="mt-4">
                        <label class="form-label fw-bold">Quick Presets:</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPreset(['name', 'town', 'contact_number', 'traveling_with', 'roommates', 'agent'])">
                                Name, Town, Contact, Traveling With, Roommates, Agent
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPreset(['name', 'town', 'agent', 'contact_number'])">
                                Name, Town, Agent, Contact Number
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPreset(['name', 'address', 'email', 'contact_number', 'agent'])">
                                Name, Address, Email, Mobile, Agent
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPreset(['name', 'town', 'country', 'package', 'room_type'])">
                                Name, Town, Country, Package, Room Type
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Report Display -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:file-document"></i> Report Results</h5>
                    <?php if (!empty($reportData)): ?>
                        <span class="badge bg-primary"><?php echo count($reportData); ?> records</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($selectedFields)): ?>
                        <div class="alert alert-info">
                            <i class="iconify" data-icon="mdi:information"></i> Please select fields from the left panel to generate a report.
                        </div>
                    <?php elseif (empty($reportData)): ?>
                        <div class="alert alert-warning">
                            <i class="iconify" data-icon="mdi:alert"></i> No data found matching your criteria.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="reportTable">
                                <thead>
                                    <tr>
                                        <?php foreach ($selectedFields as $field): ?>
                                            <?php if (isset($availableFields[$field])): ?>
                                                <th><?php echo htmlspecialchars($availableFields[$field]); ?></th>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <?php foreach ($selectedFields as $field): ?>
                                                <td>
                                                    <?php 
                                                    if ($field === 'traveling_with' || $field === 'roommates') {
                                                        $data = json_decode($row[$field], true) ?: [];
                                                        echo !empty($data) ? htmlspecialchars(implode(', ', $data)) : 'None';
                                                    } elseif ($field === 'dob') {
                                                        echo formatDate($row[$field]);
                                                    } else {
                                                        echo htmlspecialchars($row[$field] ?? '');
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setPreset(fields) {
    // Uncheck all checkboxes
    document.querySelectorAll('input[name="fields[]"]').forEach(cb => cb.checked = false);
    
    // Check selected fields
    fields.forEach(field => {
        const checkbox = document.getElementById('field_' + field);
        if (checkbox) checkbox.checked = true;
    });
    
    // Submit form
    document.getElementById('reportForm').submit();
}

<?php if (!empty($reportData)): ?>
$(document).ready(function() {
    $('#reportTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        scrollX: true,
        scrollCollapse: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>




