#!/bin/bash
set -e

VPS_HOST="vps-ideologywealth"
VPS_DIR="/var/www/ideologywealthadvisors"
DOMAIN="62.72.7.44"  # Change to your domain when ready

echo "🚀 Deploying Ideology Wealth Advisors PWA to VPS..."

# 1. Prepare VPS
echo "📦 Setting up VPS environment..."
ssh $VPS_HOST << 'ENDSSH'
# Install nginx and PHP if needed
if ! systemctl is-active --quiet nginx; then
    apt-get update -qq
    apt-get install -y nginx php-fpm php-mysql php-mbstring php-xml php-curl
fi

# Install PHP-FPM if not installed
if ! systemctl is-active --quiet php*-fpm; then
    apt-get install -y php-fpm php-mysql php-mbstring php-xml php-curl
fi

# Create web directory
mkdir -p /var/www/ideologywealthadvisors
mkdir -p /var/www/ideologywealthadvisors/api
chown -R www-data:www-data /var/www/ideologywealthadvisors
chmod -R 755 /var/www/ideologywealthadvisors
chmod -R 775 /var/www/ideologywealthadvisors/api
ENDSSH

# 2. Upload files
echo "📤 Uploading files..."
rsync -avz --delete \
    --exclude '.git' \
    --exclude '*.bak' \
    --exclude '*.backup' \
    --exclude '*.sh' \
    --exclude '*.py' \
    --exclude '*.md' \
    --exclude 'app.yaml' \
    --exclude 'firebase.json' \
    --exclude 'netlify.toml' \
    --exclude 'vercel.json' \
    --exclude 'pwa-checker.html' \
    --exclude 'pwa-code-snippet.html' \
    --exclude 'generate-*.html' \
    --exclude 'generate-*.py' \
    --exclude 'tools-menu.sh' \
    ./ $VPS_HOST:$VPS_DIR/

# Fix permissions
echo "🔐 Setting correct permissions..."
ssh $VPS_HOST << 'ENDSSH'
cd /var/www/ideologywealthadvisors
chmod -R 755 .
chmod -R 775 api
chown -R www-data:www-data .
ENDSSH

# 3. Configure Nginx
echo "⚙️  Configuring Nginx..."
ssh $VPS_HOST << ENDSSH
# Only create initial config if it doesn't exist
if [ ! -f /etc/nginx/sites-available/ideologywealthadvisors ]; then
  echo "Creating initial Nginx configuration..."
  cat > /etc/nginx/sites-available/ideologywealthadvisors << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name srv1131803.hstgr.cloud www.srv1131803.hstgr.cloud 62.72.7.44;
    
    root /var/www/ideologywealthadvisors;
    index index.html login.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # PWA specific
    add_header Cache-Control "no-cache" always;
    
    # Service Worker
    location /service-worker.js {
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
    }
    
    # Manifest
    location /manifest.json {
        add_header Content-Type "application/manifest+json";
        add_header Cache-Control "no-cache";
    }
    
    # PHP API
    location /api/ {
        try_files \$uri \$uri/ =404;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Main location
    location / {
        try_files \$uri \$uri/ /index.html;
    }
    
    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # PHP files
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;
}
EOF

  # Enable site
  ln -sf /etc/nginx/sites-available/ideologywealthadvisors /etc/nginx/sites-enabled/
  rm -f /etc/nginx/sites-enabled/default
else
  echo "Nginx configuration already exists, skipping..."
fi

# Test and reload Nginx
nginx -t
systemctl enable nginx
systemctl reload nginx
ENDSSH

echo "✅ Deployment complete!"
echo "🌐 Your PWA is now available at: http://$DOMAIN"
echo ""
echo "📝 Next steps:"
echo "   1. Visit http://$DOMAIN to verify"
echo "   2. Configure your domain DNS to point to $DOMAIN"
echo "   3. Setup SSL certificate with: ssh $VPS_HOST 'certbot --nginx -d yourdomain.com'"
