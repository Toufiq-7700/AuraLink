<?php
require_once 'db_connect.php';

echo "<h2>Setting up AuraLink Database...</h2>";

try {
    // 0. Users Table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Checked/Created TABLE 'users'.<br>";

    // 1. Mood Wall Table
    $conn->exec("CREATE TABLE IF NOT EXISTS mood_wall (
        post_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        start_color VARCHAR(7) NOT NULL,
        end_color VARCHAR(7) NOT NULL,
        pulse_intensity VARCHAR(20) DEFAULT 'pulse-medium',
        emoji VARCHAR(10),
        element_tone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Checked/Created TABLE 'mood_wall'.<br>";

    $conn->exec("CREATE TABLE IF NOT EXISTS sync_games (
        game_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        result_status VARCHAR(20),
        partner_name VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add column if it doesn't exist (migrations style)
    try {
        $conn->exec("ALTER TABLE sync_games ADD COLUMN partner_name VARCHAR(50)");
    } catch (Exception $e) { /* Ignore if exists */
    }
    echo "Checked/Created TABLE 'sync_games'.<br>";

    echo "<h3>Setup Complete! <a href='index.php'>Go Home</a></h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>