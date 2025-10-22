<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site timezone
$conf['site_timezone'] = 'Africa/Nairobi';

// Site information
$conf['site_name'] = 'ICS B Academy';
$conf['site_url'] = 'http://localhost/tol';
$conf['admin_email'] = 'admin@icsbacademy.com';

// Site language
$conf['site_lang'] = 'en';
require_once __DIR__ . '/Lang/' . $conf['site_lang'] . '.php';

// Database configuration (fill with your local DB credentials)
$conf['db_type'] = 'pdo';
$conf['db_host'] = 'localhost';
$conf['db_port'] = '3306';
$conf['db_user'] = 'root';
$conf['db_pass'] = '<your_db_password>';
$conf['db_name'] = 'noteshare_db';

// Email configuration (fill with your SMTP)
$conf['mail_type'] = 'smtp'; // 'smtp' or 'mail'
$conf['smtp_host'] = 'smtp.gmail.com';
$conf['smtp_user'] = '<your_email@example.com>';
$conf['smtp_pass'] = '<your_app_password>'; // For Gmail use an App Password
$conf['smtp_port'] = 465; // 465 for SSL, 587 for TLS
$conf['smtp_secure'] = 'ssl'; // 'ssl' or 'tls'

// Set password length
$conf['min_password_length'] = 8;

// Valid email domains
$conf['valid_email_domain'] = ['icsbacademy.com', 'yahoo.com', 'gmail.com', 'outlook.com', 'hotmail.com', 'strathmore.edu'];

// Registration verification code defaults
$conf['reg_ver_code'] = rand(100000, 999999);
$conf['ver_code_expiry'] = 10; // minutes
