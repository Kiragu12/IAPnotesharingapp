-- =====================================================
-- COMPLETE 2FA DATABASE TABLES ANALYSIS
-- Tables needed for Two-Factor Authentication system
-- =====================================================

/*
CURRENT TABLES WE HAVE:
✅ users (with verification_code, code_expiry)
✅ password_resets 
✅ remember_tokens
✅ activity_log

WHAT WE NEED FOR COMPLETE 2FA:
*/

-- =====================================================
-- 1. 2FA SETTINGS TABLE (User 2FA Preferences)
-- =====================================================
CREATE TABLE user_2fa_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    is_2fa_enabled BOOLEAN DEFAULT FALSE,
    preferred_method ENUM('email', 'sms', 'app') DEFAULT 'email',
    backup_email VARCHAR(255),
    phone_number VARCHAR(20),
    secret_key VARCHAR(255), -- For TOTP apps like Google Authenticator
    backup_codes JSON, -- Array of one-time backup codes
    last_used_method VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_2fa (user_id),
    INDEX idx_enabled (is_2fa_enabled)
);

-- =====================================================
-- 2. 2FA CODES TABLE (Active OTP codes)
-- =====================================================
CREATE TABLE two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    code_type ENUM('login', 'setup', 'backup_verification') DEFAULT 'login',
    delivery_method ENUM('email', 'sms') DEFAULT 'email',
    delivery_address VARCHAR(255), -- email or phone where code was sent
    attempts_used INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code),
    INDEX idx_expires (expires_at),
    INDEX idx_code_type (code_type),
    UNIQUE KEY unique_active_code (user_id, code_type) -- Only one active code per type per user
);

-- =====================================================
-- 3. 2FA TRUSTED DEVICES (Skip 2FA for trusted devices)
-- =====================================================
CREATE TABLE trusted_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL, -- Browser/device hash
    device_name VARCHAR(255), -- User-friendly name
    browser_info VARCHAR(500),
    os_info VARCHAR(255),
    ip_address VARCHAR(45),
    location VARCHAR(255), -- City, Country
    is_active BOOLEAN DEFAULT TRUE,
    trust_expires_at TIMESTAMP NULL, -- Optional expiration (e.g., 30 days)
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_device (user_id, device_fingerprint),
    INDEX idx_active (is_active),
    INDEX idx_expires (trust_expires_at),
    UNIQUE KEY unique_user_device (user_id, device_fingerprint)
);

-- =====================================================
-- 4. 2FA AUDIT LOG (Security events tracking)
-- =====================================================
CREATE TABLE two_factor_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_type ENUM(
        '2fa_enabled', '2fa_disabled', 'code_sent', 'code_verified', 
        'code_failed', 'backup_code_used', 'device_trusted', 
        'device_removed', 'suspicious_activity'
    ) NOT NULL,
    method_used ENUM('email', 'sms', 'app', 'backup_code') NULL,
    success BOOLEAN DEFAULT FALSE,
    failure_reason VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    location VARCHAR(255),
    additional_data JSON, -- Extra context data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_events (user_id, created_at),
    INDEX idx_event_type (event_type),
    INDEX idx_success (success),
    INDEX idx_created (created_at)
);

-- =====================================================
-- 5. BACKUP CODES TABLE (One-time recovery codes)
-- =====================================================
CREATE TABLE backup_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    code_hash VARCHAR(255) NOT NULL, -- Hashed version for security
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    used_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_codes (user_id),
    INDEX idx_code_hash (code_hash),
    INDEX idx_unused (is_used)
);

-- =====================================================
-- 6. ENHANCED USERS TABLE (Add 2FA columns)
-- =====================================================
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS two_factor_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS backup_codes_generated_at TIMESTAMP NULL,
ADD INDEX IF NOT EXISTS idx_2fa_enabled (two_factor_enabled);

-- =====================================================
-- VIEWS FOR 2FA SYSTEM
-- =====================================================

-- User 2FA Status View
CREATE VIEW user_2fa_status AS
SELECT 
    u.id,
    u.email,
    u.full_name,
    u.two_factor_enabled,
    ufs.preferred_method,
    ufs.is_2fa_enabled as settings_enabled,
    COUNT(DISTINCT td.id) as trusted_devices_count,
    COUNT(DISTINCT bc.id) as backup_codes_available,
    MAX(tfa.created_at) as last_2fa_activity
FROM users u
LEFT JOIN user_2fa_settings ufs ON u.id = ufs.user_id
LEFT JOIN trusted_devices td ON u.id = td.user_id AND td.is_active = TRUE
LEFT JOIN backup_codes bc ON u.id = bc.user_id AND bc.is_used = FALSE
LEFT JOIN two_factor_audit_log tfa ON u.id = tfa.user_id
GROUP BY u.id;

-- Active 2FA Sessions View
CREATE VIEW active_2fa_sessions AS
SELECT 
    tfc.id,
    tfc.user_id,
    u.email,
    tfc.code_type,
    tfc.delivery_method,
    tfc.attempts_used,
    tfc.max_attempts,
    tfc.expires_at,
    TIMESTAMPDIFF(MINUTE, NOW(), tfc.expires_at) as minutes_remaining
FROM two_factor_codes tfc
JOIN users u ON tfc.user_id = u.id
WHERE tfc.expires_at > NOW() 
  AND tfc.used_at IS NULL
  AND tfc.attempts_used < tfc.max_attempts;

-- =====================================================
-- TRIGGERS FOR 2FA SYSTEM
-- =====================================================

-- Auto-cleanup expired codes
DELIMITER $$
CREATE EVENT IF NOT EXISTS cleanup_expired_2fa_codes
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM two_factor_codes WHERE expires_at < NOW();
    DELETE FROM trusted_devices WHERE trust_expires_at IS NOT NULL AND trust_expires_at < NOW();
END$$
DELIMITER ;

-- Log 2FA events automatically
DELIMITER $$
CREATE TRIGGER log_2fa_code_verification
AFTER UPDATE ON two_factor_codes
FOR EACH ROW
BEGIN
    IF NEW.used_at IS NOT NULL AND OLD.used_at IS NULL THEN
        INSERT INTO two_factor_audit_log (
            user_id, event_type, method_used, success, ip_address, created_at
        ) VALUES (
            NEW.user_id, 'code_verified', NEW.delivery_method, TRUE, NEW.ip_address, NOW()
        );
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- SAMPLE DATA FOR TESTING
-- =====================================================

-- Enable 2FA for admin user
INSERT INTO user_2fa_settings (user_id, is_2fa_enabled, preferred_method) 
VALUES (1, TRUE, 'email') 
ON DUPLICATE KEY UPDATE is_2fa_enabled = TRUE;

-- Generate sample backup codes for admin
INSERT INTO backup_codes (user_id, code, code_hash) VALUES 
(1, 'BACKUP001', SHA2('BACKUP001salt', 256)),
(1, 'BACKUP002', SHA2('BACKUP002salt', 256)),
(1, 'BACKUP003', SHA2('BACKUP003salt', 256)),
(1, 'BACKUP004', SHA2('BACKUP004salt', 256)),
(1, 'BACKUP005', SHA2('BACKUP005salt', 256));

-- =====================================================
-- SUMMARY OF WHAT WE HAVE VS WHAT WE NEED
-- =====================================================

/*
✅ ALREADY IMPLEMENTED:
- Basic email OTP (using users.verification_code)
- Password reset tokens
- Remember me functionality
- Activity logging

🆕 ADDITIONAL TABLES NEEDED FOR COMPLETE 2FA:
1. user_2fa_settings - User preferences and configuration
2. two_factor_codes - Active OTP codes with tracking
3. trusted_devices - Skip 2FA for known devices  
4. two_factor_audit_log - Security events tracking
5. backup_codes - Recovery codes for emergencies

🔐 ENHANCED FEATURES THIS ENABLES:
- Multiple 2FA methods (Email, SMS, TOTP apps)
- Trusted device management
- Backup recovery codes
- Comprehensive audit logging
- Rate limiting and attempt tracking
- Device fingerprinting
- Suspicious activity detection
- Automatic cleanup of expired data

💡 CURRENT STATUS:
We have BASIC 2FA working with email OTP.
These additional tables would give us ENTERPRISE-LEVEL 2FA security.
*/

SELECT 'Complete 2FA database schema analysis completed!' as status;
SELECT '5 additional tables recommended for enterprise 2FA' as recommendation;