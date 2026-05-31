<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة بيانات SEO للكلمة المفتاحية — تُحدَّث مع كل فحص
     * search_volume: حجم البحث الشهري
     * cpc: تكلفة النقرة بالدولار
     * competition: مستوى المنافسة (0-1)
     */
    public function up(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->unsignedInteger('search_volume')->nullable()->after('keyword');
            $table->decimal('cpc', 8, 2)->nullable()->after('search_volume');
            $table->decimal('competition', 5, 4)->nullable()->after('cpc');
        });
    }

    public function down(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropColumn(['search_volume', 'cpc', 'competition']);
        });
    }
};
