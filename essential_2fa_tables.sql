-- =====================================================
-- ESSENTIAL 2FA TABLES - MINIMUM REQUIREMENTS
-- What you absolutely need for 2FA to work properly
-- =====================================================

/*
CURRENT STATUS:
✅ We already have basic 2FA working with these existing columns:
   - users.verification_code (stores the 6-digit OTP)
   - users.code_expiry (when the code expires)
   
✅ Our current 2FA flow works like this:
   1. User logs in → generates OTP → stores in verification_code
   2. User enters OTP → compares with verification_code
   3. If valid → logs user in → clears verification_code

HOWEVER, for PRODUCTION-READY 2FA, we need these additional tables:
*/

-- =====================================================
-- 1. TWO_FACTOR_CODES (Essential for proper 2FA)
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
-- 2. TRUSTED_DEVICES (Skip 2FA for known devices)
-- =====================================================
CREATE TABLE trusted_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    device_name VARCHAR(255),
    ip_address VARCHAR(45),
    trust_expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_device (user_id, device_fingerprint),
    UNIQUE KEY unique_user_device (user_id, device_fingerprint)
);

-- =====================================================
-- 3. BACKUP_CODES (Emergency access)
-- =====================================================
CREATE TABLE backup_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_codes (user_id),
    INDEX idx_unused (is_used)
);

-- =====================================================
-- 4. Add 2FA settings to users table
-- =====================================================
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_2fa_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS preferred_2fa_method ENUM('email', 'sms') DEFAULT 'email',
ADD INDEX IF NOT EXISTS idx_2fa_enabled (is_2fa_enabled);

-- =====================================================
-- WHAT EACH TABLE DOES:
-- =====================================================

/*
1. TWO_FACTOR_CODES:
   - Replaces using users.verification_code directly
   - Tracks attempts (prevent brute force)
   - Supports different code types
   - Better security and logging

2. TRUSTED_DEVICES:
   - Remember devices for 30 days
   - Skip 2FA on trusted devices
   - Better user experience

3. BACKUP_CODES:
   - Emergency access if phone/email lost
   - One-time use codes
   - Critical for account recovery

4. USERS table additions:
   - is_2fa_enabled: User can enable/disable 2FA
   - preferred_2fa_method: Email or SMS
*/

-- =====================================================
-- MINIMUM VIABLE 2FA (What we have now):
-- =====================================================

/*
CURRENT WORKING SETUP:
✅ users.verification_code - stores OTP
✅ users.code_expiry - OTP expiration
✅ two_factor_auth.php - OTP input page
✅ Email sending functionality

THIS WORKS FOR BASIC 2FA!

RECOMMENDED ADDITIONS (for production):
🔄 two_factor_codes table - better security
🔄 trusted_devices table - better UX  
🔄 backup_codes table - account recovery
*/

-- =====================================================
-- QUICK SUMMARY:
-- =====================================================

/*
MINIMUM TABLES NEEDED FOR 2FA:
1. ✅ users (with verification_code, code_expiry) - WE HAVE THIS
2. 🆕 two_factor_codes - FOR PRODUCTION SECURITY
3. 🆕 trusted_devices - FOR USER EXPERIENCE
4. 🆕 backup_codes - FOR ACCOUNT RECOVERY

CURRENT STATUS: Basic 2FA ✅ WORKING
RECOMMENDED: Add 3 tables for production-ready 2FA
*/

SELECT 'We have BASIC 2FA working, need 3 more tables for PRODUCTION 2FA' as status;