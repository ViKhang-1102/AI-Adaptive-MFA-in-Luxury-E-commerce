#!/bin/sh
set -e

if [ -z "$PORT" ]; then
  export PORT=10000
fi

echo "Starting Laravel app on port ${PORT}"

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

for i in 1 2 3 4 5; do
  if php artisan migrate --force; then
    break
  fi
  echo "Database migration failed, retrying in 5 seconds..."
  sleep 5
  if [ "$i" -eq 5 ]; then
    echo "Migration failed after 5 attempts"
    exit 1
  fi
done

cat > /etc/apache2/ports.conf <<EOF
Listen ${PORT}
EOF

cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:${PORT}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

exec apache2-foreground
