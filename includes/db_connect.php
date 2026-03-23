<?php
// Load .env file if it exists
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Production fallback credentials (used when .env is not present on server)
$servername = $_ENV['DB_HOST'] ?? 'sql206.infinityfree.com';
$username   = $_ENV['DB_USER'] ?? 'if0_41448241';
$password   = $_ENV['DB_PASS'] ?? 'Sharda9834';
$dbname     = $_ENV['DB_NAME'] ?? 'if0_41448241_planify_db';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>