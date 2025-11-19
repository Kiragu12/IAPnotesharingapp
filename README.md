# Note Sharing Application 📚

A secure web application for sharing notes with advanced authentication features including 2FA (Two-Factor Authentication), built with PHP and MySQL.

## 🚀 Features

- **User Registration & Authentication**: Secure signup and signin process.
- **Two-Factor Authentication (2FA)**: Email-based OTP verification for enhanced security.
- **Enhanced Notes Management**: Create, edit, view, and organize both text notes and file uploads
- **File Upload Support**: Upload documents (PDF, DOC, DOCX), images (JPG, PNG, GIF), and spreadsheets (XLS, XLSX)
- **Dual Note Types**: Text notes with rich content or file uploads with descriptions
- **Categories & Tags**: Organize notes with categories and tags for easy discovery
- **Public/Private Notes**: Share notes publicly or keep them private
- **Search & Filter**: Find notes quickly with search and category filters
- **Drag & Drop Upload**: Modern file upload interface with drag-and-drop support
- **Session Management**: Secure session handling with logout functionality
- **Responsive Design**: Bootstrap-based UI that works on all devices
- **Email Integration**: PHPMailer for sending OTP codes and notifications
- **Professional Architecture**: Clean MVC structure with organized file hierarchy

## 📋 Prerequisites

- **Web Server**: Apache with PHP support
- **PHP**: Version 7.4 or higher
- **MySQL**: Database server
- **Composer**: For dependency management
- **Valid Email Configuration**: For 2FA functionality

## 🗄️ Database Structure

The application uses the following main tables:
- `users` - User account information
- `two_factor_codes` - 2FA verification codes
- `remember_tokens` - Remember me functionality
- `password_resets` - Password reset tokens
- `notes` - Enhanced table supporting both text notes and file uploads
  - Text fields: `title`, `content`, `summary`, `tags`
  - File fields: `file_path`, `file_name`, `file_type`, `file_size`
  - Type field: `note_type` (text/file)
- `categories` - Note categories and organization

## 📁 Project Structure

```
IAPnotesharingapp/
├── app/
│   ├── Controllers/            # Application controllers (Notes, etc.)
│   ├── Controllers/Proc/       # Authentication logic
│   └── Services/Global/        # Core services (Database, SendMail, etc.)
├── config/                     # Configuration files
├── public/                     # Public assets (CSS, JS, images)
├── sql/                        # Database schema and setup
├── uploads/                    # File upload storage
│   ├── documents/              # Document uploads (PDF, DOC, etc.)
│   └── images/                 # Image uploads (JPG, PNG, etc.)
├── vendor/                     # Composer dependencies
├── views/                      # Frontend templates
│   ├── auth/                   # Authentication pages
│   ├── notes/                  # Notes management pages
│   └── index.php               # Homepage
├── composer.json               # Dependencies
├── debug.log                   # Application logs
└── README.md                   # This file
```

## 🌐 Testing URLs

Use these URLs to test the complete application flow in your browser:

### 1. Homepage (Entry Point)
```
http://localhost/IAPnotesharingapp/views/index.php
```
- **Purpose**: Main landing page with navigation options
- **Features**: Login/Signup buttons, application overview
- **Test**: Verify all buttons navigate correctly

### 2. User Registration
```
http://localhost/IAPnotesharingapp/views/auth/signup.php
```
- **Purpose**: New user account creation
- **Process**: Fill form → Submit → Success page → Link to signin
- **Test Data**: Use valid email domain (check `config/conf.php` for allowed domains)
- **Expected**: Account creation success message and signin redirect

### 3. User Sign-In
```
http://localhost/IAPnotesharingapp/views/auth/signin.php
```
- **Purpose**: User authentication with 2FA initiation
- **Process**: Enter credentials → Submit → 2FA code sent → Redirect to verification
- **Test**: Use credentials from registration step
- **Expected**: "2FA code sent" message and automatic redirect

### 4. Two-Factor Authentication
```
http://localhost/IAPnotesharingapp/views/auth/two_factor_auth_new.php
```
- **Purpose**: OTP verification for secure login
- **Process**: Enter 6-digit code from email → Verify → Dashboard access
- **Test**: Check email for OTP code or use debug mode
- **Expected**: Dashboard access upon successful verification

### 5. Dashboard (Protected Area)
```
http://localhost/IAPnotesharingapp/views/dashboard.php
```
- **Purpose**: Main application interface (requires authentication)
- **Features**: User session display, logout functionality
- **Access**: Only available after successful 2FA verification
- **Expected**: User information display and working logout button

### 6. Dashboard Preview (Public)
```
http://localhost/IAPnotesharingapp/views/auth/dashboard-preview.php
```
- **Purpose**: Preview of dashboard features for non-authenticated users
- **Features**: Demo interface, signup/signin links
- **Access**: Public access, no authentication required
- **Expected**: Preview interface with call-to-action buttons

### 7. Logout
```
http://localhost/IAPnotesharingapp/views/logout.php
```
- **Purpose**: Session termination and cleanup
- **Process**: Clear session → Clear remember tokens → Redirect to home
- **Expected**: Successful logout message and redirect to homepage

### 8. Create Note (Enhanced with File Upload)
```
http://localhost/IAPnotesharingapp/views/notes/create.php
```
- **Purpose**: Create text notes OR upload files with descriptions
- **Features**: 
  - **Text Notes**: Rich content writing with summary
  - **File Upload**: Drag-and-drop upload for documents, PDFs, images
  - Categories, tags, public/private settings for both note types
- **Supported Files**: PDF, DOC, DOCX, TXT, JPG, PNG, GIF, XLS, XLSX, PPT, PPTX
- **File Limit**: 10MB maximum per file
- **Access**: Requires authentication
- **Expected**: Dual-mode interface with note type selection

### 9. My Notes
```
http://localhost/IAPnotesharingapp/views/notes/my-notes.php
```
- **Purpose**: View and manage user's notes
- **Features**: Search, filter, edit, delete notes
- **Access**: Requires authentication
- **Expected**: Grid view of user's notes with actions

### 10. View Note
```
http://localhost/IAPnotesharingapp/views/notes/view.php?id=1
```
- **Purpose**: Display single note with full content
- **Features**: Note content, statistics, edit options (for owner)
- **Access**: Public notes accessible to all, private notes only to owner
- **Expected**: Formatted note display with navigation

## � Complete Testing Flow

### Step-by-Step Authentication Test:

1. **Start at Homepage**
   - Visit: `http://localhost/IAPnotesharingapp/views/index.php`
   - Click "Get Started" or "Sign Up" button

2. **Register New Account**
   - URL: `http://localhost/IAPnotesharingapp/views/auth/signup.php`
   - Fill form with valid data
   - Use email domain from allowed list in config
   - Submit and verify success message

3. **Sign In with New Account**
   - URL: `http://localhost/IAPnotesharingapp/views/auth/signin.php`
   - Enter registration credentials
   - Submit and verify 2FA initiation message

4. **Complete 2FA Verification**
   - URL: `http://localhost/IAPnotesharingapp/views/auth/two_factor_auth_new.php`
   - Check email for 6-digit OTP code
   - Enter code and verify successful login

5. **Access Dashboard**
   - URL: `http://localhost/IAPnotesharingapp/views/dashboard.php`
   - Verify user session information
   - Test navigation and features

6. **Test Logout**
   - Click logout button or visit: `http://localhost/IAPnotesharingapp/views/logout.php`
   - Verify session cleanup and redirect

7. **Verify Security**
   - Try accessing dashboard after logout (should redirect to signin)
   - Test "Back to Home" buttons on all auth pages

### Step-by-Step Notes Testing:

8. **Create Your First Note**
   - URL: `http://localhost/IAPnotesharingapp/views/notes/create.php`
   - **Choose Note Type**: Text Note or File Upload
   - **For Text Notes**: Fill in title and content (required fields)
   - **For File Upload**: Upload file and provide description (required fields)
   - Select a category (optional)
   - Add tags (optional)
   - Choose public/private setting
   - Click "Create Note"
   - **Expected**: Success message and note creation confirmation

9. **View Your Notes**
   - URL: `http://localhost/IAPnotesharingapp/views/notes/my-notes.php`
   - **Expected**: See your created note in the grid
   - Test search functionality
   - Test filter options (All/Published/Drafts)

10. **Edit a Note**
    - Click on a note from "My Notes"
    - Click "Edit" from the dropdown menu
    - Modify content and save
    - **Expected**: See "Note updated successfully" message

## 🛠️ Installation & Setup

1. **Clone or download** this repository to your web server directory
2. **Install dependencies**: Run `composer install` in the project root
3. **Configure database**: Update `config/conf.php` with your database credentials
4. **Import database**: Run the SQL files in the `sql/` directory
5. **Configure email**: Set up your SMTP settings in the configuration file

## 🔧 Troubleshooting

### Common Issues:

1. **SendMail Errors**: Check SMTP configuration in `config/conf.php`
2. **Database Connection**: Verify credentials and database existence
3. **2FA Not Working**: Check email service and debug logs
4. **Navigation Issues**: Ensure all relative paths are correct
5. **Session Problems**: Clear browser cache and cookies

### Debug Mode:

- Check `debug.log` for detailed error information
- Enable debug mode in configuration for verbose logging
- Use browser developer tools to inspect network requests

## 🔒 Security Features

- **Password Hashing**: Secure password storage with PHP's built-in functions
- **Session Security**: Proper session management and cleanup
- **Input Validation**: Server-side validation for all user inputs
- **Email Verification**: 2FA via email for account security
- **CSRF Protection**: Form security measures
- **SQL Injection Prevention**: Prepared statements for database queries

## ⚙️ Configuration

Key configuration files:
- `config/conf.php` - Main application configuration
- `config/Lang/en.php` - Language settings
- `composer.json` - Dependencies and autoloading

## 📦 Dependencies

- **PHPMailer**: Email functionality for 2FA
- **Bootstrap 5.3.0**: Responsive UI framework
- **Bootstrap Icons**: Icon library for UI elements

## 💻 Development Notes

This application follows professional PHP development practices:
- Clean MVC architecture
- Proper error handling and logging
- Secure authentication flow
- Responsive design principles
- Comprehensive testing capabilities

## 🎯 Testing

The system has been thoroughly tested with:
- Complete authentication flow (signup → signin → 2FA → dashboard → logout)
- Email delivery for 2FA codes
- Form validation and security measures
- Navigation and user interface functionality
- Session management and security

## 📞 Support

For issues or questions:
1. Check the debug logs in `debug.log`
2. Verify configuration settings
3. Test each URL individually using the provided testing guide
4. Ensure all dependencies are properly installed

---

**Status**: ✅ Production Ready with Enhanced File Upload Support  
**Last Updated**: November 10, 2025  
**Architecture**: PHP MVC with Bootstrap Frontend and File Upload System
