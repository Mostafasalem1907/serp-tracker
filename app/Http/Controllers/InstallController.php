<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    /**
     * الخطوة 1 — نموذج إعداد قاعدة البيانات
     */
    public function step1()
    {
        return view('install.step1');
    }

    /**
     * اختبار الاتصال بقاعدة البيانات (AJAX)
     */
    public function testDatabase(Request $request)
    {
        $request->validate([
            'db_host'     => 'required|string',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            // محاولة الاتصال بالـ DB بالبيانات المُدخلة
            $pdo = new \PDO(
                "mysql:host={$request->db_host};dbname={$request->db_database}",
                $request->db_username,
                $request->db_password ?? '',
                [\PDO::ATTR_TIMEOUT => 5]
            );

            return response()->json(['success' => true, 'message' => 'تم الاتصال بقاعدة البيانات بنجاح']);

        } catch (\PDOException $e) {
            return response()->json(['success' => false, 'message' => 'فشل الاتصال: ' . $e->getMessage()]);
        }
    }

    /**
     * حفظ إعدادات DB والانتقال للخطوة 2
     */
    public function saveStep1(Request $request)
    {
        $data = $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|string',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        // كتابة بيانات DB في ملف .env
        $this->updateEnvFile([
            'DB_HOST'     => $data['db_host'],
            'DB_PORT'     => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ]);

        return redirect()->route('install.step2');
    }

    /**
     * الخطوة 2 — نموذج إعداد الحساب الرئيسي والـ API keys
     */
    public function step2()
    {
        return view('install.step2');
    }

    /**
     * إتمام التثبيت — تشغيل migrate وإنشاء الحساب
     */
    public function complete(Request $request)
    {
        $data = $request->validate([
            'admin_name'          => 'required|string|max:255',
            'admin_email'         => 'required|email',
            'admin_password'      => 'required|string|min:8|confirmed',
            'dataforseo_login'    => 'nullable|string',
            'dataforseo_password' => 'nullable|string',
            'anthropic_api_key'   => 'nullable|string',
        ]);

        try {
            // تشغيل migrate لإنشاء كل الجداول
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--class' => 'CountrySeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);

            // إنشاء حساب الـ Admin
            User::create([
                'name'      => $data['admin_name'],
                'email'     => $data['admin_email'],
                'password'  => Hash::make($data['admin_password']),
                'role'      => 'admin',
                'timezone'  => 'Africa/Cairo',
                'is_active' => true,
            ]);

            // حفظ الـ API keys المشفرة
            if (!empty($data['dataforseo_login'])) {
                Setting::setValue('dataforseo_login', $data['dataforseo_login']);
            }
            if (!empty($data['dataforseo_password'])) {
                Setting::setValue('dataforseo_password', $data['dataforseo_password']);
            }
            if (!empty($data['anthropic_api_key'])) {
                Setting::setValue('anthropic_api_key', $data['anthropic_api_key']);
            }

            // إنشاء ملف install.lock لمنع إعادة تشغيل الـ wizard
            file_put_contents(storage_path('install.lock'), date('Y-m-d H:i:s'));

            return redirect('/login')->with('success', 'تم تثبيت التطبيق بنجاح! يمكنك تسجيل الدخول الآن.');

        } catch (\Exception $e) {
            Log::error('Install failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'فشل التثبيت: ' . $e->getMessage()]);
        }
    }

    /**
     * تحديث ملف .env بالقيم المطلوبة
     */
    private function updateEnvFile(array $values): void
    {
        $envPath    = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            // تنظيف القيمة من علامات الاقتباس
            $escapedValue = addslashes($value);

            if (str_contains($envContent, "{$key}=")) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escapedValue}",
                    $envContent
                );
            } else {
                $envContent .= "\n{$key}={$escapedValue}";
            }
        }

        file_put_contents($envPath, $envContent);

        // إعادة تحميل الـ .env
        Artisan::call('config:clear');
    }
}
