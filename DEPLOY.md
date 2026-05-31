# دليل النشر على cPanel

## المتطلبات
- PHP 8.2+
- Extensions: pdo_sqlite, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, curl
- Composer (أو رفع vendor يدوياً)

---

## خطوات النشر

### 1. رفع الملفات
```bash
git clone https://github.com/YOUR_USERNAME/serp-tracker.git
```
أو ارفع الـ ZIP وفك الضغط في مجلد خارج `public_html`.

### 2. ضبط المسار
في cPanel → **Domains** أو **Subdomains**: وجّه الـ Document Root إلى مجلد `public/` بداخل المشروع.

### 3. تثبيت الـ packages
```bash
composer install --no-dev --optimize-autoloader
```

### 4. إعداد البيئة
```bash
cp .env.example .env
php artisan key:generate
```
ثم عدّل `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite

DATAFORSEO_LOGIN=your_login
DATAFORSEO_PASSWORD=your_password
ANTHROPIC_API_KEY=your_key
```

### 5. إنشاء قاعدة البيانات
```bash
touch database/database.sqlite
php artisan migrate --force
```

### 6. الصلاحيات
```bash
chmod -R 775 storage bootstrap/cache
```

### 7. الـ Cron Job (فحص تلقائي)
في cPanel → **Cron Jobs** أضف:
```
* * * * * /usr/local/bin/php /home/USERNAME/serp-tracker/artisan schedule:run >> /dev/null 2>&1
```

### 8. تشغيل الـ Queue (اختياري للفحص الجماعي)
```
* * * * * /usr/local/bin/php /home/USERNAME/serp-tracker/artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### 9. Optimize للإنتاج
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10. أول دخول
افتح الموقع — ستُحوَّل تلقائياً لـ **Setup Wizard** لإعداد الأدمن وإدخال الـ API keys.

---

## ملاحظات
- ملف `.env` **لا يُرفع** على GitHub أبداً
- ملف قاعدة البيانات `database/database.sqlite` **لا يُرفع** — يُنشأ على السيرفر
- بعد كل تحديث من GitHub: `php artisan migrate --force && php artisan config:cache`
