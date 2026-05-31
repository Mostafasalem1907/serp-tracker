<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\CheckQueueLog;
use App\Models\Keyword;
use App\Models\RankCheck;
use App\Services\DataForSeoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckKeywordRankJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        private Keyword $keyword,
        private string  $source = 'auto', // auto أو manual
        private ?int    $queueLogId = null
    ) {}

    /**
     * تنفيذ فحص ترتيب الكلمة المفتاحية عبر DataForSEO
     */
    public function handle(DataForSeoService $dataForSeo): void
    {
        // تحديث حالة الـ queue log لـ running
        $this->updateQueueLog('running');

        try {
            // استدعاء DataForSEO API لفحص الترتيب
            $result = $dataForSeo->checkKeyword($this->keyword);

            // حفظ نتيجة الفحص في جدول rank_checks
            RankCheck::create([
                'keyword_id' => $this->keyword->id,
                'rank'       => $result['rank'],
                'url'        => $result['url'],
                'title'      => $result['title'],
                'source'     => $this->source,
                'checked_at' => now(),
            ]);

            // جلب بيانات Volume و CPC لو ما اتجلبوش قبل كده
            // SERP endpoint مش بيرجع volume — بنستخدم keywords_data endpoint منفصل
            if ($this->keyword->search_volume === null) {
                $metrics = $dataForSeo->fetchKeywordMetrics($this->keyword);
                $this->keyword->update(array_filter($metrics, fn($v) => $v !== null));
            }

            // تحديث حالة الـ queue log لـ done
            $this->updateQueueLog('done');

            Log::info('Keyword rank checked', [
                'keyword' => $this->keyword->keyword,
                'rank'    => $result['rank'],
                'source'  => $this->source,
            ]);

        } catch (\Exception $e) {
            Log::error('CheckKeywordRankJob failed', [
                'keyword' => $this->keyword->keyword,
                'error'   => $e->getMessage(),
            ]);

            // تحديث حالة الـ queue log لـ failed
            $this->updateQueueLog('failed', $e->getMessage());

            throw $e; // إعادة رمي الخطأ ليعيد الـ queue المحاولة
        }
    }

    /**
     * تسجيل فشل الـ Job بعد استنفاد المحاولات
     */
    public function failed(\Throwable $exception): void
    {
        $this->updateQueueLog('failed', $exception->getMessage());
    }

    /**
     * تحديث سجل الطابور بالحالة الحالية
     */
    private function updateQueueLog(string $status, string $errorMessage = null): void
    {
        if (!$this->queueLogId) {
            return;
        }

        $log = CheckQueueLog::find($this->queueLogId);
        if (!$log) {
            return;
        }

        $updates = ['status' => $status];

        if ($status === 'running') {
            $updates['started_at'] = now();
            $updates['attempts']   = $log->attempts + 1;
        } elseif (in_array($status, ['done', 'failed'])) {
            $updates['completed_at'] = now();
        }

        if ($errorMessage) {
            $updates['error_message'] = $errorMessage;
        }

        $log->update($updates);
    }
}
