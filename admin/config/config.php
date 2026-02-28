<?php
// Set timezone to Philippines (UTC+8)
date_default_timezone_set('Asia/Manila');

// Load environment variables from env.php
require_once __DIR__ . '/env.php';

// Load environment variables if not already loaded
if (!function_exists('env')) {
    throw new Exception('Environment loader not initialized');
}

// Call loadEnv to populate $_ENV if not already done
if (empty($_ENV['DB_HOST'])) {
    loadEnv();
}

// Get database credentials from environment variables
$dbuser = $_ENV['DB_USER'] ?? 'root';
$dbpass = $_ENV['DB_PASS'] ?? '';
$host = $_ENV['DB_HOST'] ?? 'localhost:3306';
$db = $_ENV['DB_NAME'] ?? 'hotel';

// Create database connection
$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

// Check connection
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Set character set
$mysqli->set_charset('utf8mb4');
