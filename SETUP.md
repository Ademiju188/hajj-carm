# Setup Instructions

## 1. Database Setup

### Option A: Using MySQL Command Line

```bash
# Login to MySQL
mysql -u root -p

# Run the migration script
source database/database.sql
```

### Option B: Using phpMyAdmin

1. Open phpMyAdmin
2. Click "Import" tab
3. Choose file: `database/migration.sql`
4. Click "Go"

## 2. Update Database Configuration

Edit `config/database.php` and update these values:

```php
define('DB_HOST', 'localhost');      // Your MySQL host
define('DB_NAME', 'hajj_registration_crm');  // Database name
define('DB_USER', 'root');            // Your MySQL username
define('DB_PASS', '');                // Your MySQL password
```

## 3. Update Email Configuration (Optional)

Edit `config/database.php` and update SMTP settings if you want email notifications:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
```

## 4. Set File Permissions

```bash
# On Linux/Mac
chmod 755 uploads/
chmod 755 uploads/passport_pictures/
chmod 755 uploads/passport_documents/

# On Windows (if needed)
# Right-click folders > Properties > Security > Edit permissions
```

## 5. Change Default Admin Password

**IMPORTANT:** Change the default admin password immediately!

### Generate a new password hash:

```php
<?php
// Run this once to generate a password hash
echo password_hash('your-new-password', PASSWORD_DEFAULT);
?>
```

Then update in MySQL:

```sql
UPDATE admin_users 
SET password_hash = 'your-generated-hash' 
WHERE username = 'admin';
```

Or use this SQL directly (password: `admin123` - CHANGE IT!):

```sql
UPDATE admin_users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';
```

## 6. Access the System

- **Registration Form**: `http://localhost/mini-crm/hajj-registration.html`
- **Admin Login**: `http://localhost/mini-crm/admin/login.php`
  - Username: `admin`
  - Password: `admin123` (change immediately!)

## 7. Share Form Link

1. Open the registration form
2. Click "Share Form Link via WhatsApp" button
3. Share the link with customers

## Features

✅ **Registration Form**
- File uploads (passport picture & document)
- Dynamic package selection
- WhatsApp sharing
- Mobile responsive

✅ **Admin Dashboard**
- Customer list with search/filter
- Customer details with passport images
- Status management
- Reports and analytics
- Excel export

✅ **Reports**
- City distribution
- Package selection stats
- Room type analysis
- Monthly registrations
- Status breakdown

## Troubleshooting

### File Upload Issues
- Check `uploads/` folder permissions (755)
- Check PHP `upload_max_filesize` in php.ini
- Check PHP `post_max_size` in php.ini

### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `config/database.php`
- Ensure database exists: `hajj_registration_crm`

### Email Not Working
- Check SMTP settings in `config/database.php`
- For Gmail, use App Password (not regular password)
- Check PHP `mail()` function is enabled

## Support

For issues or questions, contact: awaisitours@gmail.com

