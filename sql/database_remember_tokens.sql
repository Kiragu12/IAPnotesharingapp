-- Remember Me Tokens Database Table
-- Run this SQL to create the remember_tokens table

CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_info VARCHAR(500),
    ip_address VARCHAR(45),
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at)
);

-- Example data (don't run this, just for reference)
-- INSERT INTO remember_tokens (user_id, token, expires_at, device_info, ip_address) 
-- VALUES (1, 'abc123def456...', DATE_ADD(NOW(), INTERVAL 30 DAY), 'Chrome on Windows', '192.168.1.100');