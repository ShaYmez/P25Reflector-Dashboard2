# P25Reflector-Dashboard2 — Installation Guide

For **your own** G4KLX P25Reflector (any talkgroup, any host). This is the official Dashboard2 used on FreeSTAR and by independent reflector operators.

## Prerequisites

- Linux (Debian/Ubuntu recommended)
- Working **P25Reflector** (G4KLX)
- PHP 7.4–8.3 (`php`, `php-cli`, `php-mbstring`)
- Node.js >= 16.x (to build CSS once)
- Apache or Nginx
- Git

## 1. Install packages (Debian/Ubuntu)

```bash
sudo apt update
sudo apt install -y apache2 php php-cli php-mbstring php-xml git nodejs npm
# or: sudo apt install -y nginx php-fpm php-cli php-mbstring git nodejs npm
```

## 2. Clone

```bash
cd /var/www/html
sudo git clone https://github.com/ShaYmez/P25Reflector-Dashboard2.git
cd P25Reflector-Dashboard2
```

Default branch is **`master`**.

## 3. Build CSS

```bash
sudo npm install
sudo npm run build:css
```

## 4. Permissions

```bash
sudo chown -R www-data:www-data /var/www/html/P25Reflector-Dashboard2
sudo chmod -R 755 /var/www/html/P25Reflector-Dashboard2
sudo mkdir -p /var/www/html/P25Reflector-Dashboard2/config
sudo chown www-data:www-data /var/www/html/P25Reflector-Dashboard2/config
sudo chmod 775 /var/www/html/P25Reflector-Dashboard2/config
```

Replace `www-data` with `apache` / `nginx` if needed. **Do not use `chmod 777`.**

## 5. Virtual host

### Apache

Create `/etc/apache2/sites-available/p25-dashboard.conf`:

```apache
<VirtualHost *:80>
    ServerName p25.yourdomain.com
    DocumentRoot /var/www/html/P25Reflector-Dashboard2

    <Directory /var/www/html/P25Reflector-Dashboard2>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/html/P25Reflector-Dashboard2/config>
        Require all denied
    </Directory>
    <Directory /var/www/html/P25Reflector-Dashboard2/.git>
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/p25-dashboard-error.log
    CustomLog ${APACHE_LOG_DIR}/p25-dashboard-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite p25-dashboard
sudo systemctl reload apache2
```

Point DNS (or a hosts file) at this server, then open `http://p25.yourdomain.com/setup.php`.

### Nginx

Create `/etc/nginx/sites-available/p25-dashboard`:

```nginx
server {
    listen 80;
    server_name p25.yourdomain.com;
    root /var/www/html/P25Reflector-Dashboard2;
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(git|ht) {
        deny all;
    }

    location ^~ /config/ {
        deny all;
    }

    location ^~ /include/ {
        deny all;
    }
}
```

Adjust the PHP-FPM socket to your version (`php7.4-fpm.sock`, `php8.3-fpm.sock`, …).

```bash
sudo ln -s /etc/nginx/sites-available/p25-dashboard /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

## 6. Setup wizard

1. Browse to `http://p25.yourdomain.com/setup.php`
2. Set dashboard name, tagline, logo, log path, and `P25Reflector.ini` path
3. Save, then **delete setup.php**:

```bash
sudo rm /var/www/html/P25Reflector-Dashboard2/setup.php
```

P25Reflector logs (dated or logrotate) are both supported:

```ini
[Log]
FilePath=/var/log/P25Reflector/
FileRoot=P25Reflector
FileRotate=1
```

The web user must be able to **read** that directory.

Add an `[Info]` section to `P25Reflector.ini` so the header can show name and TG:

```ini
[Info]
Id=23426
Name=My P25 Reflector
Description=Optional text
```

## HTTPS

```bash
sudo apt install -y certbot python3-certbot-apache   # or python3-certbot-nginx
sudo certbot --apache -d p25.yourdomain.com
```

## Updating

Always pull **`master`** as the owner of the clone (usually root), then restore `www-data` ownership. That avoids `fatal: detected dubious ownership` and `cannot open '.git/FETCH_HEAD': Permission denied`.

```bash
cd /var/www/html/P25Reflector-Dashboard2
sudo git pull origin master
sudo npm install
sudo npm run build:css
sudo chown -R www-data:www-data .
sudo rm -f setup.php
```

If git still complains about ownership:

```bash
sudo git config --global --add safe.directory /var/www/html/P25Reflector-Dashboard2
```

## Layout options (`config/config.php`)

Live tables refresh every 5 seconds in the browser (`REFRESHAFTER` is unused).

```php
define("LAST_HEARD_FIRST", true);   // last heard above linked list
define("SHOW_SYSTEM_INFO", false);  // hide uptime / load panel
```

## Support

- Issues: https://github.com/ShaYmez/P25Reflector-Dashboard2/issues
- Live example: https://p25.freestar.network
