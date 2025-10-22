<?php
/**
 * Supabase PostgreSQL Database Connection Test
 * Tests connection to Supabase using PDO
 */

// Load Supabase configuration
require_once 'conf.supabase.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supabase Connection Test - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .test-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            max-width: 700px;
            width: 100%;
        }
        .test-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .test-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
        .result-box {
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .success-box {
            background: rgba(25, 135, 84, 0.1);
            border: 2px solid #198754;
        }
        .error-box {
            background: rgba(220, 53, 69, 0.1);
            border: 2px solid #dc3545;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            display: flex;
            align-items: center;
        }
        .info-value {
            color: #333;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
            text-align: right;
            max-width: 60%;
        }
        .table-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <div class="test-icon">
                <i class="bi bi-database-gear"></i>
            </div>
            <h2>Supabase Connection Test</h2>
            <p class="text-muted">Testing PostgreSQL database connection</p>
        </div>

        <?php
        $connectionSuccess = false;
        $pdo = null;
        
        try {
            // Build PostgreSQL DSN
            $dsn = "pgsql:host={$conf['db_host']};port={$conf['db_port']};dbname={$conf['db_name']};sslmode=require";
            
            // PDO options
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            // Attempt connection
            $pdo = new PDO($dsn, $conf['db_user'], $conf['db_pass'], $options);
            $connectionSuccess = true;
            
            // Get PostgreSQL version
            $stmt = $pdo->query('SELECT version()');
            $version = $stmt->fetch();
            
            echo '<div class="result-box success-box">';
            echo '<h4 class="text-success mb-3"><i class="bi bi-check-circle-fill me-2"></i>Connection Successful!</h4>';
            echo '<p class="mb-0">Successfully connected to your Supabase PostgreSQL database.</p>';
            echo '</div>';
            
            // Connection Details
            echo '<div class="mb-4">';
            echo '<h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Connection Details</h5>';
            
            echo '<div class="info-item">';
            echo '<span class="info-label"><i class="bi bi-server me-2"></i>Host</span>';
            echo '<span class="info-value">' . htmlspecialchars($conf['db_host']) . '</span>';
            echo '</div>';
            
            echo '<div class="info-item">';
            echo '<span class="info-label"><i class="bi bi-hdd me-2"></i>Database</span>';
            echo '<span class="info-value">' . htmlspecialchars($conf['db_name']) . '</span>';
            echo '</div>';
            
            echo '<div class="info-item">';
            echo '<span class="info-label"><i class="bi bi-plug me-2"></i>Port</span>';
            echo '<span class="info-value">' . htmlspecialchars($conf['db_port']) . '</span>';
            echo '</div>';
            
            echo '<div class="info-item">';
            echo '<span class="info-label"><i class="bi bi-person me-2"></i>User</span>';
            echo '<span class="info-value">' . htmlspecialchars($conf['db_user']) . '</span>';
            echo '</div>';
            
            echo '<div class="info-item">';
            echo '<span class="info-label"><i class="bi bi-code-square me-2"></i>Version</span>';
            echo '<span class="info-value" style="font-size: 0.75rem;">' . htmlspecialchars(substr($version['version'], 0, 100)) . '...</span>';
            echo '</div>';
            
            echo '</div>';
            
            // List existing tables
            echo '<div class="mb-4">';
            echo '<h5 class="mb-3"><i class="bi bi-table me-2"></i>Database Tables</h5>';
            
            $sql = "SELECT table_name, table_type 
                    FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    ORDER BY table_name";
            $stmt = $pdo->query($sql);
            $tables = $stmt->fetchAll();
            
            if (count($tables) > 0) {
                echo '<div class="row g-2">';
                foreach ($tables as $table) {
                    $iconClass = $table['table_type'] === 'VIEW' ? 'bi-eye' : 'bi-table';
                    echo '<div class="col-md-6">';
                    echo '<div class="table-badge">';
                    echo '<i class="bi ' . $iconClass . ' me-2"></i>';
                    echo htmlspecialchars($table['table_name']);
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="alert alert-info">';
                echo '<i class="bi bi-info-circle me-2"></i>';
                echo '<strong>No tables found.</strong> Your database is ready for table creation.';
                echo '</div>';
            }
            
            echo '</div>';
            
            // Next steps
            echo '<div class="alert alert-success">';
            echo '<h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Next Steps</h6>';
            echo '<ul class="mb-0">';
            echo '<li>Create your database tables for the notes app</li>';
            echo '<li>Set up user authentication tables</li>';
            echo '<li>Configure Row Level Security (RLS) policies</li>';
            echo '<li>Update your application code to use this connection</li>';
            echo '</ul>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="result-box error-box">';
            echo '<h4 class="text-danger mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Connection Failed</h4>';
            echo '<p class="mb-2"><strong>Error:</strong></p>';
            echo '<p class="mb-0 text-muted"><code>' . htmlspecialchars($e->getMessage()) . '</code></p>';
            echo '</div>';
            
            echo '<div class="alert alert-warning">';
            echo '<h6 class="alert-heading"><i class="bi bi-tools me-2"></i>Troubleshooting Steps</h6>';
            echo '<ol class="mb-0">';
            echo '<li><strong>Check Password:</strong> Make sure you replaced <code>PASTE_YOUR_PASSWORD_HERE</code> in <code>conf.supabase.php</code></li>';
            echo '<li><strong>Verify Credentials:</strong> Go to Supabase Dashboard → Project Settings → Database</li>';
            echo '<li><strong>Enable PostgreSQL:</strong> Check that <code>pdo_pgsql</code> extension is enabled in your <code>php.ini</code></li>';
            echo '<li><strong>SSL Support:</strong> Ensure OpenSSL is enabled in PHP</li>';
            echo '<li><strong>Network Access:</strong> Check Supabase Network Restrictions settings</li>';
            echo '</ol>';
            echo '</div>';
            
            // PHP Extensions Check
            echo '<div class="mb-4">';
            echo '<h5 class="mb-3"><i class="bi bi-gear me-2"></i>PHP Environment Check</h5>';
            
            $pdo_pgsql = extension_loaded('pdo_pgsql');
            $openssl = extension_loaded('openssl');
            
            echo '<div class="info-item ' . ($pdo_pgsql ? 'border-success' : 'border-danger') . '">';
            echo '<span class="info-label">';
            echo '<i class="bi ' . ($pdo_pgsql ? 'bi-check-circle text-success' : 'bi-x-circle text-danger') . ' me-2"></i>';
            echo 'PDO PostgreSQL Extension';
            echo '</span>';
            echo '<span class="info-value">' . ($pdo_pgsql ? 'Enabled ✓' : 'Missing ✗') . '</span>';
            echo '</div>';
            
            echo '<div class="info-item ' . ($openssl ? 'border-success' : 'border-danger') . '">';
            echo '<span class="info-label">';
            echo '<i class="bi ' . ($openssl ? 'bi-check-circle text-success' : 'bi-x-circle text-danger') . ' me-2"></i>';
            echo 'OpenSSL Extension';
            echo '</span>';
            echo '<span class="info-value">' . ($openssl ? 'Enabled ✓' : 'Missing ✗') . '</span>';
            echo '</div>';
            
            if (!$pdo_pgsql) {
                echo '<div class="alert alert-danger mt-3">';
                echo '<strong>Action Required:</strong> PostgreSQL PDO extension is not installed. ';
                echo 'Edit your <code>php.ini</code> file and uncomment: <code>;extension=pdo_pgsql</code>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        ?>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary me-2">
                <i class="bi bi-house me-2"></i>Home
            </a>
            <?php if ($connectionSuccess): ?>
                <button class="btn btn-success" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Test Again
                </button>
            <?php else: ?>
                <button class="btn btn-warning" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Retry Connection
                </button>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
