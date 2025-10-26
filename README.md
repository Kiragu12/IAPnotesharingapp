# NotesShare Academy 📚

A modern PHP-based note sharing application with secure authentication and two-factor verification.

## 🚀 Features

- **User Authentication System**
  - Secure user registration with email verification
  - Login with two-factor authentication (2FA)
  - Password reset functionality
  - Remember me functionality
  - Session management

- **Modern UI/UX**
  - Responsive Bootstrap 5 design
  - Beautiful gradient backgrounds
  - Glass-morphism effects
  - Real-time form validation
  - Loading states and animations

- **Security Features**
  - Password strength validation
  - Email verification
  - Two-factor authentication via email
  - Secure session handling
  - CSRF protection
  - SQL injection prevention

## 📋 System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHPMailer (included via Composer)

## 🗄️ Database Structure

The application uses the following main tables:
- `users` - User account information
- `two_factor_codes` - 2FA verification codes
- `remember_tokens` - Remember me functionality
- `password_resets` - Password reset tokens

## 📁 Project Structure

```
IAPnotesharingapp/
├── auth.php                    # Authentication processing
├── ClassAutoLoad.php          # Class autoloader
├── composer.json              # Dependencies
├── conf.php                   # Configuration file
├── dashboard.php              # User dashboard
├── index.php                  # Landing page
├── signin.php                 # User login page
├── signup.php                 # User registration page
├── two_factor_auth_new.php    # 2FA verification page
├── verify.php                 # Email verification
├── forgot_password.php        # Password reset request
├── reset_password.php         # Password reset form
├── logout.php                 # User logout
├── css/                       # Stylesheets
├── js/                        # JavaScript files
├── Forms/                     # Form components
├── Global/                    # Core classes
│   ├── Database.php           # Database handling
│   ├── fncs.php              # Utility functions
│   ├── providers.php         # Auth providers
│   └── SendMail.php          # Email functionality
├── Lang/                      # Language files
├── Layouts/                   # Layout templates
├── Proc/                      # Processing files
└── vendor/                    # Composer dependencies
```

## 🔧 Setup Instructions

1. **Database Setup**
   - Import the SQL files to create required tables
   - Update database credentials in `conf.php`

2. **Email Configuration**
   - Configure SMTP settings in `conf.php`
   - Update sender email credentials

3. **Web Server**
   - Point document root to project folder
   - Ensure mod_rewrite is enabled (Apache)

## 🎨 Authentication Flow

1. **User Registration**
   - User fills signup form
   - Email verification sent
   - Account activated upon verification

2. **User Login**
   - User enters credentials
   - 2FA code sent to email
   - Access granted after code verification

3. **Password Reset**
   - User requests password reset
   - Reset link sent to email
   - New password set via secure form

## 🔒 Security Best Practices

- All user inputs are sanitized and validated
- Passwords are hashed using secure algorithms
- Sessions are properly managed
- Email verification prevents fake accounts
- 2FA adds extra security layer
- Remember tokens are securely generated

## 🎯 Testing

The system has been thoroughly tested with:
- User registration flow
- Login and 2FA verification
- Password reset functionality
- Email delivery
- Form validation
- Security measures

## 📞 Support

For technical support or questions about the application, please refer to the code documentation or contact the development team.

---

**Status**: ✅ Production Ready
**Last Updated**: <?php echo date('Y-m-d H:i:s'); ?>
