<?php
/**
 * Admin Middleware
 * Checks if user is authenticated and has admin privileges
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files
require_once __DIR__ . '/../../conf.php';
require_once __DIR__ . '/../../app/Services/Global/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in - redirect to signin
    header('Location: ../../views/auth/signin.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if user is admin
try {
    $db = new Database($conf);
    $user = $db->fetchOne("SELECT is_admin FROM users WHERE id = :uid", [':uid' => $_SESSION['user_id']]);
    
    if (!$user || $user['is_admin'] != 1) {
        // Not an admin - show access denied
        $_SESSION['error_msg'] = 'Access denied. You do not have administrator privileges.';
        header('Location: ../../views/dashboard.php');
        exit();
    }
    
    // User is admin - allow access
    $_SESSION['is_admin'] = true;
    
} catch (Exception $e) {
    error_log('Admin middleware error: ' . $e->getMessage());
    header('Location: ../../views/dashboard.php');
    exit();
}
