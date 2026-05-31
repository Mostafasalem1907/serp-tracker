<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Keyword;
use App\Models\Project;
use App\Models\RankCheck;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية مع بطاقات المشاريع وأفضل الكلمات
     */
    public function index()
    {
        $user = auth()->user();

        // جلب المشاريع حسب دور المستخدم
        $projects = $this->getUserProjects($user)
            ->with(['client', 'country', 'keywords' => fn($q) => $q->where('is_active', true)])
            ->get();

        // إضافة بيانات الترتيب لكل مشروع
        $projects = $projects->map(function ($project) {
            $project->topKeywords = $this->getTopKeywords($project);
            $project->statsToday  = $this->getProjectStats($project);
            return $project;
        });

        // إحصائيات سريعة للـ admin
        $stats = null;
        if ($user->isAdmin()) {
            $stats = [
                'clients_count'  => Client::count(),
                'projects_count' => Project::where('is_active', true)->count(),
                'keywords_count' => Keyword::where('is_active', true)->count(),
            ];
        }

        return view('dashboard', compact('projects', 'stats'));
    }

    /**
     * جلب المشاريع المسموح للمستخدم برؤيتها
     */
    private function getUserProjects($user)
    {
        if ($user->isAdmin()) {
            return Project::where('is_active', true);
        }

        if ($user->isMember()) {
            return $user->assignedProjects()->where('is_active', true);
        }

        // client — يرى مشاريع عميله فقط
        return Project::where('client_id', $user->client_id)->where('is_active', true);
    }

    /**
     * جلب أفضل 5 كلمات مفتاحية للمشروع مع مؤشر التغيير
     */
    private function getTopKeywords(Project $project): array
    {
        $keywords = $project->keywords()
            ->where('is_active', true)
            ->with('latestRankCheck')
            ->get();

        return $keywords
            ->filter(fn($k) => $k->latestRankCheck?->rank !== null)
            ->sortBy('latestRankCheck.rank')
            ->take(5)
            ->map(function ($keyword) {
                // جلب الفحص قبل الأخير لحساب التغيير
                $previousCheck = $keyword->rankChecks()
                    ->where('id', '<', $keyword->latestRankCheck->id)
                    ->orderByDesc('checked_at')
                    ->first();

                $currentRank  = $keyword->latestRankCheck->rank;
                $previousRank = $previousCheck?->rank;

                // حساب اتجاه التغيير
                $change = null;
                $trend  = 'stable';
                if ($previousRank !== null) {
                    $change = $previousRank - $currentRank; // موجب = تحسّن
                    $trend  = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable');
                }

                return [
                    'id'      => $keyword->id,
                    'keyword' => $keyword->keyword,
                    'rank'    => $currentRank,
                    'change'  => $change,
                    'trend'   => $trend,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * إحصائيات اليوم للمشروع
     */
    private function getProjectStats(Project $project): array
    {
        $keywordIds  = $project->keywords()->pluck('id');
        $todayChecks = RankCheck::whereIn('keyword_id', $keywordIds)
            ->whereDate('checked_at', today())
            ->count();

        return [
            'checked_today'  => $todayChecks,
            'total_keywords' => $project->keywords->count(),
        ];
    }
}
