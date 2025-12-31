<?php
$pageTitle = 'Customers';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Search and filter
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$package = $_GET['package'] ?? '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.mobile LIKE ? OR c.form_id LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($status)) {
    $where[] = "c.status = ?";
    $params[] = $status;
}

if (!empty($package)) {
    $where[] = "c.package_id = ?";
    $params[] = $package;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get customers
$sql = "SELECT c.*, p.name as package_name 
        FROM customers c 
        LEFT JOIN packages p ON c.package_id = p.id 
        $whereClause
        ORDER BY c.submitted_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Get packages for filter
$stmt = $db->query("SELECT id, name FROM packages ORDER BY name");
$packages = $stmt->fetchAll();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Customers /</span> All Customers
            </h4>
        </div>
    </div>
    
    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Email, Mobile, Form ID">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Package</label>
                    <select class="form-select" name="package">
                        <option value="">All Packages</option>
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?php echo $pkg['id']; ?>" <?php echo $package == $pkg['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pkg['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="iconify" data-icon="mdi:magnify"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Customers Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customers List (<?php echo count($customers); ?>)</h5>
            <a href="export-customers.php" class="btn btn-sm btn-success">
                <i class="iconify" data-icon="mdi:file-excel"></i> Export Excel
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="customersTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Form ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Package</th>
                            <th>Room Type</th>
                            <th>City</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($customer['form_id']); ?></strong></td>
                                    <td>
                                        <?php 
                                        $fullName = $customer['title'] . ' ' . $customer['first_name'];
                                        if ($customer['middle_name1']) $fullName .= ' ' . $customer['middle_name1'];
                                        if ($customer['middle_name2']) $fullName .= ' ' . $customer['middle_name2'];
                                        if ($customer['middle_name3']) $fullName .= ' ' . $customer['middle_name3'];
                                        $fullName .= ' ' . $customer['last_name'];
                                        echo htmlspecialchars($fullName);
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['package_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['room_type']); ?></td>
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

<script>
$(document).ready(function() {
    $('#customersTable').DataTable({
        order: [[8, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
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

