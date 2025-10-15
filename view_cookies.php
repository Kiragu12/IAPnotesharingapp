<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Viewer - ICS Notes App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem 0;
        }
        .cookie-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
        }
        .cookie-name {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 15px 15px 0 0;
            font-weight: bold;
        }
        .cookie-details {
            padding: 1rem;
        }
        .no-cookies {
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="text-center mb-4">🍪 Cookie Viewer</h1>
                <p class="text-center mb-4">Here are all the cookies stored for your Notes Sharing App:</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h3>🖥️ Server-Side Cookies (PHP)</h3>
                        <?php if (empty($_COOKIE)): ?>
                            <div class="cookie-card">
                                <div class="cookie-details no-cookies">
                                    No cookies found on server side
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($_COOKIE as $name => $value): ?>
                                <div class="cookie-card">
                                    <div class="cookie-name">
                                        <?= htmlspecialchars($name) ?>
                                    </div>
                                    <div class="cookie-details">
                                        <strong>Value:</strong> <?= htmlspecialchars($value) ?><br>
                                        <strong>Length:</strong> <?= strlen($value) ?> characters<br>
                                        <strong>Type:</strong> Server-accessible
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <h3>🌐 Client-Side Cookies (JavaScript)</h3>
                        <div id="js-cookies">
                            <!-- JavaScript will populate this -->
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <h3>🔧 Cookie Management</h3>
                        <div class="cookie-card">
                            <div class="cookie-details">
                                <h5>Test Cookie Creation:</h5>
                                <button class="btn btn-primary me-2" onclick="createTestCookie()">Create Test Cookie</button>
                                <button class="btn btn-warning me-2" onclick="createSessionCookie()">Create Session Cookie</button>
                                <button class="btn btn-success me-2" onclick="createPersistentCookie()">Create 30-Day Cookie</button>
                                <button class="btn btn-danger" onclick="clearAllCookies()">Clear All Cookies</button>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        • Test cookies to understand how they work<br>
                                        • Refresh page to see changes<br>
                                        • Check browser developer tools for more details
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="cookie-card">
                            <div class="cookie-details text-center">
                                <a href="index.php" class="btn btn-outline-primary">← Back to Home</a>
                                <a href="dashboard.php" class="btn btn-outline-success">Go to Dashboard</a>
                                <button class="btn btn-outline-info" onclick="location.reload()">🔄 Refresh</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Display JavaScript-accessible cookies
        function displayJSCookies() {
            const jsCookiesDiv = document.getElementById('js-cookies');
            const cookies = document.cookie;
            
            if (!cookies) {
                jsCookiesDiv.innerHTML = `
                    <div class="cookie-card">
                        <div class="cookie-details no-cookies">
                            No JavaScript-accessible cookies found
                        </div>
                    </div>
                `;
                return;
            }
            
            const cookieArray = cookies.split(';');
            let html = '';
            
            cookieArray.forEach(cookie => {
                const [name, value] = cookie.trim().split('=');
                if (name && value) {
                    html += `
                        <div class="cookie-card">
                            <div class="cookie-name">${name}</div>
                            <div class="cookie-details">
                                <strong>Value:</strong> ${value}<br>
                                <strong>Length:</strong> ${value.length} characters<br>
                                <strong>Type:</strong> JavaScript-accessible
                            </div>
                        </div>
                    `;
                }
            });
            
            jsCookiesDiv.innerHTML = html;
        }
        
        // Test cookie functions
        function createTestCookie() {
            document.cookie = "test_cookie=hello_world; path=/";
            alert("Test cookie created! Refresh to see it.");
        }
        
        function createSessionCookie() {
            document.cookie = "session_test=temporary_data; path=/";
            alert("Session cookie created! (Expires when browser closes)");
        }
        
        function createPersistentCookie() {
            const expiry = new Date();
            expiry.setTime(expiry.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days
            document.cookie = `persistent_test=long_lasting_data; expires=${expiry.toUTCString()}; path=/`;
            alert("30-day persistent cookie created!");
        }
        
        function clearAllCookies() {
            const cookies = document.cookie.split(";");
            
            for (let cookie of cookies) {
                const eqPos = cookie.indexOf("=");
                const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
                document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
            }
            
            alert("All JavaScript-accessible cookies cleared! Refresh to see changes.");
        }
        
        // Load cookies when page loads
        document.addEventListener('DOMContentLoaded', displayJSCookies);
    </script>
</body>
</html>