<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = str_replace('/admin', '', $scriptPath);
$baseUrl = $protocol . '://' . $host . $basePath;

if (isLoggedIn()) {
    header('Location: ' . $baseUrl . '/admin/dashboard');
    exit;
} else {
    header('Location: ' . $baseUrl . '/admin/login');
    exit;
}
