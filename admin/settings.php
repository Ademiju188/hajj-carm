<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$db = getDB();
$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8); // Remove 'setting_' prefix
                
                // Don't update password if it's empty (keep existing)
                if ($settingKey === 'smtp_password' && empty($value)) {
                    continue;
                }
                
                $value = is_array($value) ? json_encode($value) : trim($value);
                
                $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$value, $settingKey]);
            }
        }
        
        $db->commit();
        $success = 'Settings updated successfully';
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error updating settings: ' . $e->getMessage();
    }
}

// Get all settings
$stmt = $db->query("SELECT * FROM settings ORDER BY category, setting_key");
$settingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize settings by category
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['category']][$setting['setting_key']] = $setting;
}

$pageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Settings /</span> Application & SMTP Configuration
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
    
    <form method="POST">
        <!-- Application Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="iconify" data-icon="mdi:application"></i> Application Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Application Name</label>
                        <input type="text" class="form-control" name="setting_app_name" 
                               value="<?php echo htmlspecialchars($settings['app']['app_name']['setting_value'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Application URL</label>
                        <input type="text" class="form-control" name="setting_app_url" 
                               value="<?php echo htmlspecialchars($settings['app']['app_url']['setting_value'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Timezone</label>
                        <select class="form-select" name="setting_app_timezone">
                            <option value="Europe/London" <?php echo ($settings['app']['app_timezone']['setting_value'] ?? '') == 'Europe/London' ? 'selected' : ''; ?>>Europe/London</option>
                            <option value="UTC" <?php echo ($settings['app']['app_timezone']['setting_value'] ?? '') == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            <option value="America/New_York" <?php echo ($settings['app']['app_timezone']['setting_value'] ?? '') == 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
                            <option value="Asia/Dubai" <?php echo ($settings['app']['app_timezone']['setting_value'] ?? '') == 'Asia/Dubai' ? 'selected' : ''; ?>>Asia/Dubai</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Environment</label>
                        <select class="form-select" name="setting_app_env">
                            <option value="development" <?php echo ($settings['app']['app_env']['setting_value'] ?? '') == 'development' ? 'selected' : ''; ?>>Development</option>
                            <option value="production" <?php echo ($settings['app']['app_env']['setting_value'] ?? '') == 'production' ? 'selected' : ''; ?>>Production</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- SMTP Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="iconify" data-icon="mdi:email"></i> SMTP Email Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="setting_smtp_enabled" value="1" 
                               id="smtp_enabled" <?php echo ($settings['smtp']['smtp_enabled']['setting_value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="smtp_enabled">
                            Enable SMTP Email
                        </label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" name="setting_smtp_host" 
                               value="<?php echo htmlspecialchars($settings['smtp']['smtp_host']['setting_value'] ?? 'smtp.gmail.com'); ?>" 
                               placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" class="form-control" name="setting_smtp_port" 
                               value="<?php echo htmlspecialchars($settings['smtp']['smtp_port']['setting_value'] ?? '587'); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Security</label>
                        <select class="form-select" name="setting_smtp_secure">
                            <option value="tls" <?php echo ($settings['smtp']['smtp_secure']['setting_value'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($settings['smtp']['smtp_secure']['setting_value'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Username / Email</label>
                        <input type="text" class="form-control" name="setting_smtp_username" 
                               value="<?php echo htmlspecialchars($settings['smtp']['smtp_username']['setting_value'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" name="setting_smtp_password" 
                               value="" 
                               placeholder="Leave blank to keep current password" autocomplete="new-password">
                        <?php if (!empty($settings['smtp']['smtp_password']['setting_value'])): ?>
                            <small class="text-muted">Password is set. Leave blank to keep current password.</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Email</label>
                        <input type="email" class="form-control" name="setting_smtp_from_email" 
                               value="<?php echo htmlspecialchars($settings['smtp']['smtp_from_email']['setting_value'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" class="form-control" name="setting_smtp_from_name" 
                               value="<?php echo htmlspecialchars($settings['smtp']['smtp_from_name']['setting_value'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <strong>Gmail Users:</strong> You need to use an App Password, not your regular password. 
                    <a href="https://support.google.com/accounts/answer/185833" target="_blank">Learn how to generate one</a>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">Test Email</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="test_email" placeholder="Enter email to send test">
                        <button type="button" class="btn btn-secondary" onclick="sendTestEmail()">
                            <i class="iconify" data-icon="mdi:send"></i> Send Test Email
                        </button>
                    </div>
                    <div id="test_email_result" class="mt-2"></div>
                </div>
            </div>
        </div>
        
        <!-- Notification Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="iconify" data-icon="mdi:bell"></i> Notification Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notification Email Address</label>
                        <input type="email" class="form-control" name="setting_notification_email" 
                               value="<?php echo htmlspecialchars($settings['notification']['notification_email']['setting_value'] ?? ''); ?>">
                        <small class="text-muted">Email address to receive admin notifications for new registrations</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="iconify" data-icon="mdi:content-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<script>
function sendTestEmail() {
    const email = document.getElementById('test_email').value;
    const resultDiv = document.getElementById('test_email_result');
    
    if (!email || !email.includes('@')) {
        resultDiv.innerHTML = '<div class="alert alert-danger">Please enter a valid email address</div>';
        return;
    }
    
    resultDiv.innerHTML = '<div class="alert alert-info">Sending test email...</div>';
    
    fetch('api/test-email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="iconify" data-icon="mdi:check-circle"></i> ' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="iconify" data-icon="mdi:alert-circle"></i> ' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="iconify" data-icon="mdi:alert-circle"></i> Error: ' + error.message + '</div>';
    });
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

