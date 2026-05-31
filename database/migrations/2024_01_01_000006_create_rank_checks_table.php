<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول نتائج فحص الترتيب — كل صف نتيجة فحص واحد
     */
    public function up(): void
    {
        Schema::create('rank_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            // rank: رقم الترتيب — null لو الدومين مش في أول 100 نتيجة
            $table->integer('rank')->nullable();
            $table->text('url')->nullable();     // الرابط الذي ظهر في النتائج
            $table->text('title')->nullable();   // عنوان الصفحة في النتائج
            $table->enum('source', ['auto', 'manual'])->default('auto');
            $table->timestamp('checked_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_checks');
    }
};
