<?php
/**
 * Seed Sample Data for Admin Panel Demo
 * This script adds realistic sample data to the database
 */

require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

echo "=================================\n";
echo "Seeding Sample Data\n";
echo "=================================\n\n";

try {
    $db = new Database($conf);
    
    // Sample users to add
    $sample_users = [
        ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@strathmore.edu', 'notes' => 5, 'views' => 245],
        ['name' => 'Michael Chen', 'email' => 'michael.chen@strathmore.edu', 'notes' => 8, 'views' => 432],
        ['name' => 'Emma Williams', 'email' => 'emma.williams@strathmore.edu', 'notes' => 3, 'views' => 156],
        ['name' => 'James Brown', 'email' => 'james.brown@strathmore.edu', 'notes' => 12, 'views' => 678],
        ['name' => 'Olivia Davis', 'email' => 'olivia.davis@strathmore.edu', 'notes' => 6, 'views' => 289],
        ['name' => 'William Martinez', 'email' => 'william.martinez@strathmore.edu', 'notes' => 4, 'views' => 198],
        ['name' => 'Sophia Garcia', 'email' => 'sophia.garcia@strathmore.edu', 'notes' => 9, 'views' => 512],
        ['name' => 'Benjamin Wilson', 'email' => 'benjamin.wilson@strathmore.edu', 'notes' => 7, 'views' => 345],
    ];
    
    // Sample categories
    $categories = [
        ['name' => 'Computer Science', 'description' => 'Programming, algorithms, and data structures'],
        ['name' => 'Mathematics', 'description' => 'Calculus, algebra, and statistics'],
        ['name' => 'Business', 'description' => 'Management, finance, and economics'],
        ['name' => 'Engineering', 'description' => 'Mechanical, electrical, and civil engineering'],
        ['name' => 'Sciences', 'description' => 'Physics, chemistry, and biology'],
        ['name' => 'Arts', 'description' => 'Literature, music, and visual arts'],
    ];
    
    // Check and create categories
    echo "Creating categories...\n";
    $category_ids = [];
    foreach ($categories as $cat) {
        $stmt = $db->query("SELECT id FROM categories WHERE name = ?", [$cat['name']]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            $db->query("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())", 
                [$cat['name'], $cat['description']]);
            $category_ids[] = $db->getPDO()->lastInsertId();
            echo "  ✓ Created category: {$cat['name']}\n";
        } else {
            $category_ids[] = $existing['id'];
            echo "  - Category exists: {$cat['name']}\n";
        }
    }
    
    // Create sample users and their notes
    echo "\nCreating sample users and notes...\n";
    $user_count = 0;
    $note_count = 0;
    
    foreach ($sample_users as $user_data) {
        // Check if user exists
        $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$user_data['email']]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            $user_id = $existing_user['id'];
            echo "  - User exists: {$user_data['name']}\n";
        } else {
            // Create user
            $password = password_hash('password123', PASSWORD_DEFAULT);
            
            $db->query("INSERT INTO users (email, password, full_name, created_at) VALUES (?, ?, ?, NOW())", 
                [$user_data['email'], $password, $user_data['name']]);
            
            $user_id = $db->getPDO()->lastInsertId();
            $user_count++;
            echo "  ✓ Created user: {$user_data['name']}\n";
        }
        
        // Create notes for this user
        $num_notes = $user_data['notes'];
        $total_views = $user_data['views'];
        
        for ($i = 0; $i < $num_notes; $i++) {
            $note_titles = [
                'Introduction to Data Structures',
                'Advanced Calculus Notes',
                'Marketing Strategy Summary',
                'Physics Lab Report',
                'Chemistry Formulas Sheet',
                'Java Programming Guide',
                'Financial Accounting Notes',
                'Linear Algebra Concepts',
                'Database Design Principles',
                'Organic Chemistry Review',
                'Business Ethics Case Studies',
                'Software Engineering Best Practices',
                'Microeconomics Summary',
                'Digital Marketing Tips',
                'Python for Beginners',
            ];
            
            $title = $note_titles[array_rand($note_titles)] . " (Part " . ($i + 1) . ")";
            $category_id = $category_ids[array_rand($category_ids)];
            $is_public = rand(0, 1);
            $views = rand(10, (int)($total_views / max(1, $num_notes)));
            
            $content = "This is a sample note about " . strtolower($title) . ". It contains important information for students studying this topic.";
            
            // Check if note exists
            $stmt = $db->query("SELECT id FROM notes WHERE title = ? AND user_id = ?", [$title, $user_id]);
            if (!$stmt->fetch()) {
                $db->query("INSERT INTO notes (user_id, title, content, category_id, is_public, views, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())", 
                    [$user_id, $title, $content, $category_id, $is_public, $views]);
                $note_count++;
            }
        }
    }
    
    // Add some sample activity logs
    echo "\nCreating sample admin activity logs...\n";
    $admin_stmt = $db->query("SELECT id FROM users WHERE is_admin = 1 LIMIT 1");
    $admin = $admin_stmt->fetch();
    
    if ($admin) {
        $activities = [
            ['type' => 'user_deleted', 'desc' => 'Deleted user account for policy violation'],
            ['type' => 'note_deleted', 'desc' => 'Removed inappropriate note content'],
            ['type' => 'category_created', 'desc' => 'Created new category: Computer Science'],
            ['type' => 'user_suspended', 'desc' => 'Suspended user for 7 days'],
            ['type' => 'export_data', 'desc' => 'Exported user data to CSV'],
        ];
        
        $activity_count = 0;
        foreach ($activities as $activity) {
            $db->query("INSERT INTO admin_activity_logs (admin_id, action_type, description, ip_address, created_at) VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))",
                [$admin['id'], $activity['type'], $activity['desc'], '127.0.0.1', rand(1, 30)]);
            $activity_count++;
        }
        echo "  ✓ Created $activity_count activity logs\n";
    }
    
    echo "\n=================================\n";
    echo "Sample Data Seeded Successfully!\n";
    echo "=================================\n\n";
    echo "Summary:\n";
    echo "  Users created: $user_count\n";
    echo "  Notes created: $note_count\n";
    echo "  Categories: " . count($categories) . "\n\n";
    echo "Sample Login Credentials:\n";
    echo "  Email: sarah.johnson@strathmore.edu\n";
    echo "  Password: password123\n\n";
    echo "Your admin panel now has realistic sample data!\n";
    echo "Visit: http://localhost/IAPnotesharingapp-1/views/admin/dashboard.php\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
