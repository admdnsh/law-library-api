<?php
// ---------------------------------------------------------------------------
// Database configuration
// Reads from environment variables (Railway) with local XAMPP fallbacks.
// ---------------------------------------------------------------------------

$host = getenv('MYSQLHOST')     ?: 'localhost';
$db   = getenv('MYSQLDATABASE') ?: 'law_library';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = getenv('MYSQLPORT')     ?: 3306;

// Only show errors in development
$is_dev = (getenv('APP_ENV') === 'development');
error_reporting($is_dev ? E_ALL : 0);
ini_set('display_errors', $is_dev ? '1' : '0');

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    // Never expose raw DB errors in production
    $message = $is_dev ? $e->getMessage() : 'Database connection failed';
    die(json_encode(['error' => $message]));
}
