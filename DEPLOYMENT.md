# Deployment Guide

## Prerequisites

- PHP 8.5 or higher with extensions:
  - PDO
  - PDO_MySQL
  - OpenSSL
  - Mbstring
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Web server (Apache/Nginx) or PHP built-in server
- SSL certificate (for production)

## Production Deployment Steps

### 1. Server Setup

**On Ubuntu/Debian:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.5
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.5-cli php8.5-fpm php8.5-dev php8.5-mysql php8.5-mbstring php8.5-xml php8.5-curl -y

# Verify installation
php8.5 --version

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx (or Apache)
sudo apt install nginx -y
```

### 2. Application Setup

```bash
# Clone repository
cd /var/www
git clone https://github.com/md-riaz/MailCamp-Larafony.git
cd MailCamp-Larafony

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage

# Configure environment
cp .env.example .env
nano .env
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p
```

```sql
CREATE DATABASE mailcamp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mailcamp_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON mailcamp.* TO 'mailcamp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Update `.env`:
```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mailcamp
DB_USERNAME=mailcamp_user
DB_PASSWORD=secure_password_here
```

Run migrations:
```bash
php cli/migrate.php
```

### 4. Web Server Configuration

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/MailCamp-Larafony/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache Configuration (.htaccess in public/):**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

Enable and restart:
```bash
sudo ln -s /etc/nginx/sites-available/mailcamp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d your-domain.com
sudo systemctl reload nginx
```

### 6. Queue Worker Setup

Create systemd service `/etc/systemd/system/mailcamp-queue.service`:
```ini
[Unit]
Description=MailCamp Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/MailCamp-Larafony
ExecStart=/usr/bin/php /var/www/MailCamp-Larafony/cli/queue-worker.php
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable mailcamp-queue
sudo systemctl start mailcamp-queue
sudo systemctl status mailcamp-queue
```

### 7. Security Hardening

```bash
# Disable directory listing
echo "Options -Indexes" > /var/www/MailCamp-Larafony/public/.htaccess

# Secure file permissions
find /var/www/MailCamp-Larafony -type f -exec chmod 644 {} \;
find /var/www/MailCamp-Larafony -type d -exec chmod 755 {} \;
chmod -R 775 storage

# Set up firewall
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### 8. Monitoring & Logging

```bash
# Set up log rotation
sudo nano /etc/logrotate.d/mailcamp
```

```
/var/www/MailCamp-Larafony/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 9. Backup Strategy

**Automated database backup script:**
```bash
#!/bin/bash
# /usr/local/bin/backup-mailcamp.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/mailcamp"
DB_NAME="mailcamp"
DB_USER="mailcamp_user"
DB_PASS="secure_password_here"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/MailCamp-Larafony

# Delete old backups (keep 30 days)
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Add to crontab:
```bash
sudo crontab -e
# Add daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-mailcamp.sh >> /var/log/mailcamp-backup.log 2>&1
```

## Docker Deployment

**Dockerfile:**
```dockerfile
FROM php:8.5-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data storage

EXPOSE 9000
CMD ["php-fpm"]
```

**docker-compose.yml:**
```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: mailcamp-app
    volumes:
      - ./:/var/www
    networks:
      - mailcamp-network

  nginx:
    image: nginx:alpine
    container_name: mailcamp-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx:/etc/nginx/conf.d
    networks:
      - mailcamp-network

  mysql:
    image: mysql:8.0
    container_name: mailcamp-db
    environment:
      MYSQL_DATABASE: mailcamp
      MYSQL_USER: mailcamp_user
      MYSQL_PASSWORD: secure_password
      MYSQL_ROOT_PASSWORD: root_password
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - mailcamp-network

  queue:
    build: .
    container_name: mailcamp-queue
    command: php cli/queue-worker.php
    volumes:
      - ./:/var/www
    networks:
      - mailcamp-network

networks:
  mailcamp-network:
    driver: bridge

volumes:
  mysql-data:
```

Deploy with Docker:
```bash
docker-compose up -d
docker-compose exec app php cli/migrate.php
```

## Maintenance

### Updates
```bash
cd /var/www/MailCamp-Larafony
git pull origin main
composer install --no-dev --optimize-autoloader
php cli/migrate.php
sudo systemctl restart mailcamp-queue
```

### Monitoring Queue Worker
```bash
# Check status
sudo systemctl status mailcamp-queue

# View logs
sudo journalctl -u mailcamp-queue -f

# Restart
sudo systemctl restart mailcamp-queue
```

### Database Maintenance
```bash
# Optimize tables
mysql -u mailcamp_user -p mailcamp -e "OPTIMIZE TABLE users, organizations, campaigns, recipients, queue_jobs, logs;"

# Check table sizes
mysql -u mailcamp_user -p mailcamp -e "SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = 'mailcamp';"
```

## Troubleshooting

### Queue Not Processing
```bash
# Check queue worker status
sudo systemctl status mailcamp-queue

# View error logs
sudo journalctl -u mailcamp-queue -n 100

# Restart queue worker
sudo systemctl restart mailcamp-queue
```

### Database Connection Issues
```bash
# Test connection
mysql -u mailcamp_user -p -h localhost mailcamp

# Check .env configuration
cat .env | grep DB_
```

### Permission Issues
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

## Performance Optimization

1. **Enable OPcache** in php.ini:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
```

2. **MySQL Optimization** in my.cnf:
```ini
innodb_buffer_pool_size=1G
max_connections=200
query_cache_size=64M
```

3. **Queue Throttling**: Adjust in `.env`:
```
QUEUE_THROTTLE_EMAILS_PER_HOUR=500
```

## Support

For deployment issues:
1. Check logs in `storage/logs/`
2. Review systemd logs: `journalctl -xe`
3. Check web server logs: `/var/log/nginx/error.log`
4. Open an issue on GitHub
