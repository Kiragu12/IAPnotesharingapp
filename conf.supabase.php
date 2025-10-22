<?php
/**
 * Supabase Database Configuration
 * This is a LOCAL file - never commit this to git!
 * Copy from conf.supabase.sample.php and add your credentials
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site timezone
$conf['site_timezone'] = 'Africa/Nairobi';

// Site information
$conf['site_name'] = 'NotesShare Academy';
$conf['site_url'] = 'http://localhost/IAPnotesharingapp-1';
$conf['admin_email'] = 'admin@noteshareacademy.com';

// Site language
$conf['site_lang'] = 'en';
require_once __DIR__ . '/Lang/' . $conf['site_lang'] . '.php';

// ============================================
// SUPABASE DATABASE CONFIGURATION (PostgreSQL)
// ============================================
$conf['db_type'] = 'pgsql';  // PostgreSQL
$conf['db_host'] = 'db.jijtnvlbnafnqsskwkfd.supabase.co';
$conf['db_port'] = '5432';
$conf['db_user'] = 'postgres';
$conf['db_pass'] = 'note2025';  // Your Supabase database password
$conf['db_name'] = 'postgres';

// Supabase Project Details
$conf['supabase_url'] = 'https://jijtnvlbnafnqsskwkfd.supabase.co';
$conf['supabase_anon_key'] = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImppanRudmxibmFmbnFzc2t3a2ZkIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjA1MzA4MTYsImV4cCI6MjA3NjEwNjgxNn0.no69Y4qGuAkNNKH_1ROxTE4tKyBw5-gLegli0cBfrKU';

// ============================================
// EMAIL CONFIGURATION
// ============================================
$conf['mail_type'] = 'smtp';
$conf['smtp_host'] = 'smtp.gmail.com';
$conf['smtp_user'] = 'your-email@gmail.com';
$conf['smtp_pass'] = 'your-app-password';
$conf['smtp_port'] = 465;
$conf['smtp_secure'] = 'ssl';

// ============================================
// PASSWORD & SECURITY SETTINGS
// ============================================
$conf['min_password_length'] = 8;

// Valid email domains
$conf['valid_email_domain'] = [
    'noteshareacademy.com',
    'gmail.com',
    'yahoo.com',
    'outlook.com',
    'hotmail.com',
    'strathmore.edu'
];

// Registration verification code defaults
$conf['reg_ver_code'] = rand(100000, 999999);
$conf['ver_code_expiry'] = 10; // minutes

// ============================================
// SESSION & COOKIE SETTINGS
// ============================================
$conf['session_lifetime'] = 3600; // 1 hour
$conf['remember_me_duration'] = 2592000; // 30 days
?>
