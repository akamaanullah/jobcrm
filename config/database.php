<?php
// Set PHP timezone
date_default_timezone_set('Asia/Karachi');

// Database connection function
function connectDatabase()
{
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=jobs_crm", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Set timezone to match server timezone (UTC+5 for Pakistan)
        $pdo->exec("SET time_zone = '+05:00'");
        
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Test database connection
function testDatabase()
{
    try {
        $pdo = connectDatabase();
        $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Get database connection
function getDB()
{
    return connectDatabase();
}
?>