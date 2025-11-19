<?php
// Admin Data Export Handler
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();

// Get export type
$type = $_GET['type'] ?? '';

if (empty($type)) {
    header('Location: dashboard.php');
    exit;
}

// Export and log
$result = $adminController->exportData($type, $_SESSION['user_id']);

if ($result === false) {
    header('Location: dashboard.php?error=export_failed');
    exit;
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Output CSV
echo $result;
exit;
