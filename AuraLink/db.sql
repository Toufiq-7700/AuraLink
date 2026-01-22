-- AuraLink Database Schema

CREATE DATABASE IF NOT EXISTS auralink_db;
USE auralink_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Mood Wall Table
CREATE TABLE IF NOT EXISTS mood_wall (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    start_color VARCHAR(7) NOT NULL,
    end_color VARCHAR(7) NOT NULL,
    pulse_intensity VARCHAR(20) DEFAULT 'pulse-medium',
    emoji VARCHAR(10),
    element_tone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 3. Sync Board Table
CREATE TABLE IF NOT EXISTS sync_games (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    result_status VARCHAR(20),
    partner_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
