<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Country;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * قائمة المشاريع — تُفلتر حسب دور المستخدم
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $projects = Project::with(['client', 'country'])->withCount('keywords')->paginate(15);
        } elseif ($user->isMember()) {
            $projects = $user->assignedProjects()->with(['client', 'country'])->withCount('keywords')->paginate(15);
        } else {
            $projects = Project::where('client_id', $user->client_id)->with(['client', 'country'])->withCount('keywords')->paginate(15);
        }

        return view('projects.index', compact('projects'));
    }

    /**
     * نموذج إضافة مشروع جديد
     */
    public function create()
    {
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();

        return view('projects.create', compact('clients', 'countries'));
    }

    /**
     * حفظ مشروع جديد
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'name'       => 'required|string|max:255',
            'domain'     => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'language'   => 'required|in:ar,en',
            'device'     => 'required|in:desktop,mobile',
        ]);

        $project = Project::create($data);

        ActivityLog::record('project.created', $project, [], array_merge($data, ['client_name' => $project->client->name]));

        return redirect()->route('projects.show', $project)->with('success', 'تم إنشاء المشروع بنجاح');
    }

    /**
     * عرض تفاصيل المشروع مع الكلمات والإحصائيات
     */
    public function show(Project $project)
    {
        $this->authorizeProjectAccess($project);

        $project->load(['client', 'country', 'keywords.latestRankCheck']);

        // بيانات الرسم البياني — آخر 30 يوم لكل كلمة
        $chartData = $this->buildChartData($project);

        $activityLogs = \App\Models\ActivityLog::where('model_type', 'Project')
            ->where('model_id', $project->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $aiAnalyses = $project->aiAnalyses()->orderByDesc('created_at')->take(5)->get();

        return view('projects.show', compact('project', 'chartData', 'activityLogs', 'aiAnalyses'));
    }

    /**
     * نموذج تعديل المشروع
     */
    public function edit(Project $project)
    {
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();

        return view('projects.edit', compact('project', 'clients', 'countries'));
    }

    /**
     * تحديث بيانات المشروع
     */
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'name'       => 'required|string|max:255',
            'domain'     => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'language'   => 'required|in:ar,en',
            'device'     => 'required|in:desktop,mobile',
            'is_active'  => 'boolean',
        ]);

        $oldValues = $project->toArray();
        $project->update($data);

        ActivityLog::record('project.updated', $project, $oldValues, $project->fresh()->toArray());

        return redirect()->route('projects.show', $project)->with('success', 'تم تحديث المشروع');
    }

    /**
     * حذف المشروع (soft delete)
     */
    public function destroy(Project $project)
    {
        $keywordsCount = $project->keywords()->count();
        ActivityLog::record('project.deleted', $project, $project->toArray(), [], "حذف — {$keywordsCount} كلمة");
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'تم حذف المشروع');
    }

    /**
     * بناء بيانات الرسم البياني لآخر 30 يوم
     * الـ labels بأسماء شهور عربية حقيقية مثل "1 يونيو"
     */
    private function buildChartData(Project $project): array
    {
        // أسماء الشهور بالعربية
        $monthNames = [
            1  => 'يناير', 2  => 'فبراير', 3  => 'مارس',
            4  => 'أبريل', 5  => 'مايو',   6  => 'يونيو',
            7  => 'يوليو', 8  => 'أغسطس', 9  => 'سبتمبر',
            10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        $since  = now()->subDays(29)->startOfDay();
        $days   = [];
        $labels = [];

        // بناء قائمة آخر 30 يوم مع label لكل يوم
        for ($i = 29; $i >= 0; $i--) {
            $date   = now()->subDays($i);
            $key    = $date->format('Y-m-d');    // مفتاح الـ lookup
            $day    = (int) $date->format('j');  // رقم اليوم بدون صفر
            $month  = (int) $date->format('n');  // رقم الشهر

            $days[] = $key;

            // نعرض الـ label فقط لو أول يوم في الشهر أو أول يوم في البيانات
            // باقي الأيام نعرض رقم اليوم فقط بدون اسم الشهر لتجنب الازدحام
            if ($i === 29 || $day === 1) {
                $labels[] = $day . ' ' . $monthNames[$month];
            } else {
                $labels[] = (string) $day;
            }
        }

        $datasets = [];

        foreach ($project->keywords->take(10) as $keyword) {
            // جلب كل الفحوصات في الـ 30 يوم
            $checks = $keyword->rankChecks()
                ->where('checked_at', '>=', $since)
                ->orderBy('checked_at')
                ->get(['rank', 'checked_at']);

            // لكل يوم: خذ آخر فحص فيه (لو أكثر من فحص في نفس اليوم)
            $byDay = [];
            foreach ($checks as $check) {
                $d = $check->checked_at->format('Y-m-d');
                // آخر فحص في اليوم يكسب
                $byDay[$d] = $check->rank;
            }

            // بناء array مرتب بنفس ترتيب الـ days
            $data = array_map(fn($day) => $byDay[$day] ?? null, $days);

            $datasets[] = [
                'label'  => $keyword->keyword,
                'device' => $keyword->effective_device,
                'data'   => $data,
            ];
        }

        return [
            'labels'   => $labels,
            'days'     => $days,     // ISO dates للـ tooltip
            'datasets' => $datasets,
        ];
    }

    /**
     * التحقق من صلاحية وصول المستخدم للمشروع
     */
    private function authorizeProjectAccess(Project $project): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isMember() && $user->assignedProjects()->where('id', $project->id)->exists()) {
            return;
        }

        if ($user->isClient() && $project->client_id === $user->client_id) {
            return;
        }

        abort(403, 'غير مصرح لك بالوصول لهذا المشروع');
    }
}
