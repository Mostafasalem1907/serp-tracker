<?php

namespace App\Services;

use App\Models\AiAnalysis;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    private string $apiKey;
    private string $model = 'claude-sonnet-4-20250514';
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = Setting::getValue('anthropic_api_key', '');
    }

    /**
     * تشغيل تحليل الذكاء الاصطناعي لمشروع معين
     * يأخذ بيانات آخر 30 يوم ويعيد تحليلاً شاملاً
     */
    public function analyzeProject(Project $project): AiAnalysis
    {
        // جمع بيانات الترتيب لآخر 30 يوم
        $rankData = $this->collectRankData($project);

        // بناء الـ prompt
        $prompt = $this->buildPrompt($project, $rankData);

        // إرسال الطلب لـ Claude
        $response = $this->callClaude($prompt);

        // حفظ التحليل في قاعدة البيانات
        $analysis = AiAnalysis::create([
            'project_id'    => $project->id,
            'user_id'       => auth()->id(),
            'prompt_used'   => $prompt,
            'response'      => $response['content'],
            'tokens_input'  => $response['tokens_input'],
            'tokens_output' => $response['tokens_output'],
            'created_at'    => now(),
        ]);

        return $analysis;
    }

    /**
     * جمع بيانات الترتيب لآخر 30 يوم لكل كلمات المشروع
     */
    private function collectRankData(Project $project): array
    {
        $data = [];
        $since = now()->subDays(30);

        foreach ($project->keywords()->where('is_active', true)->get() as $keyword) {
            $checks = $keyword->rankChecks()
                ->where('checked_at', '>=', $since)
                ->orderBy('checked_at')
                ->get(['rank', 'checked_at'])
                ->map(fn($c) => [
                    'date' => $c->checked_at->format('Y-m-d'),
                    'rank' => $c->rank,
                ])
                ->toArray();

            $data[] = [
                'keyword'  => $keyword->keyword,
                'history'  => $checks,
                'current'  => $checks ? end($checks)['rank'] : null,
            ];
        }

        return $data;
    }

    /**
     * بناء الـ prompt المرسل لـ Claude مع بيانات الترتيب
     */
    private function buildPrompt(Project $project, array $rankData): string
    {
        $country     = $project->country->name_ar;
        $rankJson    = json_encode($rankData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
أنت خبير SEO متخصص في تحليل نتائج محركات البحث.

قم بتحليل بيانات ترتيب الكلمات المفتاحية التالية للموقع "{$project->domain}" في {$country} خلال آخر 30 يوم.

**بيانات الترتيب:**
{$rankJson}

**المطلوب:**
1. **ملخص عام**: كيف أداء الموقع خلال الفترة؟ هل يتحسن أم يتراجع؟
2. **كلمات تحتاج تدخل عاجل**: الكلمات التي تراجعت بشكل ملحوظ أو خرجت من أول 100 نتيجة.
3. **فرص للتحسين**: كلمات في المراتب 11-30 يمكن رفعها بجهد معقول.
4. **توصيات عملية**: خطوات محددة وقابلة للتطبيق لتحسين الترتيب.
5. **الكلمات الأفضل أداءً**: أبرز الإنجازات خلال الفترة.

اكتب تحليلاً احترافياً ومفصلاً باللغة العربية.
PROMPT;
    }

    /**
     * إرسال الطلب لـ Anthropic Claude API
     */
    private function callClaude(string $prompt): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])
        ->timeout(120)
        ->post($this->apiUrl, [
            'model'      => $this->model,
            'max_tokens' => 4096,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Anthropic API error', ['status' => $response->status()]);
            throw new \RuntimeException('فشل الاتصال بخدمة الذكاء الاصطناعي');
        }

        $data = $response->json();

        return [
            'content'       => $data['content'][0]['text'] ?? '',
            'tokens_input'  => $data['usage']['input_tokens'] ?? 0,
            'tokens_output' => $data['usage']['output_tokens'] ?? 0,
        ];
    }

    /**
     * اختبار صحة الـ Anthropic API key
     */
    public function testKey(string $apiKey): bool
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])
            ->timeout(15)
            ->post($this->apiUrl, [
                'model'      => $this->model,
                'max_tokens' => 10,
                'messages'   => [
                    ['role' => 'user', 'content' => 'hi'],
                ],
            ]);

            return $response->successful() || $response->status() === 400;
        } catch (\Exception) {
            return false;
        }
    }
}
