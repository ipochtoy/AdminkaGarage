# 🚀 Инструкция по развертыванию AdminkaGarage

## Для твоего друга

Привет! Эта инструкция поможет тебе развернуть проект на своем сервере.

## 📋 Что нужно подготовить

### 1. Сервер
- Ubuntu 22.04 / 24.04 (рекомендуется)
- Минимум 2GB RAM
- 20GB свободного места
- Root или sudo доступ

### 2. Домен (опционально)
- Если хочешь развернуть на домене, подготовь его заранее
- Настрой DNS A-запись на IP сервера

## 🛠️ Быстрый старт

### Шаг 1: Подключись к серверу
```bash
ssh root@your-server-ip
```

### Шаг 2: Установи зависимости
```bash
# Обновляем систему
apt update && apt upgrade -y

# Устанавливаем PHP 8.3
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring \
    php8.3-curl php8.3-zip php8.3-gd php8.3-imagick php8.3-intl php8.3-bcmath \
    php8.3-soap php8.3-dev php8.3-common php8.3-redis

# Устанавливаем Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Устанавливаем Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Устанавливаем MySQL
apt install -y mysql-server
mysql_secure_installation

# Устанавливаем Nginx
apt install -y nginx

# Устанавливаем Supervisor (для фоновых задач)
apt install -y supervisor

# Устанавливаем Redis (опционально, для кеша)
apt install -y redis-server
```

### Шаг 3: Создай базу данных
```bash
mysql -u root -p
```

В MySQL консоли:
```sql
CREATE DATABASE adminkagarage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'adminkagarage'@'localhost' IDENTIFIED BY 'твой_пароль';
GRANT ALL PRIVILEGES ON adminkagarage.* TO 'adminkagarage'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Шаг 4: Распакуй проект
```bash
# Создай директорию
mkdir -p /var/www
cd /var/www

# Распакуй ZIP
unzip AdminkaGarage.zip

# Установи права
chown -R www-data:www-data /var/www/AdminkaGarage
chmod -R 755 /var/www/AdminkaGarage
chmod -R 775 /var/www/AdminkaGarage/storage
chmod -R 775 /var/www/AdminkaGarage/bootstrap/cache
```

### Шаг 5: Настрой проект
```bash
cd /var/www/AdminkaGarage

# Установи зависимости Composer
composer install --no-dev --optimize-autoloader

# Установи зависимости NPM и собери фронт
npm install
npm run build

# Скопируй .env
cp .env.example .env

# Сгенерируй ключ приложения
php artisan key:generate

# Создай symlink для storage
php artisan storage:link
```

### Шаг 6: Настрой .env
```bash
nano .env
```

Измени следующие параметры:
```env
APP_NAME="AdminkaGarage"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com  # или IP адрес

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adminkagarage
DB_USERNAME=adminkagarage
DB_PASSWORD=твой_пароль

# ОБЯЗАТЕЛЬНО укажи API ключи:
GEMINI_API_KEY=твой_ключ_gemini
EBAY_APP_ID=твой_ebay_app_id
EBAY_CERT_ID=твой_ebay_cert_id
UPC_API_KEY=твой_upc_api_key
```

### Шаг 7: Запусти миграции
```bash
php artisan migrate --force
```

### Шаг 8: Создай админа
```bash
php artisan make:filament-user
```
Введи email, имя и пароль для админа.

### Шаг 9: Оптимизируй для production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
```

### Шаг 10: Настрой Nginx
```bash
nano /etc/nginx/sites-available/adminkagarage
```

Вставь:
```nginx
server {
    listen 80;
    server_name your-domain.com;  # или IP адрес
    root /var/www/AdminkaGarage/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Увеличиваем лимиты для загрузки файлов
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Увеличиваем таймауты для AI-генерации
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Активируй конфиг:
```bash
ln -s /etc/nginx/sites-available/adminkagarage /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

### Шаг 11: Настрой Supervisor для Queue Worker
```bash
nano /etc/supervisor/conf.d/adminkagarage-worker.conf
```

Вставь:
```ini
[program:adminkagarage-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/AdminkaGarage/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/AdminkaGarage/storage/logs/worker.log
stopwaitsecs=3600
```

Запусти:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start adminkagarage-worker:*
```

### Шаг 12: Настрой Cron для Scheduler
```bash
crontab -e -u www-data
```

Добавь:
```cron
* * * * * cd /var/www/AdminkaGarage && php artisan schedule:run >> /dev/null 2>&1
```

### Шаг 13: Настрой SSL (опционально, но рекомендуется)
```bash
# Установи Certbot
apt install -y certbot python3-certbot-nginx

# Получи SSL сертификат
certbot --nginx -d your-domain.com

# Автопродление будет настроено автоматически
```

## ✅ Проверка

Открой в браузере:
- `http://your-domain.com/admin` (или `http://your-server-ip/admin`)
- Войди с данными админа

## 🔧 Настройка PHP для больших файлов

Если планируешь загружать много фото, увеличь лимиты в PHP:

```bash
nano /etc/php/8.3/fpm/php.ini
```

Найди и измени:
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M
```

Перезапусти PHP-FPM:
```bash
systemctl restart php8.3-fpm
```

## 🔐 Безопасность

1. **Измени пароли**:
   - MySQL root пароль
   - Админ пароль в Filament
   - Все API ключи в `.env`

2. **Настрой файрвол**:
```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
```

3. **Включи fail2ban**:
```bash
apt install -y fail2ban
systemctl enable fail2ban
systemctl start fail2ban
```

## 📊 Мониторинг

### Проверь статус сервисов:
```bash
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mysql
supervisorctl status
```

### Проверь логи:
```bash
# Логи Laravel
tail -f /var/www/AdminkaGarage/storage/logs/laravel.log

# Логи Nginx
tail -f /var/log/nginx/error.log

# Логи Worker
tail -f /var/www/AdminkaGarage/storage/logs/worker.log
```

## 🔄 Обновление проекта

Когда получишь обновления от друга:

```bash
cd /var/www/AdminkaGarage

# Сделай backup БД
php artisan db:backup

# Получи обновления
git pull origin main
# или распакуй новый ZIP

# Обнови зависимости
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Запусти миграции
php artisan migrate --force

# Очисти кеш
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Закешируй заново
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# Перезапусти worker
supervisorctl restart adminkagarage-worker:*

# Перезапусти PHP-FPM
systemctl restart php8.3-fpm
```

## 🆘 Частые проблемы

### Проблема: "500 Internal Server Error"
```bash
# Проверь права
chown -R www-data:www-data /var/www/AdminkaGarage
chmod -R 775 /var/www/AdminkaGarage/storage

# Проверь логи
tail -f /var/www/AdminkaGarage/storage/logs/laravel.log
```

### Проблема: "Permission denied" для storage
```bash
chmod -R 775 /var/www/AdminkaGarage/storage
chmod -R 775 /var/www/AdminkaGarage/bootstrap/cache
chown -R www-data:www-data /var/www/AdminkaGarage
```

### Проблема: Не работают фоновые задачи
```bash
# Проверь worker
supervisorctl status adminkagarage-worker:*

# Перезапусти
supervisorctl restart adminkagarage-worker:*

# Проверь логи
tail -f /var/www/AdminkaGarage/storage/logs/worker.log
```

### Проблема: Не загружаются изображения
```bash
# Пересоздай symlink
php artisan storage:link

# Проверь права
ls -la /var/www/AdminkaGarage/public/storage
```

## 📞 Нужна помощь?

Если что-то не получается:
1. Проверь логи: `storage/logs/laravel.log`
2. Проверь конфигурацию Nginx: `nginx -t`
3. Проверь статус сервисов: `systemctl status [service]`
4. Напиши автору проекта

## 🎉 Готово!

Теперь у тебя развернут полнофункциональный AdminkaGarage!

Админка доступна по адресу: `http://your-domain.com/admin`

**Удачи!** 🚀


