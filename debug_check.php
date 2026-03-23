<?php
// Temporary debug file — DELETE THIS FILE after diagnosing the issue
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>PHP Debug Info</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Check .env
$envFile = __DIR__ . '/.env';
echo "<p><strong>.env exists:</strong> " . (file_exists($envFile) ? 'YES' : 'NO') . "</p>";

// Check vendor/autoload.php
$autoload = __DIR__ . '/vendor/autoload.php';
echo "<p><strong>vendor/autoload.php exists:</strong> " . (file_exists($autoload) ? 'YES' : 'NO') . "</p>";

// Test DB connection
echo "<h3>DB Connection Test</h3>";
require_once __DIR__ . '/includes/db_connect.php';
if ($conn->connect_error) {
    echo "<p style='color:red'><strong>DB Error:</strong> " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green'><strong>DB Connected OK</strong></p>";
    $conn->close();
}

// Test Mail include
echo "<h3>Mail.php Include Test</h3>";
include_once __DIR__ . '/Mail.php';
echo "<p><strong>Mail class exists:</strong> " . (class_exists('Mail') ? 'YES' : 'NO') . "</p>";

echo "<p style='color:orange'><strong>Remember to delete this file after debugging!</strong></p>";
?>
