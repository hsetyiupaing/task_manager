<?php
/**
 * db.php
 * PURPOSE: Creates ONE shared PDO connection(PHP Data Objects) ($pdo) to MySQL/MariaDB.
 * Every other page does `require 'db.php';` to get access to $pdo.
 */

// Load .env from this file's directory and store its key/value pairs in $config.
// The second argument disables sections; the third keeps values as raw strings.
$config = parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_RAW);
// parse_ini_file() returns false when .env cannot be read or parsed.
if ($config === false) {
    // Stop here and explain how to create the required local configuration.
    die('Database configuration missing. Copy .env.example to .env and set your credentials.');
}

// Check that each required database setting was provided in .env.
foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
    if (!array_key_exists($key, $config)) {
        die('Database configuration is incomplete. Check your .env file.');
    }
}

$host    = $config['DB_HOST'];
$dbname  = $config['DB_NAME'];
$user    = $config['DB_USER'];
$pass    = $config['DB_PASS'];
$charset = 'utf8mb4';

// DSN = "Data Source Name": tells PDO which driver, host, database and charset to use.
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO options (an associative array).
$options = [
    // Throw exceptions on SQL errors instead of failing silently.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // fetch() / fetchAll() return associative arrays like ['title' => '...'].
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Use real prepared statements (safer against SQL injection).
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Try to open the connection.
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If it fails, stop and show a friendly message (never print passwords/details).
    die('Database connection failed. Check db.php settings and that MySQL is running.');
}
