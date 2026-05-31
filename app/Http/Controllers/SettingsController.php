<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Country;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Services\AiAnalysisService;
use App\Services\DataForSeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * عرض لوحة الإعدادات — Admin فقط
     */
    public function index()
    {
        $generalSettings = Setting::where('group', 'general')->get()->keyBy('key');
        $countries        = Country::orderBy('sort_order')->get();
        $users            = User::with('client')->orderBy('name')->get();
        $clients          = Client::where('is_active', true)->orderBy('name')->get();
        $allProjects      = Project::where('is_active', true)->with('client')->orderBy('name')->get();

        return view('settings.index', compact(
            'generalSettings', 'countries', 'users', 'clients', 'allProjects'
        ));
    }

    /**
     * تحديث الإعدادات العامة
     */
    public function updateGeneral(Request $request)
    {
        $data = $request->validate([
            'app_name'                 => 'required|string|max:255',
            'default_timezone'         => 'required|string',
            'default_country_id'       => 'required|exists:countries,id',
            'max_keywords_per_project' => 'required|integer|min:1|max:100',
            'check_interval_hours'     => 'required|integer|min:1|max:24',
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
            ActivityLog::record('settings.updated', null, [], [
                'setting' => $key, 'new_value' => $value,
            ]);
        }

        return back()->with('success', 'تم حفظ الإعدادات العامة');
    }

    /**
     * تحديث إعدادات الـ API
     */
    public function updateApis(Request $request)
    {
        $data = $request->validate([
            'dataforseo_login'    => 'nullable|string',
            'dataforseo_password' => 'nullable|string',
            'anthropic_api_key'   => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                Setting::setValue($key, $value);
                // لا نسجل القيمة الفعلية — نسجل الاسم فقط
                ActivityLog::record('api_key.updated', null, [], ['api_name' => $key]);
            }
        }

        return back()->with('success', 'تم حفظ إعدادات الـ API');
    }

    /**
     * اختبار اتصال DataForSEO (AJAX)
     */
    public function testDataForSeo(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $service = new DataForSeoService();
        $success = $service->testCredentials($request->login, $request->password);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'تم الاتصال بنجاح' : 'فشل الاتصال — تحقق من البيانات',
        ]);
    }

    /**
     * اختبار اتصال Anthropic (AJAX)
     */
    public function testAnthropic(Request $request)
    {
        $request->validate(['api_key' => 'required|string']);

        $service = new AiAnalysisService();
        $success = $service->testKey($request->api_key);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'الـ API key صحيح' : 'الـ API key غير صحيح',
        ]);
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|in:admin,member,client',
            'timezone'  => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = true;
        $data['timezone']  = $data['timezone'] ?? 'Africa/Cairo';

        $user = User::create($data);

        ActivityLog::record('user.created', $user, [], [
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]);

        return back()->with('success', "تم إنشاء حساب {$user->name}");
    }

    /**
     * تحديث بيانات مستخدم
     */
    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'role'       => 'required|in:admin,member,client',
            'timezone'   => 'nullable|string',
            'client_id'  => 'nullable|exists:clients,id',
            'is_active'  => 'boolean',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
        ]);

        $user->update([
            'name'      => $data['name'],
            'role'      => $data['role'],
            'timezone'  => $data['timezone'] ?? $user->timezone,
            'client_id' => $data['client_id'] ?? null,
            'is_active' => $data['is_active'] ?? $user->is_active,
        ]);

        // تحديث المشاريع المُسنَدة للـ member
        if ($user->isMember() && isset($data['project_ids'])) {
            $user->assignedProjects()->sync($data['project_ids']);
        }

        return back()->with('success', 'تم تحديث بيانات المستخدم');
    }

    /**
     * إضافة دولة جديدة
     */
    public function storeCountry(Request $request)
    {
        $data = $request->validate([
            'name_ar'       => 'required|string|max:100',
            'name_en'       => 'required|string|max:100',
            'location_code' => 'required|string|max:20',
            'timezone'      => 'required|string',
            'se_domain'     => 'required|string|max:100',
        ]);

        $data['sort_order'] = Country::max('sort_order') + 1;
        $country = Country::create($data);

        ActivityLog::record('settings.updated', null, [], [
            'setting' => 'country.added', 'country' => $country->name_ar,
        ]);

        return back()->with('success', "تمت إضافة دولة {$country->name_ar}");
    }
}
