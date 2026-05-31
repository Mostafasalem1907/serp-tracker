<?php

namespace App\Console\Commands;

use App\Jobs\CheckKeywordRankJob;
use App\Models\ActivityLog;
use App\Models\CheckQueueLog;
use App\Models\Keyword;
use App\Models\Project;
use Illuminate\Console\Command;

class CheckAllRanksCommand extends Command
{
    protected $signature   = 'ranks:check-all {--project= : فحص مشروع واحد فقط}';
    protected $description = 'فحص ترتيب جميع الكلمات المفتاحية في المشاريع النشطة';

    /**
     * تشغيل الفحص التلقائي لجميع المشاريع النشطة
     * يُنفَّذ كل 12 ساعة عبر Cron Job
     */
    public function handle(): int
    {
        $projectId = $this->option('project');

        // جلب المشاريع النشطة — أو مشروع واحد لو حُدِّد
        $projectsQuery = Project::where('is_active', true)
            ->with(['keywords' => fn($q) => $q->where('is_active', true)]);

        if ($projectId) {
            $projectsQuery->where('id', $projectId);
        }

        $projects  = $projectsQuery->get();
        $totalJobs = 0;

        foreach ($projects as $project) {
            foreach ($project->keywords as $keyword) {
                // إنشاء سجل في check_queue_logs لمتابعة الحالة
                $queueLog = CheckQueueLog::create([
                    'project_id' => $project->id,
                    'keyword_id' => $keyword->id,
                    'status'     => 'pending',
                    'attempts'   => 0,
                    'created_at' => now(),
                ]);

                // إضافة الـ Job للطابور
                CheckKeywordRankJob::dispatch($keyword, 'auto', $queueLog->id);
                $totalJobs++;
            }
        }

        // تسجيل النشاط في سجل الأنشطة
        ActivityLog::recordSystem(
            action: 'rank.checked_auto',
            notes:  "فحص تلقائي — {$totalJobs} كلمة"
        );

        $this->info("تم إضافة {$totalJobs} كلمة للطابور.");

        return Command::SUCCESS;
    }
}
