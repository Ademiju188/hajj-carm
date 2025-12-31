<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/includes/header.php';

$db = getDB();


// Get statistics for reports
$cityStats = [];
$packageStats = [];
$statusStats = [];

// City distribution
$stmt = $db->query("SELECT town, COUNT(*) as count FROM customers GROUP BY town ORDER BY count DESC");
$cityStats = $stmt->fetchAll();

// Package distribution
$stmt = $db->query("SELECT p.name, COUNT(c.id) as count FROM packages p LEFT JOIN customers c ON p.id = c.package_id GROUP BY p.id, p.name ORDER BY count DESC");
$packageStats = $stmt->fetchAll();

// Status distribution
$stmt = $db->query("SELECT status, COUNT(*) as count FROM customers GROUP BY status");
$statusStats = $stmt->fetchAll();

// Room type distribution
$stmt = $db->query("SELECT room_type, COUNT(*) as count FROM customers GROUP BY room_type ORDER BY count DESC");
$roomTypeStats = $stmt->fetchAll();

// Country distribution (extract from place_of_birth)
$stmt = $db->query("SELECT place_of_birth, COUNT(*) as count FROM customers GROUP BY place_of_birth ORDER BY count DESC");
$placeOfBirthStats = $stmt->fetchAll();

// Booking agent distribution
$stmt = $db->query("SELECT booking_agent, COUNT(*) as count FROM customers GROUP BY booking_agent ORDER BY count DESC");
$agentStats = $stmt->fetchAll();

// Monthly registrations
$stmt = $db->query("SELECT DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as count FROM customers GROUP BY month ORDER BY month DESC LIMIT 12");
$monthlyStats = $stmt->fetchAll();

// Detailed customer report with all requested fields
$stmt = $db->query("
    SELECT 
        c.*, 
        p.name as package_name,
        c.travel_companions,
        c.roommates
    FROM customers c 
    LEFT JOIN packages p ON c.package_id = p.id 
    ORDER BY c.submitted_at DESC
");
$detailedCustomers = $stmt->fetchAll();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Reports /</span> Analytics & Statistics
            </h4>
            <a href="export-customers.php" class="btn btn-success">
                <i class="iconify" data-icon="mdi:file-excel"></i> Export All to Excel
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- City Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:map-marker"></i> Registrations by City</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>City</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalCustomers = array_sum(array_column($cityStats, 'count'));
                                foreach ($cityStats as $stat): 
                                    $percentage = $totalCustomers > 0 ? ($stat['count'] / $totalCustomers * 100) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['town']); ?></td>
                                        <td><strong><?php echo $stat['count']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Package Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:package-variant"></i> Package Selection</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Package</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalPackages = array_sum(array_column($packageStats, 'count'));
                                foreach ($packageStats as $stat): 
                                    $percentage = $totalPackages > 0 ? ($stat['count'] / $totalPackages * 100) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['name']); ?></td>
                                        <td><strong><?php echo $stat['count']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Status Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:chart-pie"></i> Status Distribution</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($statusStats as $stat): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span><?php echo ucfirst($stat['status']); ?></span>
                                <strong><?php echo $stat['count']; ?></strong>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?php 
                                    echo $stat['status'] == 'approved' ? 'success' : 
                                        ($stat['status'] == 'pending' ? 'warning' : 
                                        ($stat['status'] == 'rejected' ? 'danger' : 'info')); 
                                ?>" role="progressbar" style="width: <?php 
                                    $total = array_sum(array_column($statusStats, 'count'));
                                    echo $total > 0 ? ($stat['count'] / $total * 100) : 0; 
                                ?>%">
                                    <?php 
                                    $total = array_sum(array_column($statusStats, 'count'));
                                    echo $total > 0 ? number_format($stat['count'] / $total * 100, 1) : 0; 
                                    ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Room Type Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:bed"></i> Room Type Selection</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Room Type</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roomTypeStats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['room_type']); ?></td>
                                        <td><strong><?php echo $stat['count']; ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Country Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:earth"></i> Registrations by Country (Place of Birth)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Place of Birth</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalPlace = array_sum(array_column($placeOfBirthStats, 'count'));
                                foreach ($placeOfBirthStats as $stat): 
                                    $percentage = $totalPlace > 0 ? ($stat['count'] / $totalPlace * 100) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['place_of_birth']); ?></td>
                                        <td><strong><?php echo $stat['count']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Agent Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:account-tie"></i> Registrations by Booking Agent</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking Agent</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalAgents = array_sum(array_column($agentStats, 'count'));
                                foreach ($agentStats as $stat): 
                                    $percentage = $totalAgents > 0 ? ($stat['count'] / $totalAgents * 100) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['booking_agent']); ?></td>
                                        <td><strong><?php echo $stat['count']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Report with All Information -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:file-document-multiple"></i> Detailed Customer Report</h5>
                    <p class="text-muted mb-0 mt-2">Complete information: Country, Package, Room Type, Group Members, Roommates, Booking Agent</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="detailedReportTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Form ID</th>
                                    <th>Name</th>
                                    <th>Country (Place of Birth)</th>
                                    <th>Package</th>
                                    <th>Room Type</th>
                                    <th>Group Size</th>
                                    <th>Group Members</th>
                                    <th>Roommates</th>
                                    <th>Booking Agent</th>
                                    <th>City</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detailedCustomers as $customer): 
                                    $travelCompanions = json_decode($customer['travel_companions'], true) ?: [];
                                    $roommates = json_decode($customer['roommates'], true) ?: [];
                                    $fullName = $customer['title'] . ' ' . $customer['first_name'];
                                    if ($customer['middle_name1']) $fullName .= ' ' . $customer['middle_name1'];
                                    if ($customer['middle_name2']) $fullName .= ' ' . $customer['middle_name2'];
                                    if ($customer['middle_name3']) $fullName .= ' ' . $customer['middle_name3'];
                                    $fullName .= ' ' . $customer['last_name'];
                                    $groupSize = 1 + count($travelCompanions);
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($customer['form_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($fullName); ?></td>
                                        <td><?php echo htmlspecialchars($customer['place_of_birth']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['package_name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['room_type']); ?></td>
                                        <td><strong><?php echo $groupSize; ?> person(s)</strong></td>
                                        <td>
                                            <?php if (!empty($travelCompanions)): ?>
                                                <ul class="mb-0" style="padding-left: 20px;">
                                                    <?php foreach ($travelCompanions as $companion): ?>
                                                        <li><?php echo htmlspecialchars($companion); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($roommates)): ?>
                                                <ul class="mb-0" style="padding-left: 20px;">
                                                    <?php foreach ($roommates as $roommate): ?>
                                                        <li><?php echo htmlspecialchars($roommate); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($customer['booking_agent']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['town']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $customer['status'] == 'approved' ? 'success' : 
                                                    ($customer['status'] == 'pending' ? 'warning' : 
                                                    ($customer['status'] == 'rejected' ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($customer['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="customer-details.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="iconify" data-icon="mdi:eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Registrations -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:chart-line"></i> Monthly Registrations (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Registrations</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($monthlyStats)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No monthly registrations data available</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $maxCount = !empty($monthlyStats) ? max(array_column($monthlyStats, 'count')) : 0;
                                    foreach ($monthlyStats as $stat): 
                                        $percentage = $maxCount > 0 ? ($stat['count'] / $maxCount * 100) : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo date('F Y', strtotime($stat['month'] . '-01')); ?></td>
                                            <td><strong><?php echo $stat['count']; ?></strong></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                                        <?php echo $stat['count']; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#detailedReportTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        scrollX: true,
        scrollCollapse: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            emptyTable: "No customers found",
            zeroRecords: "No matching records found"
        }
    });
});
</script>

<?php
$extraScripts = [
    '../../template/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    '../../template/assets/js/tables-datatables-basic.js'
];
require_once __DIR__ . '/includes/footer.php';
?>

