<?php

namespace App\Http\Controllers;

use App\Jobs\CheckKeywordRankJob;
use App\Models\ActivityLog;
use App\Models\CheckQueueLog;
use App\Models\Keyword;
use App\Models\Project;
use App\Models\RankCheck;
use App\Models\Setting;
use App\Services\DataForSeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KeywordController extends Controller
{
    /**
     * إضافة كلمة مفتاحية جديدة للمشروع
     */
    public function store(Request $request, Project $project)
    {
        $maxKeywords = (int) Setting::getValue('max_keywords_per_project', 20);

        if ($project->keywords()->where('is_active', true)->count() >= $maxKeywords) {
            return back()->withErrors(['keyword' => "الحد الأقصى للكلمات هو {$maxKeywords} كلمة لكل مشروع"]);
        }

        $data = $request->validate([
            'keyword' => 'required|string|max:255',
            'device'  => 'nullable|in:desktop,mobile',
            'tags'    => 'nullable|array',
            'tags.*'  => 'string|max:50',
        ]);

        // لو مش محدد يرث من المشروع (null)
        if (empty($data['device'])) {
            $data['device'] = null;
        }

        $keyword = $project->keywords()->create($data);

        ActivityLog::record('keyword.added', $keyword, [], [
            'keyword'      => $keyword->keyword,
            'project_name' => $project->name,
        ]);

        return back()->with('success', "تمت إضافة الكلمة: {$keyword->keyword}");
    }

    /**
     * حذف كلمة مفتاحية (soft delete)
     */
    public function destroy(Keyword $keyword)
    {
        $lastRank = $keyword->latestRankCheck?->rank;
        ActivityLog::record('keyword.deleted', $keyword, $keyword->toArray(), [], "آخر ترتيب: {$lastRank}");
        $keyword->delete();

        return back()->with('success', 'تم حذف الكلمة المفتاحية');
    }

    /**
     * فحص يدوي فوري لكلمة واحدة — يعمل SYNCHRONOUS (بدون queue)
     * يرجع JSON مباشرة بالنتيجة أو الخطأ
     */
    public function checkNow(Keyword $keyword, DataForSeoService $dataForSeo)
    {
        // التحقق من وجود API credentials
        $login = Setting::getValue('dataforseo_login');
        if (!$login) {
            return response()->json([
                'success' => false,
                'error'   => 'لم يتم إعداد DataForSEO API بعد — اذهب للإعدادات',
            ], 422);
        }

        try {
            // فحص مباشر بدون queue — النتيجة فورية
            $result = $dataForSeo->checkKeyword($keyword);

            // حفظ نتيجة الفحص
            $check = RankCheck::create([
                'keyword_id' => $keyword->id,
                'rank'       => $result['rank'],
                'url'        => $result['url'],
                'title'      => $result['title'],
                'source'     => 'manual',
                'checked_at' => now(),
            ]);

            // جلب Volume/CPC لو أول مرة
            if ($keyword->search_volume === null) {
                $metrics = $dataForSeo->fetchKeywordMetrics($keyword);
                if (array_filter($metrics)) {
                    $keyword->update(array_filter($metrics, fn($v) => $v !== null));
                    $keyword->refresh();
                }
            }

            ActivityLog::record('rank.checked_manual', $keyword, [], [
                'keyword' => $keyword->keyword,
                'rank'    => $result['rank'],
            ]);

            // حساب التغيير مقارنةً بالفحص السابق
            $previousCheck = RankCheck::where('keyword_id', $keyword->id)
                ->where('id', '<', $check->id)
                ->orderByDesc('checked_at')
                ->first();

            $change = null;
            $trend  = 'stable';
            if ($previousCheck?->rank && $result['rank']) {
                $change = $previousCheck->rank - $result['rank'];
                $trend  = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable');
            }

            return response()->json([
                'success'       => true,
                'rank'          => $result['rank'],
                'url'           => $result['url'],
                'title'         => $result['title'],
                'change'        => $change,
                'trend'         => $trend,
                'search_volume' => $keyword->search_volume,
                'cpc'           => $keyword->cpc,
                'competition'   => $keyword->competition,
                'checked_at'    => now()->diffForHumans(),
                'message'       => $result['rank']
                    ? "المرتبة {$result['rank']}"
                    : 'خارج أول 100 نتيجة',
            ]);

        } catch (\Exception $e) {
            Log::error('Manual keyword check failed', [
                'keyword' => $keyword->keyword,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'فشل الفحص: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * فحص كل كلمات المشروع — يضيفها للـ queue (للعمليات الكبيرة)
     */
    public function checkProjectNow(Project $project)
    {
        $keywords      = $project->keywords()->where('is_active', true)->get();
        $totalKeywords = $keywords->count();

        foreach ($keywords as $keyword) {
            $queueLog = CheckQueueLog::create([
                'project_id' => $project->id,
                'keyword_id' => $keyword->id,
                'status'     => 'pending',
                'created_at' => now(),
            ]);
            CheckKeywordRankJob::dispatch($keyword, 'manual', $queueLog->id);
        }

        ActivityLog::record('rank.checked_manual', $project, [], [
            'triggered'      => auth()->user()->name,
            'keywords_count' => $totalKeywords,
        ]);

        return back()->with('success', "تم إضافة {$totalKeywords} كلمة للطابور — شغّل queue worker لمعالجتها");
    }

    /**
     * بيانات الترتيب التاريخية لكلمة واحدة (AJAX)
     */
    public function history(Keyword $keyword)
    {
        $checks = $keyword->rankChecks()
            ->where('checked_at', '>=', now()->subDays(30))
            ->orderBy('checked_at')
            ->get(['rank', 'checked_at'])
            ->map(fn($c) => [
                'date' => $c->checked_at->format('Y-m-d'),
                'rank' => $c->rank,
            ]);

        return response()->json($checks);
    }
}
