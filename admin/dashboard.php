<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Get statistics
$stats = [];

// Total customers
$stmt = $db->query("SELECT COUNT(*) as total FROM customers");
$stats['total_customers'] = $stmt->fetch()['total'];

// Pending registrations
$stmt = $db->query("SELECT COUNT(*) as total FROM customers WHERE status = 'pending'");
$stats['pending'] = $stmt->fetch()['total'];

// Approved registrations
$stmt = $db->query("SELECT COUNT(*) as total FROM customers WHERE status = 'approved'");
$stats['approved'] = $stmt->fetch()['total'];

// Today's registrations
$stmt = $db->query("SELECT COUNT(*) as total FROM customers WHERE DATE(submitted_at) = CURDATE()");
$stats['today'] = $stmt->fetch()['total'];

// Recent registrations
$stmt = $db->query("SELECT c.*, p.name as package_name FROM customers c LEFT JOIN packages p ON c.package_id = p.id ORDER BY c.submitted_at DESC LIMIT 10");
$recent = $stmt->fetchAll();

// Package distribution
$stmt = $db->query("SELECT p.name, COUNT(c.id) as count FROM packages p LEFT JOIN customers c ON p.id = c.package_id GROUP BY p.id, p.name");
$packageStats = $stmt->fetchAll();

// Status distribution for chart
$stmt = $db->query("SELECT status, COUNT(*) as count FROM customers GROUP BY status");
$statusStats = $stmt->fetchAll();

// Monthly registrations for last 6 months
$stmt = $db->query("SELECT DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as count FROM customers WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(submitted_at, '%Y-%m') ORDER BY month");
$monthlyStats = $stmt->fetchAll();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Overview</h4>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="fas fa-users" style="font-size: 32px; color: #1a5f7a;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Customers</span>
                    <h3 class="card-title mb-2"><?php echo $stats['total_customers']; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="far fa-clock" style="font-size: 32px; color: #ff9800;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Pending</span>
                    <h3 class="card-title mb-2"><?php echo $stats['pending']; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="fas fa-check-circle" style="font-size: 32px; color: #28a745;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Approved</span>
                    <h3 class="card-title mb-2"><?php echo $stats['approved']; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="fas fa-calendar-day" style="font-size: 32px; color: #2d8b9c;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Today's Registrations</span>
                    <h3 class="card-title mb-2"><?php echo $stats['today']; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Status Distribution</h5>
                </div>
                <div class="card-body" style="height: 300px; position: relative;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Package Distribution</h5>
                </div>
                <div class="card-body" style="height: 300px; position: relative;">
                    <canvas id="packageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- All Customers Information -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Customer Registrations</h5>
                    <a href="export-customers.php" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    // Get all customers with full details
                    $stmt = $db->query("SELECT c.*, p.name as package_name FROM customers c LEFT JOIN packages p ON c.package_id = p.id ORDER BY c.submitted_at DESC");
                    $allCustomers = $stmt->fetchAll();
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="allCustomersTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Form ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Package</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allCustomers as $customer): 
                                    $fullName = $customer['title'] . ' ' . $customer['first_name'];
                                    if ($customer['middle_name1']) $fullName .= ' ' . $customer['middle_name1'];
                                    if ($customer['middle_name2']) $fullName .= ' ' . $customer['middle_name2'];
                                    if ($customer['middle_name3']) $fullName .= ' ' . $customer['middle_name3'];
                                    $fullName .= ' ' . $customer['last_name'];
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($customer['form_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($fullName); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['package_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $customer['status'] == 'approved' ? 'success' : 
                                                    ($customer['status'] == 'pending' ? 'warning' : 
                                                    ($customer['status'] == 'rejected' ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($customer['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($customer['submitted_at']); ?></td>
                                        <td>
                                            <a href="customer-view.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info" title="View Form">
                                                <i class="fas fa-file-alt"></i>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart (Doughnut)
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = {
            labels: [<?php 
                $statusLabels = [];
                $statusCounts = [];
                foreach ($statusStats as $stat) {
                    $statusLabels[] = "'" . ucfirst($stat['status']) . "'";
                    $statusCounts[] = $stat['count'];
                }
                echo implode(', ', $statusLabels);
            ?>],
            datasets: [{
                data: [<?php echo implode(', ', $statusCounts); ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',   // Approved - Green
                    'rgba(255, 152, 0, 0.8)',   // Pending - Orange
                    'rgba(220, 53, 69, 0.8)',   // Rejected - Red
                    'rgba(23, 162, 184, 0.8)'   // Other - Blue
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 152, 0, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(23, 162, 184, 1)'
                ],
                borderWidth: 2
            }]
        };
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: statusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed;
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Package Distribution Chart (Bar)
    const packageCtx = document.getElementById('packageChart');
    if (packageCtx) {
        const packageData = {
            labels: [<?php 
                $packageLabels = [];
                $packageCounts = [];
                foreach ($packageStats as $stat) {
                    $packageLabels[] = "'" . htmlspecialchars($stat['name'], ENT_QUOTES) . "'";
                    $packageCounts[] = $stat['count'];
                }
                echo implode(', ', $packageLabels);
            ?>],
            datasets: [{
                label: 'Number of Customers',
                data: [<?php echo implode(', ', $packageCounts); ?>],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(118, 75, 162, 0.8)',
                    'rgba(240, 147, 251, 0.8)',
                    'rgba(79, 172, 254, 0.8)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(118, 75, 162, 1)',
                    'rgba(240, 147, 251, 1)',
                    'rgba(79, 172, 254, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        };
        
        new Chart(packageCtx, {
            type: 'bar',
            data: packageData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' customers';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});

// DataTable initialization
$(document).ready(function() {
    $('#allCustomersTable').DataTable({
        order: [[6, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        scrollX: false,
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

