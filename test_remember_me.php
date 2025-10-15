<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remember Me Test - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="bi bi-cookie me-2"></i>Remember Me Functionality Test</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        require_once 'ClassAutoLoad.php';
                        
                        echo "<h5>Current Session Status:</h5>";
                        
                        if (isset($_SESSION['user_id'])) {
                            echo "<div class='alert alert-success'>";
                            echo "<i class='bi bi-check-circle me-2'></i><strong>Logged In!</strong><br>";
                            echo "User ID: " . $_SESSION['user_id'] . "<br>";
                            echo "Email: " . ($_SESSION['user_email'] ?? 'Not set') . "<br>";
                            echo "Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
                            
                            if (isset($_SESSION['auto_login'])) {
                                echo "<br><span class='badge bg-info'>Auto-logged in via Remember Token</span>";
                            }
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning'>";
                            echo "<i class='bi bi-exclamation-triangle me-2'></i>Not logged in";
                            echo "</div>";
                        }
                        
                        echo "<h5>Remember Token Status:</h5>";
                        
                        if (isset($_COOKIE['remember_token'])) {
                            echo "<div class='alert alert-info'>";
                            echo "<i class='bi bi-cookie me-2'></i><strong>Remember Token Found!</strong><br>";
                            echo "Token: " . substr($_COOKIE['remember_token'], 0, 20) . "...<br>";
                            echo "This token will keep you logged in for 30 days.";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-secondary'>";
                            echo "<i class='bi bi-cookie me-2'></i>No remember token found";
                            echo "</div>";
                        }
                        ?>
                        
                        <h5>Test Actions:</h5>
                        <div class="d-grid gap-2">
                            <a href="signin.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Go to Sign In
                            </a>
                            <a href="dashboard.php" class="btn btn-success">
                                <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                            </a>
                            <a href="logout.php" class="btn btn-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout (Clear All Tokens)
                            </a>
                            <a href="view_cookies.php" class="btn btn-info">
                                <i class="bi bi-cookie me-2"></i>View All Cookies
                            </a>
                        </div>
                        
                        <hr>
                        
                        <h6>How to Test Remember Me:</h6>
                        <ol>
                            <li>Sign in with "Remember me for 30 days" checked</li>
                            <li>Visit this page to see your remember token</li>
                            <li>Close your browser completely</li>
                            <li>Reopen browser and visit any page</li>
                            <li>You should be automatically logged in!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>