<?php
require_once __DIR__ . '/db/config.php';

try {
    $pdo = db();
    
    $queries = [
        "CREATE TABLE IF NOT EXISTS Officers (
            User_ID VARCHAR(255) PRIMARY KEY,
            Role VARCHAR(100),
            Access_Level INT DEFAULT 0,
            Email VARCHAR(255) UNIQUE,
            Password VARCHAR(255),
            Name VARCHAR(255),
            Photo VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS Voters (
            User_ID VARCHAR(255) PRIMARY KEY,
            Email VARCHAR(255) UNIQUE,
            Password VARCHAR(255),
            Track_Cluster VARCHAR(100),
            Grade_Level INT,
            Section VARCHAR(100),
            Has_Voted BOOLEAN DEFAULT FALSE
        )",
        "CREATE TABLE IF NOT EXISTS Candidates (
            User_ID VARCHAR(255) PRIMARY KEY,
            Email VARCHAR(255) UNIQUE,
            Password VARCHAR(255),
            Track_Cluster VARCHAR(100),
            Grade_Level INT,
            Section VARCHAR(100),
            Name VARCHAR(255),
            Position VARCHAR(255),
            Party VARCHAR(255),
            Photo VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS Election_History (
            Election_ID INT AUTO_INCREMENT PRIMARY KEY,
            Year INT,
            Parties TEXT,
            Candidates TEXT,
            Results TEXT,
            Total_Voters INT,
            Status VARCHAR(50),
            Start_Date DATETIME,
            End_Date DATETIME
        )",
        "CREATE TABLE IF NOT EXISTS Audit_Trail (
            Log_ID INT AUTO_INCREMENT PRIMARY KEY,
            User_ID VARCHAR(255),
            User_Role VARCHAR(100),
            Action_Type VARCHAR(100),
            Action_Details TEXT,
            Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($queries as $query) {
        $pdo->exec($query);
    }

    // Insert default admin (Password: Admin123)
    $stmt = $pdo->prepare("INSERT IGNORE INTO Officers (User_ID, Role, Access_Level, Email, Password, Name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['ADMIN-001', 'Admin', 3, 'admin@school.edu', password_hash('Admin123', PASSWORD_DEFAULT), 'System Administrator']);

    echo "Database initialized successfully.\n";

} catch (Exception $e) {
    die("Error initializing database: " . $e->getMessage());
}
