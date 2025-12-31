<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hajj_registration_crm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration (defaults - can be overridden by database settings)
define('APP_NAME_DEFAULT', 'Hajj Registration CRM');
define('APP_URL_DEFAULT', 'http://localhost/mini-crm');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('ADMIN_SESSION_KEY', 'hajj_admin_session');

// Load settings from database if available
function loadSettingsFromDB() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Set timezone
        $timezone = $settings['app_timezone'] ?? 'Europe/London';
        date_default_timezone_set($timezone);
        
        return $settings;
    } catch (Exception $e) {
        // If database not available, use defaults
        date_default_timezone_set('Europe/London');
        return [];
    }
}

// Load settings
$GLOBALS['app_settings'] = loadSettingsFromDB();

// Helper function to get setting
function getSetting($key, $default = '') {
    return $GLOBALS['app_settings'][$key] ?? $default;
}

// Application constants (use settings if available)
define('APP_NAME', getSetting('app_name', APP_NAME_DEFAULT));
define('APP_URL', getSetting('app_url', APP_URL_DEFAULT));
define('UPLOAD_URL', APP_URL . '/uploads/');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection Class
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}

