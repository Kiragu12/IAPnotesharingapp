<?php
// Redirect root to the public views index so the site loads in a browser
// If you'd rather serve a different document root (for example `public/`),
// update your Apache VirtualHost DocumentRoot instead.
header('Location: views/index.php');
exit;
