# Database Schema Documentation

## IAP Note Sharing App - Database Structure

---

## 📊 Database Overview

**Database Name:** `iap_notesharing`  
**Tables:** 4  
**Engine:** InnoDB  
**Charset:** utf8mb4_unicode_ci

---

## 📋 Tables Summary

| # | Table Name | Records | Purpose |
|---|-----------|---------|---------|
| 1 | `users` | Dynamic | User accounts and profiles |
| 2 | `two_factor_codes` | Dynamic | OTP codes for 2FA verification |
| 3 | `remember_tokens` | Dynamic | Persistent login tokens |
| 4 | `password_resets` | Dynamic | Password reset tokens |

---

## 🔐 Table 1: users

**Purpose:** Stores user account information including credentials and profile data.

### Structure

| Column | Type | Null | Key | Default | Description |
|--------|------|------|-----|---------|-------------|
| `id` | INT | NO | PRI | AUTO_INCREMENT | Unique user identifier |
| `email` | VARCHAR(255) | NO | UNI | - | User's email (login ID) |
| `password` | VARCHAR(255) | NO | - | - | Hashed password (bcrypt) |
| `full_name` | VARCHAR(255) | NO | - | - | User's full name |
| `phone` | VARCHAR(20) | YES | - | NULL | Phone number (optional) |
| `profile_picture` | VARCHAR(500) | YES | - | NULL | Profile image URL/path |
| `email_verified` | BOOLEAN | NO | - | FALSE | Email verification status |
| `is_2fa_enabled` | BOOLEAN | NO | - | TRUE | 2FA requirement flag |
| `is_active` | BOOLEAN | NO | IDX | TRUE | Account active status |
| `is_admin` | BOOLEAN | NO | - | FALSE | Admin privileges flag |
| `created_at` | TIMESTAMP | NO | - | CURRENT_TIMESTAMP | Account creation time |
| `updated_at` | TIMESTAMP | NO | - | CURRENT_TIMESTAMP | Last update time |

### Indexes
- **PRIMARY KEY:** `id`
- **UNIQUE:** `email`
- **INDEX:** `idx_email` (email)
- **INDEX:** `idx_active` (is_active)

### Sample Data
```sql
INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled)
VALUES (
    'john@example.com',
    '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890',  -- Hashed password
    'John Doe',
    TRUE,
    TRUE
);
```

### Relationships
- **Referenced by:** `two_factor_codes.user_id`
- **Referenced by:** `remember_tokens.user_id`
- **Referenced by:** `password_resets.user_id`

---

## 🔑 Table 2: two_factor_codes

**Purpose:** Stores OTP (One-Time Password) codes for two-factor authentication during login.

### Structure

| Column | Type | Null | Key | Default | Description |
|--------|------|------|-----|---------|-------------|
| `id` | INT | NO | PRI | AUTO_INCREMENT | Unique code identifier |
| `user_id` | INT | NO | FOR | - | Reference to users table |
| `code` | VARCHAR(10) | NO | IDX | - | 6-digit OTP code |
| `code_type` | ENUM | NO | - | 'login' | Type: login/setup/password_reset |
| `attempts_used` | INT | NO | - | 0 | Failed verification attempts |
| `max_attempts` | INT | NO | - | 3 | Maximum allowed attempts |
| `expires_at` | TIMESTAMP | NO | IDX | - | Code expiration time (5 min) |
| `used_at` | TIMESTAMP | YES | IDX | NULL | When code was used |
| `ip_address` | VARCHAR(45) | YES | - | NULL | Requester's IP |
| `created_at` | TIMESTAMP | NO | - | CURRENT_TIMESTAMP | Code generation time |

### Indexes
- **PRIMARY KEY:** `id`
- **FOREIGN KEY:** `user_id` → `users(id)` ON DELETE CASCADE
- **INDEX:** `idx_user_code` (user_id, code)
- **INDEX:** `idx_expires` (expires_at)
- **INDEX:** `idx_used` (used_at)

### Sample Data
```sql
INSERT INTO two_factor_codes (user_id, code, expires_at)
VALUES (
    1,
    '123456',
    DATE_ADD(NOW(), INTERVAL 5 MINUTE)
);
```

### Lifecycle
1. **Generated** when user signs in (expires in 5 minutes)
2. **Verified** when user enters code
3. **Marked used** (`used_at` set) on successful verification
4. **Expired** codes are not valid (checked via `expires_at > NOW()`)

### Important Notes
- Codes are **single-use** (checked via `used_at IS NULL`)
- Max **3 attempts** per code (checked via `attempts_used < max_attempts`)
- Old codes for same user are marked as used when new code generated

---

## 🍪 Table 3: remember_tokens

**Purpose:** Stores secure tokens for "Remember Me" functionality (persistent login).

### Structure

| Column | Type | Null | Key | Default | Description |
|--------|------|------|-----|---------|-------------|
| `id` | INT | NO | PRI | AUTO_INCREMENT | Unique token identifier |
| `user_id` | INT | NO | FOR | - | Reference to users table |
| `token` | VARCHAR(255) | NO | UNI | - | 64-char random token |
| `expires_at` | TIMESTAMP | NO | IDX | - | Token expiration (30 days) |
| `device_info` | VARCHAR(500) | YES | - | NULL | User agent string |
| `ip_address` | VARCHAR(45) | YES | - | NULL | IP address |
| `created_at` | TIMESTAMP | NO | - | CURRENT_TIMESTAMP | Token creation time |
| `last_used` | TIMESTAMP | NO | - | CURRENT_TIMESTAMP | Last auto-login time |

### Indexes
- **PRIMARY KEY:** `id`
- **FOREIGN KEY:** `user_id` → `users(id)` ON DELETE CASCADE
- **UNIQUE:** `token`
- **INDEX:** `idx_token` (token)
- **INDEX:** `idx_user_id` (user_id)
- **INDEX:** `idx_expires` (expires_at)

### Sample Data
```sql
INSERT INTO remember_tokens (user_id, token, expires_at, device_info, ip_address)
VALUES (
    1,
    'a1b2c3d4e5f6...64_character_random_string',
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64)...',
    '192.168.1.100'
);
```

### Lifecycle
1. **Created** when user signs in with "Remember Me" checked
2. **Stored as cookie** in user's browser (30-day expiration)
3. **Validated** on each visit via `ClassAutoLoad.php`
4. **Auto-login** if token is valid and not expired
5. **Refreshed** (`last_used` updated) on each use
6. **Deleted** when:
   - User signs out
   - Token expires
   - User changes password

### Security Features
- **Secure random tokens** (`bin2hex(random_bytes(32))`)
- **Device tracking** (user agent and IP)
- **Expiration enforcement** (30 days)
- **HttpOnly cookies** (not accessible via JavaScript)

---

## 🔄 Table 4: password_resets

**Purpose:** Stores tokens for password reset requests via email.

### Structure

| Column | Type | Null | Key | Default | Description |
|--------|------|------|-----|---------|-------------|
| `id` | INT | NO | PRI | AUTO_INCREMENT | Unique reset identifier |
| `user_id` | INT | NO | FOR | - | Reference to users table |
| `token` | VARCHAR(255) | NO | UNI | - | Secure reset token |
| `expires_at` | TIMESTAMP | NO | IDX | - | Token expiration (1 hour) |
| `used_at` | TIMESTAMP | YES | - | NULL | When token was used |
| `created_at` | TIMESTAMP | NO | - | CURRENT_TIMESTAMP | Request creation time |

### Indexes
- **PRIMARY KEY:** `id`
- **FOREIGN KEY:** `user_id` → `users(id)` ON DELETE CASCADE
- **UNIQUE:** `token`
- **INDEX:** `idx_token` (token)
- **INDEX:** `idx_user_id` (user_id)
- **INDEX:** `idx_expires` (expires_at)

### Sample Data
```sql
INSERT INTO password_resets (user_id, token, expires_at)
VALUES (
    1,
    'abc123def456...secure_random_token',
    DATE_ADD(NOW(), INTERVAL 1 HOUR)
);
```

### Lifecycle
1. **Created** when user requests password reset via `forgot_password.php`
2. **Email sent** with reset link containing token
3. **Validated** when user clicks link in `reset_password.php`
4. **Used** when user submits new password
5. **Marked used** (`used_at` set) after successful reset
6. **Expired** after 1 hour (not usable)

---

## 🔗 Entity Relationships

```
┌─────────────────┐
│     users       │
│─────────────────│
│ id (PK)         │◄────┐
│ email           │     │
│ password        │     │ One-to-Many
│ full_name       │     │
│ ...             │     │
└─────────────────┘     │
                        │
       ┌────────────────┼────────────────┬──────────────────┐
       │                │                │                  │
       │                │                │                  │
┌──────▼──────────┐  ┌─▼──────────────┐ ┌▼────────────────┐ ┌▼────────────────┐
│ two_factor_     │  │ remember_      │ │ password_       │ │ (future tables) │
│ codes           │  │ tokens         │ │ resets          │ │                 │
│─────────────────│  │────────────────│ │─────────────────│ │─────────────────│
│ id (PK)         │  │ id (PK)        │ │ id (PK)         │ │ ...             │
│ user_id (FK)    │  │ user_id (FK)   │ │ user_id (FK)    │ │                 │
│ code            │  │ token          │ │ token           │ │                 │
│ expires_at      │  │ expires_at     │ │ expires_at      │ │                 │
│ ...             │  │ ...            │ │ ...             │ │                 │
└─────────────────┘  └────────────────┘ └─────────────────┘ └─────────────────┘
```

---

## 📝 Common Queries

### Get User with Active 2FA Code
```sql
SELECT u.*, tfc.code, tfc.expires_at
FROM users u
JOIN two_factor_codes tfc ON u.id = tfc.user_id
WHERE u.email = 'user@example.com'
  AND tfc.expires_at > NOW()
  AND tfc.used_at IS NULL
ORDER BY tfc.created_at DESC
LIMIT 1;
```

### Validate Remember Token
```sql
SELECT rt.*, u.email, u.full_name
FROM remember_tokens rt
JOIN users u ON rt.user_id = u.id
WHERE rt.token = 'token_string_here'
  AND rt.expires_at > NOW()
LIMIT 1;
```

### Check Valid Password Reset Token
```sql
SELECT pr.*, u.email
FROM password_resets pr
JOIN users u ON pr.user_id = u.id
WHERE pr.token = 'reset_token_here'
  AND pr.expires_at > NOW()
  AND pr.used_at IS NULL
LIMIT 1;
```

### Cleanup Expired Records
```sql
-- Delete expired 2FA codes (older than 1 day)
DELETE FROM two_factor_codes
WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Delete expired remember tokens
DELETE FROM remember_tokens
WHERE expires_at < NOW();

-- Delete used or expired password resets (older than 7 days)
DELETE FROM password_resets
WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
   OR used_at IS NOT NULL;
```

---

## 🛡️ Security Considerations

### Password Storage
- ✅ **Never** store plain-text passwords
- ✅ Use `password_hash($password, PASSWORD_DEFAULT)` in PHP
- ✅ Use `password_verify($input, $hash)` to check passwords
- ✅ Current algorithm: bcrypt (cost: 10)

### Token Generation
- ✅ Use `bin2hex(random_bytes(32))` for 64-character tokens
- ✅ Use cryptographically secure random generation
- ✅ Never use predictable patterns (timestamps, user IDs, etc.)

### Expiration Times
- **2FA Codes:** 5 minutes (short-lived, one-time use)
- **Remember Tokens:** 30 days (persistent login)
- **Password Reset:** 1 hour (security vs. usability balance)

### Indexes for Performance
- ✅ All foreign keys have indexes
- ✅ Frequently queried columns have indexes
- ✅ Unique constraints on tokens prevent duplicates

---

## 🔄 Maintenance Tasks

### Regular Cleanup (Recommended: Daily Cron Job)
```sql
-- Clean up old 2FA codes
DELETE FROM two_factor_codes WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Clean up expired tokens
DELETE FROM remember_tokens WHERE expires_at < NOW();
DELETE FROM password_resets WHERE expires_at < NOW() OR used_at IS NOT NULL;
```

### Monitoring Queries
```sql
-- Active users count
SELECT COUNT(*) FROM users WHERE is_active = TRUE;

-- Pending 2FA verifications
SELECT COUNT(*) FROM two_factor_codes WHERE used_at IS NULL AND expires_at > NOW();

-- Active remember me sessions
SELECT COUNT(*) FROM remember_tokens WHERE expires_at > NOW();

-- Table sizes
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'iap_notesharing';
```

---

**Last Updated:** October 23, 2025  
**Schema Version:** 1.0.0  
**Compatible With:** MySQL 5.7+, MariaDB 10.2+
