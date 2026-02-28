# 🏨 Luxe Haven Hotel Management System

A comprehensive hotel booking management system built with PHP, MySQL, and Bootstrap. Features secure online reservations, client management, room management, and administrative dashboard with enterprise-grade security.

## 📋 Table of Contents

- [Features](#features)
- [Security](#security)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Installation & Setup](#installation--setup)
- [Configuration](#configuration)
- [Database Schema](#database-schema)
- [User Roles](#user-roles)
- [API Endpoints](#api-endpoints)
- [Troubleshooting](#troubleshooting)
- [Support](#support)

---

## ✨ Features

### 🎯 Core Functionality

#### **Client Features**

- ✅ User registration with email verification (OTP)
- ✅ Two-factor authentication (2FA) via email
- ✅ Secure login with failed attempt tracking
- ✅ Room browsing and detailed room information
- ✅ Online room booking with GCash payment integration
- ✅ Reservation management (view, extend, cancel)
- ✅ Guest inquiries submission
- ✅ Profile management and password updates
- ✅ Reservation history and PDF generation

#### **Admin Dashboard**

- ✅ Comprehensive dashboard with analytics
- ✅ Room management (add, edit, delete)
- ✅ Room category management
- ✅ Client account management
- ✅ Walk-in reservation booking
- ✅ Online reservation tracking
- ✅ Payment management and history
- ✅ Product/service management
- ✅ Request management
- ✅ Inquiry tracking and responses
- ✅ Site settings configuration
- ✅ PDF invoice generation

#### **Payment Integration**

- ✅ GCash payment processing
- ✅ Payment verification
- ✅ Transaction history
- ✅ Payment status tracking

---

## 🔒 Security Features

### Enterprise-Grade Security Implementation

#### **Authentication & Authorization**

- ✅ **Bcrypt Password Hashing** - Passwords stored with bcrypt algorithm
- ✅ **Argon2ID Hashing** - Upgrade path for sensitive password updates
- ✅ **Email OTP Verification** - Prevents unauthorized account access
- ✅ **Two-Factor Authentication (2FA)** - Optional email-based 2FA for extra security
- ✅ **Session Hardening** - Automatic timeout, regeneration, IP validation
- ✅ **Failed Login Tracking** - Automatic account blocking after 3 failed attempts
- ✅ **Rate Limiting** - Protection against brute force attacks

#### **Data Protection**

- ✅ **Prepared Statements** - All database queries use parameterized statements
- ✅ **CSRF Token Protection** - Cryptographic tokens on all state-changing forms
- ✅ **SQL Injection Prevention** - No dynamic SQL concatenation
- ✅ **File Upload Security** - MIME type validation, size limits, secure filenames
- ✅ **Environment Variables** - Credentials stored in `.env` (never in code)

#### **Session Security**

- ✅ **Session Timeout** - 30 minutes of inactivity
- ✅ **Session Regeneration** - Every 10 minutes automatically
- ✅ **IP Address Validation** - Detects session hijacking
- ✅ **HttpOnly Cookies** - Prevents XSS script access
- ✅ **SameSite=Strict** - Prevents CSRF-like attacks

#### **Input Validation**

- ✅ **Email Validation** - Format and existence verification
- ✅ **Type-Safe Binding** - Database parameters with strict type checking
- ✅ **HTML Output Escaping** - XSS prevention via `htmlspecialchars()`
- ✅ **File Upload Validation** - Extension whitelist and MIME checking

---

## 🛠️ Technology Stack

| Component              | Technology                  |
| ---------------------- | --------------------------- |
| **Scripting Language** | PHP 7.4+                    |
| **Database**           | MySQL 5.7+                  |
| **Frontend Framework** | Bootstrap 5                 |
| **Authentication**     | Native PHP Sessions         |
| **Password Hashing**   | bcrypt + Argon2ID           |
| **Email Service**      | PHPMailer (SMTP)            |
| **Payment Gateway**    | GCash API                   |
| **PDF Generation**     | TCPDF                       |
| **Version Control**    | Git + GitHub                |
| **Server**             | Apache 2.4 with mod_rewrite |

---

## 📁 Project Structure

```
Hotel/
├── admin/                          # Admin panel
│   ├── dashboard.php              # Main admin dashboard
│   ├── rooms.php                  # Room management
│   ├── category.php               # Room category management
│   ├── clients.php                # Client account management
│   ├── online_reservation.php     # Online bookings
│   ├── walkin_reservation.php     # Walk-in bookings
│   ├── payments.php               # Payment tracking
│   ├── products.php               # Product management
│   ├── services.php               # Service management
│   ├── inquiry.php                # Guest inquiries
│   ├── requests.php               # Service requests
│   ├── admin_settings.php         # Admin account settings
│   ├── config/
│   │   ├── config.php             # Database configuration
│   │   └── checklogin.php         # Admin authentication check
│   ├── inc/
│   │   ├── SessionManager.php     # Session security management
│   │   ├── CSRFToken.php          # CSRF token handling
│   │   ├── FileUploadHandler.php  # Secure file uploads
│   │   ├── password_helper.php    # Password hashing utilities
│   │   ├── email_2fa_helper.php   # 2FA email functions
│   │   ├── mailer_helper.php      # Email helper functions
│   │   ├── alert.php              # Alert display functions
│   │   ├── side_header.php        # Navigation sidebar
│   │   └── links.php              # CSS/JS includes
│   ├── ajax/                      # AJAX handlers
│   ├── fetch/                     # Data fetching endpoints
│   └── style/                     # Admin panel CSS
├── client/                         # Client portal
│   ├── index.php                  # Homepage
│   ├── login.php                  # Client login
│   ├── register.php               # Registration
│   ├── profile.php                # User profile
│   ├── profile_edit.php           # Edit profile
│   ├── room_category.php          # Browse by category
│   ├── room_details.php           # Room details page
│   ├── room_book.php              # Booking page
│   ├── otp.php                    # OTP verification
│   ├── verify_email_2fa.php       # 2FA verification
│   ├── forgot_password.php        # Password recovery
│   ├── reset_password_token.php   # Password reset
│   ├── send_inquiry.php           # Submit inquiry
│   ├── inc/                       # Client-side includes
│   ├── style/                     # Client CSS
│   └── dist/                      # Images and assets
├── PHPMailer/                      # Email library
├── vendor/                         # Composer dependencies
├── backup/sql/                     # Database backups
├── .env                           # Environment variables (NEVER commit)
├── .gitignore                     # Git ignore rules
├── composer.json                  # PHP dependencies
└── README.md                      # This file
```

---

## 📦 Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite
- Composer (for dependencies)
- Git

### Step 1: Clone the Repository

```bash
git clone https://github.com/Yuutzu/onlinebooking.git
cd Hotel
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Configure Environment Variables

Create a `.env` file in the root directory:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=hotel_management
APP_ENV=production
APP_DEBUG=false
APP_SESSION_TIMEOUT=1800
```

**Important:** Add `.env` to `.gitignore` to prevent credential exposure:

```bash
echo ".env" >> .gitignore
git add .gitignore
git commit -m "Update gitignore to exclude .env"
```

### Step 4: Create Database

Import the SQL file:

```bash
mysql -u root -p hotel_management < backup/sql/hotel.sql
```

Or manually create through phpMyAdmin:

1. Create new database: `hotel_management`
2. Import `backup/sql/hotel.sql`

### Step 5: Set File Permissions

```bash
chmod 755 admin/dist/img/
chmod 755 admin/dist/img/logos/
chmod 755 admin/dist/img/invoices/
chmod 755 client/dist/img/
```

### Step 6: Configure Apache Virtual Host

Add to `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName hotel.local
    ServerAlias www.hotel.local
    DocumentRoot "C:/xampp/htdocs/Hotel"

    <Directory "C:/xampp/htdocs/Hotel">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Restart Apache:

```bash
# Windows XAMPP
net stop Apache2.4
net start Apache2.4

# Linux
sudo systemctl restart apache2
```

### Step 7: Access the System

- **Client Portal:** `http://localhost/Hotel/client/` or `http://hotel.local/client/`
- **Admin Panel:** `http://localhost/Hotel/admin/` or `http://hotel.local/admin/`

---

## ⚙️ Configuration

### Environment Variables (.env)

| Variable              | Description                          | Default          |
| --------------------- | ------------------------------------ | ---------------- |
| `DB_HOST`             | Database server address              | localhost        |
| `DB_USER`             | Database username                    | root             |
| `DB_PASS`             | Database password                    | (empty)          |
| `DB_NAME`             | Database name                        | hotel_management |
| `APP_ENV`             | Environment (production/development) | production       |
| `APP_DEBUG`           | Debug mode (true/false)              | false            |
| `APP_SESSION_TIMEOUT` | Session timeout in seconds           | 1800             |

### SMTP Configuration (mailer_helper.php)

Update email settings in `admin/inc/mailer_helper.php`:

```php
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
```

### GCash Payment Integration

Configure payment settings in `admin/admin_settings.php`:

- GCash Account Name
- GCash Mobile Number
- GCash Account Details

---

## 🗄️ Database Schema

### Core Tables

#### **clients**

- `id` - Primary key
- `client_email` - Email address (unique)
- `client_password` - Bcrypt hashed password
- `client_name` - Full name
- `client_status` - Status (Pending, Activated, Blocked)
- `client_address` - Address
- `client_phone` - Phone number
- `two_fa_enabled` - 2FA enabled flag
- `failed_attempts` - Failed login counter
- `last_failed_attempt` - Last failed attempt timestamp

#### **rooms**

- `room_id` - Primary key
- `room_name` - Room name
- `room_category` - Category name
- `room_description` - Description
- `room_price` - Price per night
- `room_adult` - Max adults
- `room_child` - Max children
- `room_status` - Status (Available, Unavailable)
- `room_picture` - Image filename

#### **online_reservation**

- `reservation_id` - Primary key
- `client_id` - Foreign key to clients
- `room_id` - Foreign key to rooms
- `check_in` - Check-in date
- `check_out` - Check-out date
- `total_price` - Total booking price
- `payment_method` - Payment method
- `reservation_status` - Status (Pending, Confirmed, Cancelled)

#### **payments**

- `payment_id` - Primary key
- `reservation_id` - Foreign key
- `amount` - Payment amount
- `payment_date` - Payment date
- `payment_method` - Payment method
- `payment_status` - Status (Pending, Confirmed, Failed)

---

## 👥 User Roles

### Admin Account (Role: Admin)

- **ID:** `0` (default)
- **Access:** Complete system control
- **Default:** `admin@hotel.local`
- **Features:** All admin functions, settings, reports

### Client Account (Role: Client)

- **Self-registration:** Available on `/client/register.php`
- **Account Statuses:**
  - **Pending:** Awaiting OTP verification
  - **Activated:** Can book rooms
  - **Blocked:** Access denied (after 3 failed logins or by admin)
- **Features:** Browse, book, manage reservations

---

## 🔌 API Endpoints

### Authentication

- `POST /client/login.php` - Client login
- `POST /client/register.php` - Client registration
- `POST /client/otp.php` - OTP verification
- `POST /client/verify_email_2fa.php` - 2FA verification
- `GET /client/logout.php` - Logout

### Reservations

- `POST /admin/book_room.php` - Create walk-in reservation
- `POST /admin/extend_reservation.php` - Extend reservation
- `GET /admin/fetch_reservation.php` - Fetch reservation details

### Admin Operations

- `POST /admin/add_product.php` - Add product
- `POST /admin/room_add.php` - Add room
- `POST /admin/verify_admin_password.php` - Verify admin password

### Utilities

- `POST /admin/fetch/` - Various data fetching endpoints

---

## 🐛 Troubleshooting

### Common Issues

#### **Database Connection Error**

```
Error: SQLSTATE[HY000] [1045]
```

**Solution:**

- Check `.env` file credentials
- Verify MySQL is running
- Ensure database `hotel_management` exists

#### **Session Timeout Issues**

```
Redirected to login unexpectedly
```

**Solution:**

- Check `APP_SESSION_TIMEOUT` in `.env`
- Verify SessionManager is initialized
- Clear browser cookies

#### **Email Not Sending**

```
SMTP Error: Could not connect to SMTP host
```

**Solution:**

- Verify SMTP credentials in `mailer_helper.php`
- Check firewall allows port 587
- Enable "Less secure apps" (Gmail)

#### **File Upload Failing**

```
MIME type validation failed
```

**Solution:**

- Verify upload directory permissions (755)
- Check file matches allowed MIME types
- Ensure file size < 2MB

#### **CSRF Token Errors**

```
403 Forbidden - Invalid CSRF token
```

**Solution:**

- Clear browser session/cookies
- Ensure CSRFToken class is loaded
- Verify `<form>` includes `<?php echo CSRFToken::field(); ?>`

---

## 🔐 Security Best Practices

### For Deployment

1. **Always use HTTPS** - Enable SSL certificate
2. **Disable Debug Mode** - Set `APP_DEBUG=false`
3. **Strong Passwords** - Enforce complex admin password
4. **Regular Backups** - Schedule daily database backups
5. **Update Dependencies** - Run `composer update` regularly
6. **Monitor Logs** - Check Apache error logs weekly
7. **Firewall Rules** - Restrict admin access by IP
8. **Database User** - Create limited DB user for application

### For Development

1. **Use `.env.example`** - Document required variables
2. **Enable Debug Logging** - Set `APP_DEBUG=true`
3. **Use Postman** - Test API endpoints
4. **Run Tests** - Automated security scanning
5. **Code Review** - Before merging to main

---

## 📊 Recent Security Improvements

All critical vulnerabilities have been patched. See [Security Updates](#security-features) above.

### Fixes Applied (Feb 2026)

- ✅ Admin password hashing with bcrypt + rate limiting
- ✅ Argon2ID password upgrade path
- ✅ Session management after 2FA
- ✅ SQL injection prevention in filters
- ✅ Open redirect vulnerability closed
- ✅ Email enumeration prevention
- ✅ Cryptographically secure OTP/ID generation
- ✅ Environment-based configuration

---

## 📝 Maintenance

### Regular Tasks

| Frequency | Task                | Command                                                         |
| --------- | ------------------- | --------------------------------------------------------------- |
| Daily     | Backup database     | `mysqldump hotel_management > backup/daily_$(date +%Y%m%d).sql` |
| Weekly    | Check error logs    | Review Apache `error.log`                                       |
| Monthly   | Update dependencies | `composer update`                                               |
| Quarterly | Security audit      | Review recent commits                                           |

### Database Maintenance

```bash
# Optimize tables
mysqlcheck -u root -p hotel_management --optimize

# Repair corrupted tables
mysqlcheck -u root -p hotel_management --repair
```

---

## 🤝 Contributing

1. Create a new branch: `git checkout -b feature/your-feature`
2. Make your changes with security in mind
3. Test thoroughly before committing
4. Push to branch: `git push origin feature/your-feature`
5. Open a Pull Request

---

## 📞 Support

### Getting Help

- **Documentation:** See [Project Structure](#project-structure)
- **Error Logs:** Check `apache2/error.log`
- **Database Issues:** Use phpMyAdmin
- **Email Support:** Contact administrator

### Security Issues

⚠️ **Report security vulnerabilities privately** - Do not create public issues!

Contact: admin@luxehavenhotel.com

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 🙏 Acknowledgments

- Built with PHP and MySQL
- UI powered by Bootstrap 5
- Email service by PHPMailer
- Security best practices from OWASP

---

## 📈 Version History

| Version | Date         | Changes                                           |
| ------- | ------------ | ------------------------------------------------- |
| 1.1.0   | Feb 28, 2026 | Security patch: 10 critical vulnerabilities fixed |
| 1.0.5   | Feb 20, 2026 | File upload security implementation               |
| 1.0.0   | Jan 15, 2026 | Initial production release                        |

---

**Last Updated:** February 28, 2026

**Status:** ✅ Production Ready - Enhanced Security
