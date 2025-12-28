# Installation Guide

## Quick Start

This guide will help you install and configure P25Reflector-Dashboard2 on your server.

## Prerequisites

- Linux server (Debian/Ubuntu recommended)
- PHP >= 7.4 (tested with PHP 7.4, 8.0, 8.1, 8.2, and 8.3)
- Node.js >= 16.x
- Web server (Apache or Nginx)
- P25Reflector installed and running

### PHP Compatibility

This dashboard is fully compatible with:
- PHP 7.4 (minimum required version)
- PHP 8.0, 8.1, 8.2, 8.3 (tested and confirmed)

All deprecated functions have been replaced with PHP 8+ compatible alternatives.

## Step-by-Step Installation

### 1. Install Dependencies

#### On Debian/Ubuntu:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and required extensions
sudo apt install -y php php-cli php-fpm php-json php-mbstring

# Install Node.js (if not already installed)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Apache or Nginx
sudo apt install -y apache2  # For Apache
# OR
sudo apt install -y nginx php-fpm  # For Nginx
```

### 2. Download Dashboard

```bash
cd /var/www/html
sudo git clone https://github.com/ShaYmez/P25Reflector-Dashboard2.git
cd P25Reflector-Dashboard2
```

### 3. Build Assets

```bash
npm install
npm run build:css
```

### 4. Set Permissions IMPORTANT!!

```bash
sudo chown -R www-data:www-data /var/www/html/P25Reflector-Dashboard2
sudo chmod -R 755 /var/www/html/P25Reflector-Dashboard2
```

### 5. Configure Web Server

#### Apache Configuration

Create `/etc/apache2/sites-available/p25-dashboard.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    
    DocumentRoot /var/www/html/P25Reflector-Dashboard2
    
    <Directory /var/www/html/P25Reflector-Dashboard2>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/p25-dashboard-error.log
    CustomLog ${APACHE_LOG_DIR}/p25-dashboard-access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite p25-dashboard
sudo systemctl reload apache2
```

#### Nginx Configuration

Create `/etc/nginx/sites-available/p25-dashboard`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    
    root /var/www/html/P25Reflector-Dashboard2;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    location ~ /config/ {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/p25-dashboard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Initial Setup

1. Open your browser and navigate to `http://your-domain.com/setup.php`

2. Fill in the configuration form:

   **Dashboard Branding:**
   - Dashboard Name: Your reflector name
   - Dashboard Tagline: Custom tagline
   - Logo URL: (optional) URL to your logo image or drop image file in /img

   **P25Reflector Configuration:**
   - Path to Log Files: `/var/log/P25Reflector/`
   - Log File Prefix: `P25Reflector`
   - Path to P25Reflector.ini: `/etc/`
   - P25Reflector.ini Filename: `P25Reflector.ini`
   - Path to Executable: `/usr/local/bin/`

   **Global Settings:**
   - Select your timezone
   - Set refresh interval (default: 60 seconds)
   - Configure other options as needed

3. Click "Save Configuration"

4. **IMPORTANT:** Delete setup.php for security:
   ```bash
   sudo rm /var/www/html/P25Reflector-Dashboard2/setup.php
   ```

### 7. Verify Installation

Navigate to `http://your-domain.com/index.php` to see your dashboard.

## SSL/HTTPS Configuration (Recommended)

### Using Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache  # For Apache
# OR
sudo apt install -y certbot python3-certbot-nginx  # For Nginx

# Obtain certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com  # For Apache
# OR
sudo certbot --nginx -d your-domain.com -d www.your-domain.com  # For Nginx

# Auto-renewal is configured automatically
```

## Troubleshooting

### Dashboard shows "No repeaters connected"

- Verify P25Reflector is running: `systemctl status p25reflector`
- Check log file path in config
- Ensure web server has read permissions on log files

### Setup page won't create config.php

- Check directory permissions: `ls -la /var/www/html/P25Reflector-Dashboard2/`
- Ensure www-data has write access

### PHP errors

- Check PHP error log: `tail -f /var/log/apache2/error.log` or `/var/log/nginx/error.log`
- Verify PHP version: `php -v` (must be >= 7.4)

### CSS not loading properly

- Rebuild CSS: `npm run build:css`
- Clear browser cache
- Check file permissions on assets/css/output.css

## Updating

To update the dashboard:

```bash
cd /var/www/html/P25Reflector-Dashboard2
git pull origin main
npm install
npm run build:css
sudo chown -R www-data:www-data .
```

## Support

For issues or questions:
- GitHub Issues: https://github.com/ShaYmez/P25Reflector-Dashboard2/issues
- Check the README.md for more information
