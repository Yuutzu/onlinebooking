<?php
// Set timezone to Philippines (UTC+8)
date_default_timezone_set('Asia/Manila');

// Load environment variables
function loadEnv($filePath = __DIR__ . '/../../.env')
{
    if (!file_exists($filePath)) {
        throw new Exception('.env file not found');
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
        }
    }
}

loadEnv();

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
