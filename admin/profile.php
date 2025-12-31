<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$db = getDB();
$success = '';
$error = '';

// Handle profile update BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = getAdminUser();
    if (isset($_POST['update_profile'])) {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        
        // Validate
        if (empty($fullName) || empty($email) || empty($username)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else {
            // Check if username or email already exists (excluding current user)
            $checkStmt = $db->prepare("SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?");
            $checkStmt->execute([$username, $email, $adminUser['id']]);
            if ($checkStmt->fetch()) {
                $error = 'Username or email already exists';
            } else {
                // Update profile
                $updateStmt = $db->prepare("UPDATE admin_users SET full_name = ?, email = ?, username = ? WHERE id = ?");
                $updateStmt->execute([$fullName, $email, $username, $adminUser['id']]);
                
                // Update session
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_name'] = $fullName;
                
                $success = 'Profile updated successfully';
                
                // Reload admin user data
                $adminUser = getAdminUser();
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Verify current password
            $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$adminUser['id']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($currentPassword, $user['password_hash'])) {
                // Update password
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $updateStmt->execute([$newHash, $adminUser['id']]);
                
                $success = 'Password changed successfully';
            } else {
                $error = 'Current password is incorrect';
            }
        }
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';

$adminUser = getAdminUser();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Account /</span> My Profile
    </h4>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="iconify" data-icon="mdi:check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <i class="iconify" data-icon="mdi:alert-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:account-edit"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($adminUser['full_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($adminUser['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($adminUser['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($adminUser['role']); ?>" disabled>
                            <small class="text-muted">Role cannot be changed</small>
                        </div>
                        
                        <?php if ($adminUser['last_login']): ?>
                            <div class="mb-3">
                                <label class="form-label">Last Login</label>
                                <input type="text" class="form-control" value="<?php echo formatDateTime($adminUser['last_login']); ?>" disabled>
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="iconify" data-icon="mdi:content-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:lock-reset"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="passwordForm">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" id="new_password" required minlength="6">
                            <small class="text-muted">Password must be at least 6 characters long</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required minlength="6">
                            <div class="invalid-feedback" id="password-match-error" style="display: none;">
                                Passwords do not match
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="iconify" data-icon="mdi:lock"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Account Information -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="iconify" data-icon="mdi:information"></i> Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Account Created:</th>
                                    <td><?php echo formatDateTime($adminUser['created_at'] ?? date('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td><?php echo formatDateTime($adminUser['updated_at'] ?? date('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th>Account Status:</th>
                                    <td>
                                        <?php if ($adminUser['is_active'] ?? 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const errorDiv = document.getElementById('password-match-error');
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        errorDiv.style.display = 'block';
        document.getElementById('confirm_password').classList.add('is-invalid');
    } else {
        errorDiv.style.display = 'none';
        document.getElementById('confirm_password').classList.remove('is-invalid');
    }
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    const errorDiv = document.getElementById('password-match-error');
    
    if (newPassword !== confirmPassword && confirmPassword.length > 0) {
        errorDiv.style.display = 'block';
        this.classList.add('is-invalid');
    } else {
        errorDiv.style.display = 'none';
        this.classList.remove('is-invalid');
    }
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

