<?php
/**
 * User Settings Page - Enhanced with Modern UI and Full Database Integration
 */
session_start();

// Load configuration and required services
require_once __DIR__ . '/../../config/conf.php';
require_once __DIR__ . '/../../app/Services/Global/Database.php';
require_once __DIR__ . '/../../app/Services/Global/fncs.php';

$ObjFncs = new fncs();
$db = new Database($conf);

// Check user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Fetch current user data and additional settings
try {
    $current_user = $db->fetchOne(
        'SELECT id, full_name, email, created_at, status FROM users WHERE id = :id LIMIT 1', 
        [':id' => $user_id]
    );
    
    // Get 2FA status
    $tfa_status = $db->fetchOne(
        'SELECT COUNT(*) as count FROM two_factor_codes WHERE user_id = :id AND used = 0', 
        [':id' => $user_id]
    );
    $has_2fa_enabled = ($tfa_status['count'] ?? 0) > 0;
    
    // Get user notes statistics
    $notes_stats = $db->fetchOne(
        'SELECT 
            COUNT(*) as total_notes,
            SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END) as public_notes,
            SUM(CASE WHEN note_type = "file" THEN 1 ELSE 0 END) as file_notes
        FROM notes WHERE user_id = :id', 
        [':id' => $user_id]
    );
    
} catch (Exception $e) {
    error_log('Settings: failed to fetch user data: ' . $e->getMessage());
    $current_user = null;
    $has_2fa_enabled = false;
    $notes_stats = ['total_notes' => 0, 'public_notes' => 0, 'file_notes' => 0];
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));

    // Basic validation
    if ($name === '' || !preg_match('/^[\p{L} \-\.]+$/u', $name)) {
        $error_message = 'Please enter a valid name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            $db->query('UPDATE users SET full_name = :name, email = :email WHERE id = :id', [
                ':name' => $name,
                ':email' => $email,
                ':id' => $user_id
            ]);

            // Update session variables to reflect changes
            $_SESSION['user_name'] = $name;
            $_SESSION['full_name'] = $name;
            $_SESSION['user_email'] = $email;

            $success_message = 'Profile updated successfully! Changes are now active.';
            // Refresh current user values
            $current_user['full_name'] = $name;
            $current_user['email'] = $email;
        } catch (Exception $e) {
            error_log('Settings (update profile) error: ' . $e->getMessage());
            $error_message = 'Failed to update profile. Please try again later.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if ($old_pass === '' || $new_pass === '' || $confirm_pass === '') {
        $error_message = 'Please fill in all password fields.';
    } elseif ($new_pass !== $confirm_pass) {
        $error_message = 'New password and confirmation do not match.';
    } elseif (strlen($new_pass) < ($conf['min_password_length'] ?? 6)) {
        $error_message = 'New password must be at least ' . ($conf['min_password_length'] ?? 6) . ' characters long.';
    } else {
        try {
            $row = $db->fetchOne('SELECT password FROM users WHERE id = :id LIMIT 1', [':id' => $user_id]);
            $hash = $row['password'] ?? '';

            if ($hash && password_verify($old_pass, $hash)) {
                $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $db->query('UPDATE users SET password = :pass WHERE id = :id', [':pass' => $hashed_new_pass, ':id' => $user_id]);
                $success_message = 'Password changed successfully!';
            } else {
                $error_message = 'Old password is incorrect.';
            }
        } catch (Exception $e) {
            error_log('Settings (change password) error: ' . $e->getMessage());
            $error_message = 'Failed to change password. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
       body {
  background-color: #f9f9f9;
  color: #222;
  transition: background-color 0.3s, color 0.3s;
    }

/* Dark theme */
      body.dark-mode {
           background-color: #1e1e1e;
            color: #ddd;
  } 

/* Optional tweaks to match dashboard style */
    .dark-mode .navbar,
    .dark-mode .sidebar,
    .dark-mode .card {
     background-color: #2b2b2b !important;
     color: #ddd;
    }

    .dark-mode .form-check-label {
      color: #ddd;
     }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .settings-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            overflow: hidden;
            border: none;
            margin-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-bottom: 2px solid #f8f9fa;
            padding: 25px 30px;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 32px;
            margin: 0 auto 15px;
        }
        
        .back-btn {
            background: #6c757d;
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            padding: 10px 25px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
            color: white;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">
                <i class="bi bi-journal-text me-2"></i>NotesShare Academy
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user_name); ?>!</span>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        
        <!-- Back Button -->
        <div class="mb-4">
            <a href="../dashboard.php" class="back-btn" onclick="sessionStorage.setItem('selectedNavPage', 'settings');">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
        
        <div class="row">
            <!-- User Profile Summary -->
            <div class="col-lg-4 mb-4">
                <div class="settings-card">
                    <div class="card-body text-center">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($current_user['full_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($current_user['full_name'] ?? 'User'); ?></h5>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($current_user['email'] ?? ''); ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Account Status:</span>
                            <span class="status-badge status-active">
                                <i class="bi bi-check-circle me-1"></i>Active
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>2FA Status:</span>
                            <span class="status-badge <?php echo $has_2fa_enabled ? 'status-active' : 'status-inactive'; ?>">
                                <i class="bi bi-shield<?php echo $has_2fa_enabled ? '-check' : ''; ?> me-1"></i>
                                <?php echo $has_2fa_enabled ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </div>
                        
                        <div class="text-muted mt-3">
                            <small>Member since: <?php echo date('F Y', strtotime($current_user['created_at'] ?? 'now')); ?></small>
                        </div>
                    </div>
                </div>
                
                <!-- Notes Statistics -->
                <div class="stats-card">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-graph-up me-2"></i>Your Activity
                    </h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-bold fs-4"><?php echo $notes_stats['total_notes'] ?? 0; ?></div>
                            <div class="small">Total Notes</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold fs-4"><?php echo $notes_stats['public_notes'] ?? 0; ?></div>
                            <div class="small">Public</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold fs-4"><?php echo $notes_stats['file_notes'] ?? 0; ?></div>
                            <div class="small">Files</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-check form-switch mt-3 ms-3">
              <input class="form-check-input" type="checkbox" id="themeSwitch">
                 <label class="form-check-label" for="themeSwitch">
                    <i class="bi bi-brightness-high me-2" id="themeIcon"></i>
                    <span id="themeLabel">Light Mode</span>
                 </label>
            </div>
            
            <!-- Settings Forms -->
            <div class="col-lg-8">
                
                <!-- Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-custom alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-custom alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Update Profile -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-person-circle me-2"></i>Update Profile
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 30px;">
                        <form method="POST">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Full Name</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($current_user['full_name'] ?? ''); ?>" required>
                                    <div class="form-text">Your display name across the platform</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Email Address</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" required>
                                    <div class="form-text">Used for login and notifications</div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" name="update_profile" class="btn btn-gradient">
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 30px;">
                        <form method="POST" id="passwordForm">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Current Password</label>
                                    <input type="password" name="old_password" class="form-control" required>
                                    <div class="form-text">Enter your existing password to verify</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">New Password</label>
                                    <input type="password" name="new_password" class="form-control" 
                                           minlength="<?php echo $conf['min_password_length'] ?? 6; ?>" required 
                                           id="newPassword">
                                    <div class="form-text">
                                        Password must be at least <?php echo $conf['min_password_length'] ?? 6; ?> characters long.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" 
                                           minlength="<?php echo $conf['min_password_length'] ?? 6; ?>" required 
                                           id="confirmPassword">
                                    <div class="form-text" id="passwordMatch"></div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" name="change_password" class="btn btn-gradient" id="passwordSubmit">
                                    <i class="bi bi-key me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-shield-check me-2"></i>Security Settings
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 30px;">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <strong><i class="bi bi-shield-check me-2 text-primary"></i>Two-Factor Authentication (2FA)</strong>
                                        <div class="text-muted small mt-1">Add an extra layer of security to your account</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="status-badge <?php echo $has_2fa_enabled ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $has_2fa_enabled ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                        <div class="mt-2">
                                            <button class="btn btn-outline-primary btn-sm" onclick="manage2FA()">
                                                <i class="bi bi-gear me-1"></i>Manage
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <strong><i class="bi bi-list me-2 text-primary"></i>Login Sessions</strong>
                                        <div class="text-muted small mt-1">Manage your active login sessions and devices</div>
                                    </div>
                                    <div>
                                        <a href="../auth/sessions.php" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-list me-1"></i>View Sessions
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <strong><i class="bi bi-download me-2 text-primary"></i>Download Your Data</strong>
                                        <div class="text-muted small mt-1">Export all your notes and account information</div>
                                    </div>
                                    <div>
                                        <button class="btn btn-outline-success btn-sm" onclick="exportData()">
                                            <i class="bi bi-download me-1"></i>Export Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25">
                                    <div>
                                        <strong class="text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Account</strong>
                                        <div class="text-muted small mt-1">Permanently delete your account and all data</div>
                                    </div>
                                    <div>
                                        <button class="btn btn-outline-danger btn-sm" onclick="confirmDeleteAccount()">
                                            <i class="bi bi-trash me-1"></i>Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Password validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const passwordMatch = document.getElementById('passwordMatch');
            const submitBtn = document.getElementById('passwordSubmit');

            function validatePassword() {
                if (newPassword.value && confirmPassword.value) {
                    if (newPassword.value === confirmPassword.value) {
                        passwordMatch.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</span>';
                        submitBtn.disabled = false;
                    } else {
                        passwordMatch.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match</span>';
                        submitBtn.disabled = true;
                    }
                } else {
                    passwordMatch.innerHTML = '';
                    submitBtn.disabled = false;
                }
            }

            newPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
        });

        // 2FA Management
        function manage2FA() {
            alert('2FA management will redirect to setup page. This feature can be implemented based on your 2FA system.');
            // window.location.href = 'two-factor-setup.php';
        }

        // Export Data
        function exportData() {
            if (confirm('This will download all your notes and account data as a JSON file. Continue?')) {
                // Create export functionality
                window.location.href = 'export-data.php';
            }
        }

        // Delete Account
        function confirmDeleteAccount() {
            if (confirm('⚠️ WARNING: This will permanently delete your account and ALL your notes. This action cannot be undone!\n\nAre you absolutely sure you want to delete your account?')) {
                if (confirm('Final confirmation: Type "DELETE" in the prompt that follows to confirm account deletion.')) {
                    const confirmation = prompt('Type "DELETE" to confirm account deletion:');
                    if (confirmation === 'DELETE') {
                        window.location.href = 'delete-account.php';
                    } else {
                        alert('Account deletion cancelled.');
                    }
                }
            }
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.settings-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        // Theme Switcher
        const themeSwitch = document.getElementById('themeSwitch');
        const themeIcon = document.getElementById('themeIcon');
        const themeLabel = document.getElementById('themeLabel');
        themeSwitch.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                themeIcon.classList.remove('bi-brightness-high');
                themeIcon.classList.add('bi-moon');
                themeLabel.textContent = 'Dark Mode';
            } else {
                document.body.classList.remove('dark-mode');
                themeIcon.classList.remove('bi-moon');
                themeIcon.classList.add('bi-brightness-high');
                themeLabel.textContent = 'Light Mode';
            }
        });
    </script>
</body>
</html>