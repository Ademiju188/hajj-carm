<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID is required']);
    exit;
}

try {
    $db = getDB();
    
    // Get file paths before deleting record
    $stmt = $db->prepare("SELECT passport_picture, passport_document FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Customer not found']);
        exit;
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    // Delete record
    $deleteStmt = $db->prepare("DELETE FROM customers WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    $db->commit();
    
    // If database delete successful, delete files
    // Note: UPLOAD_DIR is defined in config/database.php as __DIR__ . '/../uploads/' relative to config dir
    // But since this file is in admin/api, we might need to be careful.
    // However, UPLOAD_DIR is an absolute path usually if defined with __DIR__.
    // Let's verify UPLOAD_DIR usage.
    
    // Helper function to delete file
    function deleteFileIfExists($relativePath) {
        if (empty($relativePath)) return;
        
        // Remove 'uploads/' prefix if present in the database path, as UPLOAD_DIR points to uploads folder
        // Actually, submit-registration.php stores it as '$subfolder . '/' . $filename'
        // And UPLOAD_DIR is '.../uploads/'
        // So absolute path is UPLOAD_DIR . $relativePath
        
        $absolutePath = UPLOAD_DIR . $relativePath;
        
        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }
    }
    
    deleteFileIfExists($customer['passport_picture']);
    deleteFileIfExists($customer['passport_document']);
    
    echo json_encode(['success' => true, 'message' => 'Record and files deleted successfully']);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    error_log($e->getMessage());
}
