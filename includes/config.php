<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rent_rooms_pgs');

// Site configuration
define('SITE_URL', 'http://localhost/rent-rooms-pgs');
define('SITE_NAME', 'Rent Rooms & PGS');

// File upload paths
define('UPLOAD_PATH', dirname(__DIR__) . '/assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
