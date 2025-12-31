# Hajj Registration CRM System

A complete registration and customer management system for Hajj 2026 registrations.

## Features

- **Registration Form**: Enhanced form with file uploads (passport picture & document)
- **WhatsApp Sharing**: Share form link via WhatsApp
- **Dynamic Package Selection**: Room types change based on selected package
- **Admin Dashboard**: Full CRM interface for managing customers
- **Reports**: Analytics and Excel export functionality
- **Email Notifications**: Automatic confirmation emails

## Installation

### 1. Database Setup

```bash
# Login to MySQL
mysql -u root -p

# Run migration
source database/migration.sql
```

Or import the SQL file through phpMyAdmin.

### 2. Configuration

Edit `config/database.php` and update:
- Database credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS)
- Email SMTP settings
- Application URL

### 3. File Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/passport_pictures/
chmod 755 uploads/passport_documents/
```

### 4. Default Admin Login

- Username: `admin`
- Password: `admin123` (CHANGE THIS IMMEDIATELY!)

## Project Structure

```
mini-crm/
├── api/                    # API endpoints
│   └── submit-registration.php
├── admin/                  # Admin panel
│   ├── login.php
│   ├── dashboard.php
│   ├── customers.php
│   └── reports.php
├── config/                 # Configuration files
│   └── database.php
├── database/               # Database files
│   └── migration.sql
├── includes/               # Shared PHP functions
│   └── functions.php
├── template/               # Material UI template assets
├── uploads/                # Uploaded files
│   ├── passport_pictures/
│   └── passport_documents/
├── hajj-registration.html  # Main registration form
└── package.json            # (Not used with PHP)
```

## Usage

1. Access the registration form: `http://localhost/mini-crm/hajj-registration.html`
2. Share the link via WhatsApp using the share button
3. Customers complete and submit the form
4. Admin login: `http://localhost/mini-crm/admin/login.php`
5. View and manage customers in the admin dashboard

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx with mod_rewrite
- GD Library (for image handling)

