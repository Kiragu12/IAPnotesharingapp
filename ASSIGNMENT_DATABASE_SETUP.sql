-- =====================================================
-- BASIC DATABASE SETUP FOR 2FA DEVELOPMENT
-- Run this script after cloning the project
-- =====================================================

-- Create the database
DROP DATABASE IF EXISTS noteshare_db;
CREATE DATABASE noteshare_db;
USE noteshare_db;

-- =====================================================
-- USERS TABLE (core accounts + 2FA flags)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email_verified BOOLEAN DEFAULT FALSE,
    is_2fa_enabled BOOLEAN DEFAULT FALSE,
    preferred_2fa_method ENUM('email', 'sms') DEFAULT 'email',
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_2fa_enabled (is_2fa_enabled)
);

-- =====================================================
-- TWO FACTOR CODES TABLE (temporary verification codes)
-- =====================================================
CREATE TABLE two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    code_type ENUM('login', 'setup', 'password_reset') DEFAULT 'login',
    attempts_used INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code),
    INDEX idx_expires (expires_at)
);

-- =====================================================
-- PASSWORD RESET TOKENS (kept simple for now)
-- =====================================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
);

-- =====================================================
-- OPTIONAL TRUSTED DEVICE LIST (skip 2FA on known devices)
-- =====================================================
CREATE TABLE trusted_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    device_name VARCHAR(255),
    ip_address VARCHAR(45),
    trust_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_device (user_id, device_fingerprint)
);

-- =====================================================
-- STARTER DATA (password hashes = "Password123" hashed with bcrypt)
-- =====================================================
INSERT INTO users (email, password, full_name, phone, email_verified, is_admin, is_2fa_enabled) VALUES
('admin@noteshareacademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+1234567000', TRUE, TRUE, TRUE),
('student@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sample Student', '+1234567001', TRUE, FALSE, FALSE);

-- Insert one device trust row to illustrate usage (fingerprint is placeholder)
INSERT INTO trusted_devices (user_id, device_fingerprint, device_name, ip_address, trust_expires_at) VALUES
(1, 'demo-device-admin', 'Admin Laptop', '127.0.0.1', DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Insert a setup 2FA code for the student account (expires in 15 minutes)
INSERT INTO two_factor_codes (user_id, code, code_type, expires_at) VALUES
(2, '123456', 'setup', DATE_ADD(NOW(), INTERVAL 15 MINUTE));

-- =====================================================
-- QUICK VERIFICATIONS
-- =====================================================
SELECT 'Database setup completed for 2FA development.' AS status;
SELECT COUNT(*) AS total_tables FROM information_schema.tables WHERE table_schema = 'noteshare_db';
SHOW TABLES;