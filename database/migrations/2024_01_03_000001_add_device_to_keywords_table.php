<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة device على مستوى الكلمة
     * القيمة الافتراضية null = يرث device المشروع
     * لو حُدِّد explicitly = يستخدم هذا الجهاز بغض النظر عن المشروع
     */
    public function up(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            // null = يرث من المشروع، desktop/mobile = قيمة صريحة
            $table->string('device', 10)->nullable()->after('keyword')
                  ->comment('null=يرث من المشروع | desktop | mobile');
        });
    }

    public function down(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropColumn('device');
        });
    }
};
