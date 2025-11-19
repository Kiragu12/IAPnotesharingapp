<?php
/**
 * Heroku Configuration File
 * This file reads database credentials from Heroku's CLEARDB_DATABASE_URL
 */

// Parse ClearDB URL from Heroku environment variable
$cleardb_url = getenv("CLEARDB_DATABASE_URL");

if ($cleardb_url) {
    $url = parse_url($cleardb_url);
    
    $db_host = $url["host"];
    $db_name = substr($url["path"], 1);
    $db_user = $url["user"];
    $db_pass = $url["pass"];
} else {
    // Fallback for local development
    $db_host = 'localhost';
    $db_name = 'noteshare_db';
    $db_user = 'root';
    $db_pass = 'paul';
}

// Configuration array
$conf = [
    // Database Configuration
    'db_host' => $db_host,
    'db_name' => $db_name,
    'db_user' => $db_user,
    'db_pass' => $db_pass,
    
    // Site Configuration
    'site_name' => 'Note Sharing App',
    'site_url' => getenv("SITE_URL") ?: 'http://localhost/IAPnotesharingapp-1',
    'admin_email' => getenv("SMTP_FROM_EMAIL") ?: 'admin@notesharing.com',
    
    // SMTP Configuration (from environment variables)
    'smtp_host' => getenv("SMTP_HOST") ?: 'smtp.gmail.com',
    'smtp_port' => getenv("SMTP_PORT") ?: 587,
    'smtp_username' => getenv("SMTP_USERNAME") ?: '',
    'smtp_password' => getenv("SMTP_PASSWORD") ?: '',
    'smtp_from_email' => getenv("SMTP_FROM_EMAIL") ?: 'noreply@notesharing.com',
    'smtp_from_name' => getenv("SMTP_FROM_NAME") ?: 'Note Sharing App',
    
    // Security Configuration
    'valid_email_domain' => ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'student.edu'],
    'min_password_length' => 8,
    
    // File Upload Configuration
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt'],
    'upload_path' => __DIR__ . '/uploads/documents/',
];

return $conf;
