# Server Infrastructure Guide for Blake Quarterly

This guide explains how to deploy the modernized Blake Quarterly application on your server.

## Infrastructure Changes Summary

### ✅ What You Can Remove
- ❌ **PHP** and all PHP extensions (php-fpm, mod_php, etc.)
- ❌ **Apache Solr** (no longer needed!)
- ❌ **Composer** (PHP package manager)
- ❌ Complex PHP configuration

### ✅ What You Need to Add
- ✅ **Python 3.9+** runtime
- ✅ **PostgreSQL 15+** database
- ✅ **Reverse proxy** configuration (Nginx or Apache)
- ✅ **Process manager** (systemd or supervisor)

### ↔️ What Stays the Same
- Your web server (Nginx/Apache) - just reconfigure it
- SSL/TLS certificates
- Domain names and DNS
- Static file serving
- Firewall rules (mostly)

## Deployment Architecture

### Before (PHP)
```
Internet → Web Server (Apache/Nginx) → PHP-FPM → Solr
                                     → File System
```

### After (Python)
```
Internet → Web Server (Nginx/Apache) → FastAPI (uvicorn) → PostgreSQL
                                     → File System
```

**Simpler!** One less service (no Solr), cleaner architecture.

---

## Step-by-Step Server Setup

### 1. Install Required Software

#### Ubuntu/Debian
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Python 3.9+
sudo apt install -y python3 python3-pip python3-venv

# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Install Nginx (if not already installed)
sudo apt install -y nginx

# Install supervisor for process management
sudo apt install -y supervisor

# Install build dependencies for Python packages
sudo apt install -y build-essential python3-dev libpq-dev libxml2-dev libxslt1-dev
```

#### RHEL/CentOS/Rocky Linux
```bash
# Update system
sudo dnf update -y

# Install Python 3.9+
sudo dnf install -y python39 python39-pip python39-devel

# Install PostgreSQL
sudo dnf install -y postgresql-server postgresql-contrib

# Initialize PostgreSQL (first time only)
sudo postgresql-setup --initdb
sudo systemctl enable postgresql
sudo systemctl start postgresql

# Install Nginx
sudo dnf install -y nginx

# Install supervisor
sudo dnf install -y supervisor

# Install build dependencies
sudo dnf install -y gcc libpq-devel libxml2-devel libxslt-devel
```

### 2. Setup PostgreSQL Database

```bash
# Switch to postgres user
sudo -u postgres psql

# In PostgreSQL prompt:
CREATE DATABASE blake_quarterly;
CREATE USER blake WITH PASSWORD 'your_secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE blake_quarterly TO blake;
\q
```

**Important:** Choose a strong password and save it securely!

### 3. Deploy Application Files

```bash
# Create application directory
sudo mkdir -p /var/www/quarterly
sudo chown $USER:www-data /var/www/quarterly

# Clone or copy your application
cd /var/www/quarterly
git clone <your-repo-url> .

# Or if uploading files:
# scp -r quarterly/ user@server:/var/www/

# Set permissions
sudo chown -R www-data:www-data /var/www/quarterly
sudo chmod -R 755 /var/www/quarterly
```

### 4. Setup Python Virtual Environment

```bash
cd /var/www/quarterly

# Create virtual environment
python3 -m venv venv

# Activate it
source venv/bin/activate

# Install dependencies
pip install --upgrade pip
pip install -r requirements.txt

# Install production WSGI server
pip install gunicorn
```

### 5. Configure Environment

```bash
# Create .env file
sudo nano /var/www/quarterly/.env
```

Add this content (adjust as needed):

```bash
# Production Environment
ENVIRONMENT=production
DEBUG=False

# Database
DATABASE_URL=postgresql://blake:your_secure_password_here@localhost:5432/blake_quarterly

# Server
HOST=127.0.0.1
PORT=8000

# Publication Range
MIN_VOLUME=1
MAX_VOLUME=48
MIN_ISSUE=1
MAX_ISSUE=4
```

**Security:** Protect this file!
```bash
sudo chmod 600 /var/www/quarterly/.env
sudo chown www-data:www-data /var/www/quarterly/.env
```

### 6. Index Documents

```bash
cd /var/www/quarterly
source venv/bin/activate
python -m app.indexer
```

This will create tables and index all your documents.

---

## Process Management

You need a way to keep the Python app running. Choose one:

### Option A: systemd (Recommended for Modern Linux)

Create service file:
```bash
sudo nano /etc/systemd/system/blake-quarterly.service
```

Add this content:

```ini
[Unit]
Description=Blake Quarterly FastAPI Application
After=network.target postgresql.service
Requires=postgresql.service

[Service]
Type=notify
User=www-data
Group=www-data
WorkingDirectory=/var/www/quarterly
Environment="PATH=/var/www/quarterly/venv/bin"
EnvironmentFile=/var/www/quarterly/.env

# Use gunicorn with uvicorn workers for production
ExecStart=/var/www/quarterly/venv/bin/gunicorn app.main:app \
    --workers 4 \
    --worker-class uvicorn.workers.UvicornWorker \
    --bind 127.0.0.1:8000 \
    --timeout 120 \
    --access-logfile /var/log/blake-quarterly/access.log \
    --error-logfile /var/log/blake-quarterly/error.log

# Restart policy
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Create log directory:
```bash
sudo mkdir -p /var/log/blake-quarterly
sudo chown www-data:www-data /var/log/blake-quarterly
```

Enable and start service:
```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable service (start on boot)
sudo systemctl enable blake-quarterly

# Start service
sudo systemctl start blake-quarterly

# Check status
sudo systemctl status blake-quarterly

# View logs
sudo journalctl -u blake-quarterly -f
```

### Option B: Supervisor (Alternative)

Create config:
```bash
sudo nano /etc/supervisor/conf.d/blake-quarterly.conf
```

Add:
```ini
[program:blake-quarterly]
command=/var/www/quarterly/venv/bin/gunicorn app.main:app
    --workers 4
    --worker-class uvicorn.workers.UvicornWorker
    --bind 127.0.0.1:8000
directory=/var/www/quarterly
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/blake-quarterly/app.log
```

Start:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start blake-quarterly
sudo supervisorctl status blake-quarterly
```

---

## Web Server Configuration

### Option A: Nginx (Recommended)

Create site configuration:
```bash
sudo nano /etc/nginx/sites-available/blake-quarterly
```

Add this configuration:

```nginx
# Blake Quarterly - Production Configuration

upstream blake_app {
    server 127.0.0.1:8000 fail_timeout=0;
}

server {
    listen 80;
    server_name bq.blakearchive.org;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name bq.blakearchive.org;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/blake-quarterly.crt;
    ssl_certificate_key /etc/ssl/private/blake-quarterly.key;

    # Modern SSL settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Logging
    access_log /var/log/nginx/blake-quarterly.access.log;
    error_log /var/log/nginx/blake-quarterly.error.log;

    # Max upload size (if needed)
    client_max_body_size 10M;

    # Root directory for static files
    root /var/www/quarterly;

    # Static files (served directly by Nginx for performance)
    location /js/ {
        alias /var/www/quarterly/js/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location /images/ {
        alias /var/www/quarterly/images/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location /style.css {
        alias /var/www/quarterly/style.css;
        expires 7d;
        add_header Cache-Control "public";
    }

    # Proxy all other requests to FastAPI
    location / {
        proxy_pass http://blake_app;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;

        # WebSocket support (if needed later)
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }

    # Health check endpoint (can be accessed directly)
    location /health {
        proxy_pass http://blake_app;
        access_log off;
    }
}
```

Enable site:
```bash
# Test configuration
sudo nginx -t

# Enable site
sudo ln -s /etc/nginx/sites-available/blake-quarterly /etc/nginx/sites-enabled/

# Remove old PHP site (if exists)
sudo rm /etc/nginx/sites-enabled/old-php-site

# Reload Nginx
sudo systemctl reload nginx
```

### Option B: Apache (Alternative)

Enable required modules:
```bash
sudo a2enmod proxy proxy_http headers rewrite ssl
```

Create site configuration:
```bash
sudo nano /etc/apache2/sites-available/blake-quarterly.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName bq.blakearchive.org
    Redirect permanent / https://bq.blakearchive.org/
</VirtualHost>

<VirtualHost *:443>
    ServerName bq.blakearchive.org

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/blake-quarterly.crt
    SSLCertificateKeyFile /etc/ssl/private/blake-quarterly.key

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/blake-quarterly-error.log
    CustomLog ${APACHE_LOG_DIR}/blake-quarterly-access.log combined

    # Static files
    Alias /js /var/www/quarterly/js
    Alias /images /var/www/quarterly/images
    Alias /style.css /var/www/quarterly/style.css

    <Directory /var/www/quarterly>
        Require all granted
    </Directory>

    # Proxy to FastAPI
    ProxyPreserveHost On
    ProxyPass /js !
    ProxyPass /images !
    ProxyPass /style.css !
    ProxyPass / http://127.0.0.1:8000/
    ProxyPassReverse / http://127.0.0.1:8000/

    # Headers
    RequestHeader set X-Forwarded-Proto "https"
    RequestHeader set X-Forwarded-Port "443"
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite blake-quarterly
sudo a2dissite old-php-site  # Disable old site
sudo systemctl reload apache2
```

---

## Firewall Configuration

```bash
# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# PostgreSQL should NOT be exposed to internet (only localhost)
# Don't open port 5432 externally

# If using SSH
sudo ufw allow 22/tcp

# Enable firewall
sudo ufw enable
```

---

## Resource Requirements

### Minimum Requirements
- **CPU:** 1 core
- **RAM:** 1 GB
- **Disk:** 500 MB (plus your documents)
- **Database:** PostgreSQL can run on same server

### Recommended for Production
- **CPU:** 2 cores
- **RAM:** 2-4 GB
- **Disk:** 5 GB (for logs, backups, growth)
- **Database:** Same server is fine for this workload

### Gunicorn Workers
Use this formula: `(2 × CPU cores) + 1`

- 1 CPU → 3 workers
- 2 CPU → 5 workers
- 4 CPU → 9 workers

Adjust in systemd service or supervisor config.

---

## Monitoring and Maintenance

### Check Application Status

```bash
# Using systemd
sudo systemctl status blake-quarterly

# View logs
sudo journalctl -u blake-quarterly -f

# Or supervisor
sudo supervisorctl status blake-quarterly
sudo tail -f /var/log/blake-quarterly/app.log
```

### Check Database

```bash
# Connect to database
sudo -u postgres psql blake_quarterly

# Check document count
SELECT COUNT(*) FROM documents;

# Check database size
SELECT pg_size_pretty(pg_database_size('blake_quarterly'));

\q
```

### Application Logs

```bash
# View recent errors
sudo journalctl -u blake-quarterly --since "1 hour ago" | grep ERROR

# Application logs (if using supervisor)
sudo tail -f /var/log/blake-quarterly/app.log

# Nginx access logs
sudo tail -f /var/log/nginx/blake-quarterly.access.log
```

### Database Backup

```bash
# Create backup script
sudo nano /usr/local/bin/backup-blake-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/blake-quarterly"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
sudo -u postgres pg_dump blake_quarterly | gzip > $BACKUP_DIR/blake_db_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "blake_db_*.sql.gz" -mtime +7 -delete

echo "Backup complete: blake_db_$DATE.sql.gz"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-blake-db.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```

Add this line:
```
0 2 * * * /usr/local/bin/backup-blake-db.sh
```

### Restart Application

```bash
# After code updates
cd /var/www/quarterly
git pull
source venv/bin/activate
pip install -r requirements.txt  # If dependencies changed

# Restart service
sudo systemctl restart blake-quarterly

# Or with supervisor
sudo supervisorctl restart blake-quarterly
```

---

## Migration Checklist

### Pre-Migration
- [ ] Backup current PHP site
- [ ] Export any existing data
- [ ] Note current PHP version and extensions
- [ ] Document current server configuration
- [ ] Test new app on staging server

### Installation
- [ ] Install Python 3.9+
- [ ] Install PostgreSQL 15+
- [ ] Install Nginx or reconfigure Apache
- [ ] Deploy application files
- [ ] Create virtual environment
- [ ] Install Python dependencies

### Configuration
- [ ] Setup PostgreSQL database
- [ ] Configure `.env` file
- [ ] Index documents
- [ ] Configure systemd/supervisor
- [ ] Configure web server (Nginx/Apache)
- [ ] Setup SSL certificates
- [ ] Configure firewall

### Testing
- [ ] Test application locally (curl http://localhost:8000)
- [ ] Test through web server
- [ ] Test all pages (issues, articles, search)
- [ ] Test document viewing (HTML and XML)
- [ ] Check logs for errors
- [ ] Performance test

### Post-Migration
- [ ] Monitor logs for errors
- [ ] Setup database backups
- [ ] Setup monitoring (optional)
- [ ] Remove old PHP installation (after confirming everything works)
- [ ] Remove Solr installation
- [ ] Update documentation

---

## Troubleshooting

### Application won't start

```bash
# Check logs
sudo journalctl -u blake-quarterly -n 50

# Test manually
cd /var/www/quarterly
source venv/bin/activate
python -m app.main
```

### Database connection errors

```bash
# Check PostgreSQL is running
sudo systemctl status postgresql

# Test connection
psql -h localhost -U blake -d blake_quarterly

# Check .env file has correct credentials
cat /var/www/quarterly/.env | grep DATABASE_URL
```

### 502 Bad Gateway (Nginx)

```bash
# Application probably not running
sudo systemctl status blake-quarterly
sudo systemctl start blake-quarterly

# Check if port 8000 is listening
sudo netstat -tlnp | grep 8000
```

### Permission errors

```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/quarterly

# Fix permissions
sudo chmod 755 /var/www/quarterly
sudo chmod 600 /var/www/quarterly/.env
```

---

## Cost Comparison

### Before (PHP + Solr)
- PHP runtime
- Apache Solr (separate JVM, ~512MB RAM)
- Web server
- **Total RAM:** ~1.5-2 GB minimum

### After (Python + PostgreSQL)
- Python runtime
- PostgreSQL (shared with other apps, ~100-200MB)
- Web server
- **Total RAM:** ~1 GB minimum

**You can use a smaller/cheaper server!**

---

## Support

If you encounter issues:

1. Check logs: `sudo journalctl -u blake-quarterly -f`
2. Check this guide's troubleshooting section
3. Verify all services are running
4. Test database connection
5. Check file permissions

## Summary

**Yes, you need infrastructure changes, but they're straightforward:**

1. ✅ Install Python (easy)
2. ✅ Install PostgreSQL (easy)
3. ✅ Configure reverse proxy (one config file)
4. ✅ Setup process manager (one service file)
5. ❌ Remove PHP and Solr (simplification!)

**Result:** Simpler, more maintainable infrastructure with one less service to manage.
