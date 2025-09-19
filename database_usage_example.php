<?php
/**
 * Example usage of the Database class
 * This file demonstrates how to use the Database class in your application
 */

require_once 'conf.php';
require_once 'Global/Database.php';

echo "=== Database Class Usage Example ===\n\n";

try {
    // Create database instance
    $db = new Database($conf);
    
    echo "✅ Database connection established successfully!\n\n";
    
    // Example 1: Check if tables exist
    echo "1. Checking existing tables:\n";
    $tables = ['users', 'notes'];
    foreach ($tables as $table) {
        $exists = $db->tableExists($table);
        echo "   - Table '$table': " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
    }
    echo "\n";
    
    // Example 2: Fetch all users (if users table exists)
    if ($db->tableExists('users')) {
        echo "2. Fetching users from database:\n";
        try {
            $users = $db->fetchAll("SELECT * FROM users LIMIT 5");
            if (count($users) > 0) {
                echo "   Found " . count($users) . " user(s):\n";
                foreach ($users as $user) {
                    // Display user info (be careful with sensitive data)
                    echo "   - ID: " . $user['id'] . ", Email: " . $user['email'] . "\n";
                }
            } else {
                echo "   No users found in the database.\n";
            }
        } catch (Exception $e) {
            echo "   Error fetching users: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // Example 3: Fetch all notes (if notes table exists)
    if ($db->tableExists('notes')) {
        echo "3. Fetching notes from database:\n";
        try {
            $notes = $db->fetchAll("SELECT * FROM notes LIMIT 5");
            if (count($notes) > 0) {
                echo "   Found " . count($notes) . " note(s):\n";
                foreach ($notes as $note) {
                    echo "   - ID: " . $note['id'] . ", Title: " . (isset($note['title']) ? $note['title'] : 'N/A') . "\n";
                }
            } else {
                echo "   No notes found in the database.\n";
            }
        } catch (Exception $e) {
            echo "   Error fetching notes: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // Example 4: Get database info
    echo "4. Database information:\n";
    $version = $db->fetchOne("SELECT VERSION() as version");
    echo "   MySQL Version: " . $version['version'] . "\n";
    
    $currentDb = $db->fetchOne("SELECT DATABASE() as db_name");
    echo "   Current Database: " . $currentDb['db_name'] . "\n";
    
    echo "\n✅ All database operations completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>