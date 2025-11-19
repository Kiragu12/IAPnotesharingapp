<?php
/**
 * Railway Configuration File
 * This file reads database credentials from Railway's environment variables
 */

// Get Railway MySQL credentials from environment variables
$db_host = getenv("MYSQLHOST") ?: getenv("DB_HOST");
$db_port = getenv("MYSQLPORT") ?: getenv("DB_PORT") ?: "3306";
$db_name = getenv("MYSQLDATABASE") ?: getenv("DB_NAME");
$db_user = getenv("MYSQLUSER") ?: getenv("DB_USER");
$db_pass = getenv("MYSQLPASSWORD") ?: getenv("DB_PASSWORD");

// Fallback for local development
if (!$db_host) {
    $db_host = 'localhost';
    $db_name = 'noteshare_db';
    $db_user = 'root';
    $db_pass = 'paul';
    $db_port = '3306';
}

// Build connection string with port
$db_host_with_port = $db_host . ':' . $db_port;

// Configuration array
$conf = [
    // Database Configuration
    'db_host' => $db_host_with_port,
    'db_name' => $db_name,
    'db_user' => $db_user,
    'db_pass' => $db_pass,
    
    // Site Configuration
    'site_name' => 'Note Sharing App',
    'site_url' => getenv("RAILWAY_STATIC_URL") ?: getenv("SITE_URL") ?: 'http://localhost/IAPnotesharingapp-1',
    'admin_email' => getenv("SMTP_FROM_EMAIL") ?: 'admin@notesharing.com',
    
    // SMTP Configuration (from environment variables)
    'smtp_host' => getenv("SMTP_HOST") ?: 'smtp.gmail.com',
    'smtp_port' => getenv("SMTP_PORT") ?: 587,
    'smtp_username' => getenv("SMTP_USERNAME") ?: '',
    'smtp_password' => getenv("SMTP_PASSWORD") ?: '',
    'smtp_from_email' => getenv("SMTP_FROM_EMAIL") ?: 'noreply@notesharing.com',
    'smtp_from_name' => getenv("SMTP_FROM_NAME") ?: 'Note Sharing App',
    
    // Security Configuration
    'valid_email_domain' => ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'student.edu', 'strathmore.edu'],
    'min_password_length' => 8,
    
    // File Upload Configuration
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt'],
    'upload_path' => __DIR__ . '/uploads/documents/',
];

return $conf;
