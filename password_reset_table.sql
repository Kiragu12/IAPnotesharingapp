-- =====================================================
-- PASSWORD RESET TABLE
-- Table for storing password reset tokens
-- =====================================================

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at),
    UNIQUE KEY unique_user_reset (user_id)  -- Only one active reset per user
);

-- Add verification columns to users table if they don't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verification_code VARCHAR(10),
ADD COLUMN IF NOT EXISTS code_expiry TIMESTAMP NULL,
ADD INDEX IF NOT EXISTS idx_verification_code (verification_code);

SELECT 'Password reset table created successfully!' as status;