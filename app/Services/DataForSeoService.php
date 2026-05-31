<?php

namespace App\Services;

use App\Models\Keyword;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DataForSeoService
{
    private string $baseUrl = 'https://api.dataforseo.com/v3';
    private string $login;
    private string $password;

    public function __construct()
    {
        // جلب الـ credentials المشفرة من جدول settings
        $this->login    = Setting::getValue('dataforseo_login', '');
        $this->password = Setting::getValue('dataforseo_password', '');
    }

    /**
     * فحص ترتيب كلمة مفتاحية واحدة في نتائج جوجل
     * يعيد مصفوفة تحتوي على: rank, url, title, search_volume, cpc, competition
     * rank = null لو الدومين مش في أول 100 نتيجة
     */
    public function checkKeyword(Keyword $keyword): array
    {
        $project = $keyword->project;
        $country = $project->country;
        $domain  = $this->cleanDomain($project->domain);

        // الجهاز: يستخدم device الكلمة إن وُجد، وإلا يرث من المشروع
        $device = $keyword->effective_device;

        // بناء طلب البحث لـ DataForSEO
        $payload = [[
            'keyword'       => $keyword->keyword,
            'location_code' => (int) $country->location_code,
            'language_code' => $project->language === 'ar' ? 'ar' : 'en',
            'device'        => $device,
            'os'            => $device === 'mobile' ? 'android' : 'windows',
            'depth'         => 100,
            'se_domain'     => $country->se_domain,
        ]];

        try {
            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(30)
                ->post("{$this->baseUrl}/serp/google/organic/live/advanced", $payload);

            if (!$response->successful()) {
                Log::error('DataForSEO API error', [
                    'status'  => $response->status(),
                    'keyword' => $keyword->keyword,
                ]);
                return $this->emptyResult();
            }

            $data   = $response->json();
            $result = data_get($data, 'tasks.0.result.0', []);
            $items  = $result['items'] ?? [];

            // استخراج بيانات الكلمة (volume, cpc) من keyword_info
            $keywordInfo = $result['keyword_info'] ?? [];

            // استخراج الترتيب من قائمة النتائج
            $rankData = $this->extractRankFromItems($items, $domain);

            return array_merge($rankData, [
                'search_volume' => $keywordInfo['search_volume'] ?? null,
                'cpc'           => $keywordInfo['cpc'] ?? null,
                'competition'   => $keywordInfo['competition'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('DataForSEO request failed', [
                'keyword' => $keyword->keyword,
                'error'   => $e->getMessage(),
            ]);
            return $this->emptyResult();
        }
    }

    /**
     * استخراج الترتيب من قائمة النتائج
     * يدعم: exact match + subdomain match (m.domain.com) + www match
     */
    private function extractRankFromItems(array $items, string $domain): array
    {
        foreach ($items as $item) {
            // نقبل organic فقط — نتجاهل الإعلانات والـ featured snippets
            if (($item['type'] ?? '') !== 'organic') {
                continue;
            }

            $itemDomain = $this->cleanDomain($item['domain'] ?? '');

            // مطابقة مرنة: exact أو subdomain (m.domain.com, amp.domain.com, ...)
            if ($this->domainsMatch($itemDomain, $domain)) {
                return [
                    'rank'  => $item['rank_absolute'] ?? null,
                    'url'   => $item['url'] ?? null,
                    'title' => $item['title'] ?? null,
                ];
            }
        }

        return ['rank' => null, 'url' => null, 'title' => null];
    }

    /**
     * مطابقة الدومينات — يحل مشكلة Mobile subdomains
     * مثال: m.scrap-jeddah.com يتطابق مع scrap-jeddah.com
     */
    private function domainsMatch(string $itemDomain, string $projectDomain): bool
    {
        // مطابقة تامة
        if ($itemDomain === $projectDomain) {
            return true;
        }

        // مطابقة subdomain: m.domain.com أو amp.domain.com أو blog.domain.com
        if (str_ends_with($itemDomain, '.' . $projectDomain)) {
            return true;
        }

        // العكس: لو المستخدم أدخل m.domain.com وهو في الواقع domain.com
        if (str_ends_with($projectDomain, '.' . $itemDomain)) {
            return true;
        }

        return false;
    }

    /**
     * جلب بيانات الكلمة المفتاحية (Volume, CPC, Competition) من endpoint منفصل
     * يُستدعى مرة لكل كلمة — يُحدَّث عند أول فحص أو لما تكون القيم فارغة
     */
    public function fetchKeywordMetrics(Keyword $keyword): array
    {
        $country = $keyword->project->country;

        $payload = [[
            'keywords'      => [$keyword->keyword],
            'location_code' => (int) $country->location_code,
            'language_code' => $keyword->project->language === 'ar' ? 'ar' : 'en',
        ]];

        try {
            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(30)
                ->post("{$this->baseUrl}/keywords_data/google_ads/search_volume/live", $payload);

            if (!$response->successful()) {
                return ['search_volume' => null, 'cpc' => null, 'competition' => null];
            }

            $item = data_get($response->json(), 'tasks.0.result.0', []);

            return [
                'search_volume' => $item['search_volume'] ?? null,
                'cpc'           => $item['cpc'] ?? null,
                'competition'   => $item['competition'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::warning('fetchKeywordMetrics failed', ['keyword' => $keyword->keyword, 'error' => $e->getMessage()]);
            return ['search_volume' => null, 'cpc' => null, 'competition' => null];
        }
    }

    /**
     * نتيجة فارغة عند الفشل
     */
    private function emptyResult(): array
    {
        return [
            'rank'          => null,
            'url'           => null,
            'title'         => null,
            'search_volume' => null,
            'cpc'           => null,
            'competition'   => null,
        ];
    }

    /**
     * تنظيف الدومين — إزالة www وhttp وhttps والـ path
     */
    private function cleanDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        $domain = explode('/', $domain)[0];
        return $domain;
    }

    /**
     * اختبار صحة الـ credentials
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(15)
                ->get("{$this->baseUrl}/appendix/user_data");
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * اختبار credentials مخصصة (من لوحة الإعدادات قبل الحفظ)
     */
    public function testCredentials(string $login, string $password): bool
    {
        try {
            $response = Http::withBasicAuth($login, $password)
                ->timeout(15)
                ->get("{$this->baseUrl}/appendix/user_data");
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }
}
