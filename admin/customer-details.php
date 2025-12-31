<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$db = getDB();
$id = $_GET['id'] ?? 0;

// Handle status update BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (in_array($newStatus, ['pending', 'approved', 'rejected', 'completed'])) {
        $updateStmt = $db->prepare("UPDATE customers SET status = ?, notes = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $notes, $id]);
        header('Location: customer-details.php?id=' . $id);
        exit;
    }
}

$pageTitle = 'Customer Details';
require_once __DIR__ . '/includes/header.php';

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
?>

<style>
.customer-header-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 2rem;
    margin-bottom: 2rem;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}
.info-item {
    background: rgba(255,255,255,0.1);
    padding: 1rem;
    border-radius: 8px;
}
.info-item label {
    font-size: 0.85rem;
    opacity: 0.9;
    display: block;
    margin-bottom: 0.25rem;
}
.info-item .value {
    font-size: 1.1rem;
    font-weight: 600;
}
.profile-picture {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <a href="customers.php" class="btn btn-label-secondary mb-3">
                <i class="iconify" data-icon="mdi:arrow-left"></i> Back to Customers
            </a>
        </div>
    </div>
    
    <?php 
    // Build full name
    $fullName = $customer['title'] . ' ' . $customer['first_name'];
    if ($customer['middle_name1']) $fullName .= ' ' . $customer['middle_name1'];
    if ($customer['middle_name2']) $fullName .= ' ' . $customer['middle_name2'];
    if ($customer['middle_name3']) $fullName .= ' ' . $customer['middle_name3'];
    $fullName .= ' ' . $customer['last_name'];
    ?>
    
    <!-- Customer Header - At a Glance -->
    <div class="customer-header-card">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <?php if ($customer['passport_picture']): ?>
                    <img src="../../uploads/<?php echo htmlspecialchars($customer['passport_picture']); ?>" 
                         alt="Profile" class="profile-picture">
                <?php else: ?>
                    <div class="profile-picture bg-white d-flex align-items-center justify-content-center">
                        <i class="iconify" data-icon="mdi:account" style="font-size: 60px; color: #667eea;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-10">
                <h2 class="mb-2"><?php echo htmlspecialchars($fullName); ?></h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Form ID</label>
                        <div class="value"><?php echo htmlspecialchars($customer['form_id']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Status</label>
                        <div class="value">
                            <span class="badge bg-<?php 
                                echo $customer['status'] == 'approved' ? 'success' : 
                                    ($customer['status'] == 'pending' ? 'warning' : 
                                    ($customer['status'] == 'rejected' ? 'danger' : 'info')); 
                            ?> text-white">
                                <?php echo ucfirst($customer['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Booking Agent</label>
                        <div class="value"><?php echo htmlspecialchars($customer['booking_agent']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <div class="value">
                            <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" class="text-white">
                                <?php echo htmlspecialchars($customer['email']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Mobile</label>
                        <div class="value">
                            <a href="tel:<?php echo htmlspecialchars($customer['mobile']); ?>" class="text-white">
                                <?php echo htmlspecialchars($customer['mobile']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Town/City</label>
                        <div class="value"><?php echo htmlspecialchars($customer['town']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Country</label>
                        <div class="value"><?php echo htmlspecialchars($customer['country'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Package</label>
                        <div class="value"><?php echo htmlspecialchars($customer['package_name']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Left Column - Customer Info -->
        <div class="col-lg-8 mb-4">
            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:account"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Form ID:</strong><br>
                            <span class="text-primary"><?php echo htmlspecialchars($customer['form_id']); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Booking Agent:</strong><br>
                            <?php echo htmlspecialchars($customer['booking_agent']); ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Full Name:</strong><br>
                            <?php echo htmlspecialchars($fullName); ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date of Birth:</strong><br>
                            <?php echo formatDate($customer['date_of_birth']); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Place of Birth:</strong><br>
                            <?php echo htmlspecialchars($customer['place_of_birth']); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:map-marker"></i> Address</h5>
                </div>
                <div class="card-body">
                    <p>
                        <?php echo htmlspecialchars($customer['address']); ?><br>
                        <?php echo htmlspecialchars($customer['town']); ?><br>
                        <?php if (!empty($customer['district'])): ?>
                            <?php echo htmlspecialchars($customer['district']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($customer['postcode']); ?><br>
                        <?php if (!empty($customer['country'])): ?>
                            <?php echo htmlspecialchars($customer['country']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <!-- Passport Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:passport"></i> Passport Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Passport Number:</strong><br>
                            <?php echo htmlspecialchars($customer['passport_number']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Passport Country of Issue:</strong><br>
                            <?php echo htmlspecialchars($customer['passport_country_of_issue'] ?? $customer['passport_type'] ?? 'N/A'); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Issue Date:</strong><br>
                            <?php echo formatDate($customer['passport_issue_date']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Expiry Date:</strong><br>
                            <?php echo formatDate($customer['passport_expiry_date']); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Travel Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:airplane"></i> Travel & Accommodation</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Package:</strong><br>
                            <?php echo htmlspecialchars($customer['package_name']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Room Type:</strong><br>
                            <?php echo htmlspecialchars($customer['room_type']); ?>
                        </div>
                    </div>
                    <?php if (!empty($travelCompanions)): ?>
                        <div class="mb-3">
                            <strong>Travel Companions:</strong><br>
                            <ul>
                                <?php foreach ($travelCompanions as $companion): ?>
                                    <li><?php echo htmlspecialchars($companion); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($roommates)): ?>
                        <div class="mb-3">
                            <strong>Room Sharing With:</strong><br>
                            <ul>
                                <?php foreach ($roommates as $roommate): ?>
                                    <li><?php echo htmlspecialchars($roommate); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Emergency Contact -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:phone-alert"></i> Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Name:</strong><br>
                            <?php echo htmlspecialchars($customer['emergency_name']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Relationship:</strong><br>
                            <?php echo htmlspecialchars($customer['emergency_relationship']); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Contact:</strong><br>
                            <?php echo htmlspecialchars($customer['emergency_contact']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Country:</strong><br>
                            <?php echo htmlspecialchars($customer['emergency_country'] ?? 'N/A'); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($customer['emergency_address'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Documents & Actions -->
        <div class="col-lg-4 mb-4">
            <!-- Status Update -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:account-edit"></i> Update Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" <?php echo $customer['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $customer['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $customer['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="completed" <?php echo $customer['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($customer['notes'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                            <i class="iconify" data-icon="mdi:content-save"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Passport Picture -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:image"></i> Passport Picture</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($customer['passport_picture']): ?>
                        <img src="../../uploads/<?php echo htmlspecialchars($customer['passport_picture']); ?>" 
                             alt="Passport Picture" 
                             class="img-fluid rounded mb-2" 
                             style="max-height: 300px;">
                        <br>
                        <a href="../../uploads/<?php echo htmlspecialchars($customer['passport_picture']); ?>" 
                           download class="btn btn-sm btn-primary">
                            <i class="iconify" data-icon="mdi:download"></i> Download
                        </a>
                    <?php else: ?>
                        <p class="text-muted">No passport picture uploaded</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Passport Document -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:file-document"></i> Passport Document</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($customer['passport_document']): ?>
                        <a href="../../uploads/<?php echo htmlspecialchars($customer['passport_document']); ?>" 
                           download class="btn btn-primary w-100">
                            <i class="iconify" data-icon="mdi:file-download"></i> Download Document
                        </a>
                    <?php else: ?>
                        <p class="text-muted">No passport document uploaded</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Submission Info -->
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Submitted:</strong> <?php echo formatDateTime($customer['submitted_at']); ?><br>
                        <strong>Last Updated:</strong> <?php echo formatDateTime($customer['updated_at']); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

