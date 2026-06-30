# Poster Printer Server Setup Guide

Complete setup instructions for deploying the Poster Printer application on a fresh Rocky Linux 10 server.

## 1. System Requirements

- **OS:** Rocky Linux 10 (or RHEL 10 compatible)
- **Web Server:** Apache (httpd)
- **PHP:** PHP 8.x with php-fpm
- **Database:** MariaDB
- **Additional:** git, certbot

## 2. Install Packages

```bash
sudo dnf -y install httpd php php-fpm php-mysqlnd php-ldap php-gd php-mbstring php-xml mariadb-server git
sudo dnf -y install certbot python3-certbot-apache
```

## 3. Start and Enable Services

```bash
sudo systemctl enable --now httpd
sudo systemctl enable --now php-fpm
sudo systemctl enable --now mariadb
```

## 4. MariaDB Setup

### Create Database and User

```bash
sudo mysql
```

```sql
CREATE DATABASE posterprinter;
CREATE USER 'posterprinter'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON posterprinter.* TO 'posterprinter'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Import Database

If importing from a dump file:

```bash
zcat /path/to/daily_posterprinter_YYYY-MM-DD.sql.gz | mysql -u posterprinter -p posterprinter
```

### Run Schema Migration (if importing from v1.x)

```bash
mysql -u posterprinter -p posterprinter < /path/to/posterprinter/sql/update-2.0.sql
```

**Note:** If the dump already contains v2.0 schema (non-prefixed table names like `orders` instead of `tbl_orders`), the migration may fail with "Table already exists" errors. In that case, check if the data is already in the correct tables and skip the migration.

## 5. Deploy Application Code

```bash
cd /var/www
sudo git clone https://github.com/YOUR_ORG/posterprinter.git
cd posterprinter
sudo git checkout heel-one   # or your target branch
```

### Install Composer Dependencies

```bash
cd /var/www/posterprinter
sudo composer install --no-dev
```

### Configure Application

Copy and edit the settings file:

```bash
sudo cp conf/settings.inc.php.example conf/settings.inc.php
sudo vi conf/settings.inc.php
```

Key settings to configure:
- `MYSQL_HOST`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`
- `LDAP_HOST`, `LDAP_BASE_DN`, `LDAP_PORT`, `LDAP_GROUP`
- `LDAP_BIND_USER`, `LDAP_BIND_PASS` (if required)
- `WEBSITE_URL`
- `POSTER_DIR` (path where uploaded poster files are stored)
- `SESSION_NAME`
- `ADMIN_EMAIL`, `ADMIN_NAME`
- `SMTP_HOST`

### Set File Permissions

```bash
# Apache needs to read the application files
sudo chown -R root:apache /var/www/posterprinter
sudo chmod -R 750 /var/www/posterprinter

# Poster upload directory needs to be writable by Apache
sudo chown apache:apache /var/www/posterprinter/posters
sudo chmod 770 /var/www/posterprinter/posters

# Vendor directory needs to be readable
sudo chmod -R 755 /var/www/posterprinter/vendor
```

## 6. Apache Configuration

### Create VirtualHost Config

Create `/etc/httpd/conf.d/posterprinter.conf`:

```apache
<VirtualHost *:80>
    ServerName YOUR_HOSTNAME.igb.illinois.edu

    Alias /posterprinter/vendor /var/www/posterprinter/vendor
    <Directory /var/www/posterprinter/vendor>
        AllowOverride None
        Require all granted
    </Directory>

    Alias /posterprinter /var/www/posterprinter/html
    <Directory /var/www/posterprinter/html>
        AllowOverride None
        Require all granted
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
```

### Disable Default SSL Config (if it references missing certs)

```bash
sudo mv /etc/httpd/conf.d/ssl.conf /etc/httpd/conf.d/ssl.conf.bak
```

### Test and Reload

```bash
sudo apachectl configtest
sudo systemctl reload httpd
```

## 7. SSL/HTTPS with Certbot (Let's Encrypt)

### Prerequisites
- Server must have a DNS hostname (e.g., `hostname.igb.illinois.edu`)
- Port 80 must be reachable from the internet (at least temporarily for certificate validation)

### Request Certificate

```bash
# Temporarily open port 80 to all (needed for Let's Encrypt validation)
sudo firewall-cmd --add-service=http --permanent
sudo firewall-cmd --reload

# Request certificate (certbot will auto-configure Apache SSL)
sudo certbot --apache -d YOUR_HOSTNAME.igb.illinois.edu

# Certbot will:
# - Obtain the certificate
# - Create an SSL VirtualHost config
# - Set up automatic renewal via systemd timer
```

### Verify Auto-Renewal

```bash
sudo certbot renew --dry-run
```

**Note on renewal:** Let's Encrypt certs expire every 90 days. Certbot sets up a systemd timer for automatic renewal. Port 80 must be reachable from the internet during renewal. If the firewall restricts port 80, add pre/post hooks:

```bash
# Edit /etc/letsencrypt/renewal/YOUR_HOSTNAME.igb.illinois.edu.conf
# Add under [renewalparams]:
pre_hook = firewall-cmd --add-service=http
post_hook = firewall-cmd --remove-service=http
```

## 8. Firewall Configuration

```bash
# Allow HTTPS from UIUC network only
sudo firewall-cmd --add-rich-rule='rule family="ipv4" source address="128.174.0.0/16" service name="https" accept' --permanent

# Allow HTTP from UIUC network only (for redirect to HTTPS)
sudo firewall-cmd --add-rich-rule='rule family="ipv4" source address="128.174.0.0/16" service name="http" accept' --permanent

# Remove wide-open HTTP if it was added for certbot
sudo firewall-cmd --remove-service=http --permanent

# Apply changes
sudo firewall-cmd --reload
```

### Default Allowed Services (already present on fresh install)

- SSH (port 22)
- Cockpit (port 9090) — web-based server management
- DHCPv6 client

### Optional: Add RDP if needed

```bash
sudo firewall-cmd --add-port=3389/tcp --permanent
sudo firewall-cmd --add-port=3390/tcp --permanent
sudo firewall-cmd --reload
```

### Verify Firewall Rules

```bash
sudo firewall-cmd --list-all
```

## 9. PHP Configuration

### OPcache

OPcache is enabled by default. After deploying code changes, restart php-fpm to clear the cache:

```bash
sudo systemctl restart php-fpm
```

### Session Security

The `IGBIllinois\session` class sets `cookie_secure = true` by default. This means:
- Sessions **only work over HTTPS** — this is by design for security
- `localhost` access works (browsers are lenient) but HTTP via IP will fail
- **HTTPS must be configured before the admin panel login will work remotely**

## 10. Deployment Workflow

Once the server is set up, deploying code changes follows this flow:

```bash
# On dev machine (e.g., /home/igb/posterprinter)
git add <files>
git commit -m "description"
git push origin heel-one

# On prod server
cd /var/www/posterprinter
git pull origin heel-one
sudo systemctl restart php-fpm
sudo systemctl restart httpd
```

## 11. Security Features

### Login Brute Force Protection

The admin login page (`html/admin/login.php`) includes brute force protection via the `loginthrottle` class:
- **Max attempts:** 7 failed logins per IP
- **Lockout duration:** 15 minutes
- **Storage:** File-based in `/tmp/posterprinter_login_attempts/`
- **Cleanup:** Old lockout files (>1 hour) are periodically cleaned up
- Failed attempt counts and lockouts are logged

### Firewall

- HTTP/HTTPS restricted to UIUC network (128.174.0.0/16)
- No public access from outside campus network

### Session Cookies

- Secure flag: enabled (HTTPS only)
- HttpOnly flag: enabled (prevents JavaScript access)
- SameSite: lax

## 12. Troubleshooting

### Site not accessible from other machines
- Check firewall: `sudo firewall-cmd --list-all`
- Ensure HTTP/HTTPS services or rich rules are configured

### Login form resets without error
- Cookie issue — the `Secure` cookie flag requires HTTPS
- Verify SSL cert is installed and working: `curl -I https://YOUR_HOSTNAME.igb.illinois.edu`

### Database connection errors
- Check credentials in `conf/settings.inc.php`
- Verify MariaDB is running: `sudo systemctl status mariadb`
- Test connection: `mysql -u posterprinter -p posterprinter`

### Changes not taking effect after deploy
- Restart php-fpm to clear OPcache: `sudo systemctl restart php-fpm`
- Restart httpd if Apache config changed: `sudo systemctl restart httpd`

### Certbot renewal fails
- Ensure port 80 is reachable from the internet during renewal
- Check logs: `sudo cat /var/log/letsencrypt/letsencrypt.log`
- Test renewal: `sudo certbot renew --dry-run`
