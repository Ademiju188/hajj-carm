<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, password_hash, full_name, role FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION[ADMIN_SESSION_KEY] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            
            // Update last login
            $updateStmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!doctype html>
<html lang="en" class="layout-navbar-fixed layout-menu-fixed" dir="ltr" data-assets-path="../template/assets/" data-template="vertical-menu-template-no-customizer">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Admin Login - Hajj Registration CRM</title>
    <link rel="icon" type="image/x-icon" href="../template/assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../template/assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="../template/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../template/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../template/assets/css/demo.css" />
    <link rel="stylesheet" href="../template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <style>
        body { background: linear-gradient(135deg, #1a5f7a 0%, #2d8b9c 100%); }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: white; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 400px; width: 100%; padding: 40px; }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h1 { color: #1a5f7a; margin-bottom: 10px; }
        .login-header p { color: #666; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="iconify" data-icon="mdi:account-circle"></i> Admin Login</h1>
                <p>Hajj Registration CRM</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="iconify" data-icon="mdi:login"></i> Login
                </button>
            </form>
            
        </div>
    </div>
    
    <script src="../template/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../template/assets/vendor/libs/popper/popper.js"></script>
    <script src="../template/assets/vendor/js/bootstrap.js"></script>
    <script src="../template/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../template/assets/vendor/js/menu.js"></script>
    <script src="../template/assets/js/main.js"></script>
</body>
</html>

