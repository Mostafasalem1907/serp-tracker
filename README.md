# 🔍 SERP Rank Tracker

> تطبيق احترافي لمتابعة ترتيب الكلمات المفتاحية في نتائج Google — مبني بـ Laravel 11

---

## ✨ المميزات

- **📊 متابعة الترتيب** — فحص يدوي أو تلقائي لكل كلمة مفتاحية عبر DataForSEO API
- **📱🖥️ Desktop & Mobile** — كل كلمة لها جهاز مستقل أو ترث من المشروع
- **📈 رسم بياني تاريخي** — آخر 30 يوم مع أسماء شهور عربية
- **🤖 تحليل AI** — تقارير ذكية بـ Claude AI (Anthropic)
- **🔎 فلتر ذكي** — بحث + جهاز + ترتيب + اتجاه + صعوبة KD
- **👥 أدوار متعددة** — Admin / Member / Client
- **🏢 Multi-Client** — إدارة عملاء ومشاريع متعددة
- **🧭 Sidebar** — تنقل سريع بين المشاريع
- **⚡ Setup Wizard** — إعداد فوري عند أول تشغيل
- **🌍 دعم دول متعددة** — كود الدولة + محرك البحث لكل دولة

---

## 🛠️ التقنيات

| الجانب | التقنية |
|--------|---------|
| Backend | Laravel 11 / PHP 8.2 |
| Frontend | Blade + Alpine.js + Tailwind CSS (CDN) |
| Charts | Chart.js (CDN) |
| Database | SQLite (zero-config) |
| SERP API | DataForSEO |
| AI | Anthropic Claude (claude-sonnet) |
| Queue | Laravel Database Queue |

---

## 🚀 تشغيل على الجهاز المحلي

```bash
# 1. clone المشروع
git clone https://github.com/Mostafasalem1907/serp-tracker.git
cd serp-tracker

# 2. تثبيت الـ packages
composer install

# 3. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 4. إنشاء قاعدة البيانات
touch database/database.sqlite
php artisan migrate --seed

# 5. تشغيل السيرفر
php artisan serve
```

افتح `http://localhost:8000` — سيُحوَّل تلقائياً لـ Setup Wizard لإعداد الأدمن.

---

## ⚙️ متغيرات البيئة المهمة

```env
# التطبيق
APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false

# قاعدة البيانات (SQLite افتراضياً)
DB_CONNECTION=sqlite

# DataForSEO
DATAFORSEO_LOGIN=your_login@email.com
DATAFORSEO_PASSWORD=your_password

# Anthropic Claude AI
ANTHROPIC_API_KEY=sk-ant-...
```

> ⚠️ **لا ترفع ملف `.env` أبداً على GitHub** — يحتوي على بيانات حساسة

---

## 📂 هيكل المشروع

```
app/
├── Http/Controllers/
│   ├── ProjectController.php     # إدارة المشاريع + الرسم البياني
│   ├── KeywordController.php     # إضافة + فحص + حذف الكلمات
│   ├── ClientController.php      # إدارة العملاء
│   ├── SettingsController.php    # إعدادات النظام
│   ├── AiAnalysisController.php  # تحليل AI
│   └── InstallController.php     # Setup Wizard
├── Models/
│   ├── Keyword.php    # effective_device + device_label
│   ├── Project.php
│   ├── RankCheck.php
│   └── Setting.php    # API keys مشفرة بـ Crypt
└── Services/
    ├── DataForSeoService.php   # SERP API + Keywords Metrics
    └── AiAnalysisService.php   # Claude API

resources/views/
├── projects/show.blade.php   # الصفحة الرئيسية للمشروع
├── layouts/app.blade.php     # Layout مع Sidebar
└── install/                  # Setup Wizard
```

---

## 🌐 النشر على cPanel

راجع ملف [`DEPLOY.md`](DEPLOY.md) للتعليمات الكاملة خطوة بخطوة.

**الخلاصة السريعة:**
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate --force
php artisan config:cache && php artisan route:cache
```

**Cron Job للفحص التلقائي:**
```
* * * * * php /home/user/serp-tracker/artisan schedule:run >> /dev/null 2>&1
```

---

## 📸 الشاشات

| الشاشة | الوصف |
|--------|-------|
| Dashboard | ملخص عام للمشاريع والكلمات |
| Projects / Show | جدول الكلمات + فلتر ذكي + رسم بياني |
| Settings | إدخال API keys + إعدادات النظام |
| Setup Wizard | إعداد أول مستخدم وربط الـ APIs |

---

## 📋 Roadmap

- [ ] تقارير PDF تلقائية أسبوعية
- [ ] إشعارات Email عند تغيير الترتيب
- [ ] مقارنة بين فترتين زمنيتين
- [ ] دعم Bing + Yahoo
- [ ] API للتكامل مع أدوات خارجية

---

## 📄 الترخيص

مشروع خاص — جميع الحقوق محفوظة.
