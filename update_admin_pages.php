<?php
/**
 * Quick Script to Update All Admin Pages
 * Removes Categories and Activity Log links from sidebar
 */

$adminPages = [
    'views/admin/users.php',
    'views/admin/notes.php',
    'views/admin/flagged.php',
    'views/admin/export.php',
    'views/admin/analytics.php'
];

$oldSidebar = <<<'HTML'
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="bi bi-graph-up me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="bi bi-tags-fill me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="activity.php">
                                <i class="bi bi-clock-history me-2"></i>Activity Log
                            </a>
                        </li>
                        <li class="nav-item mt-4">
HTML;

$newSidebar = <<<'HTML'
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="bi bi-graph-up me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item mt-4">
HTML;

echo "=================================\n";
echo "UPDATING ADMIN PAGES\n";
echo "=================================\n\n";

$updated = 0;
$skipped = 0;

foreach ($adminPages as $page) {
    if (!file_exists($page)) {
        echo "✗ Skipped: $page (not found)\n";
        $skipped++;
        continue;
    }
    
    $content = file_get_contents($page);
    
    if (strpos($content, 'categories.php') !== false || strpos($content, 'activity.php') !== false) {
        $newContent = str_replace($oldSidebar, $newSidebar, $content);
        file_put_contents($page, $newContent);
        echo "✓ Updated: $page\n";
        $updated++;
    } else {
        echo "- Already clean: $page\n";
        $skipped++;
    }
}

echo "\n=================================\n";
echo "SUMMARY\n";
echo "=================================\n";
echo "Updated: $updated files\n";
echo "Skipped: $skipped files\n";
echo "=================================\n";
