<?php
/*
 * db_connect.php
 * Purpose: Establish a MySQLi connection to the `notes_app` database.
 * Usage: Include this file in other scripts to access the $conn MySQLi connection.
 * Note: Update credentials for your environment. For production, avoid hard-coding credentials.
 */
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "notes_app";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
