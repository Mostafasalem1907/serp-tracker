<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول تحليلات الذكاء الاصطناعي — كل تشغيل يُحفظ مع عدد الـ tokens
     */
    public function up(): void
    {
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('prompt_used');         // الـ prompt الذي أُرسل لـ Claude
            $table->longText('response');        // رد Claude كاملاً
            $table->integer('tokens_input')->default(0);
            $table->integer('tokens_output')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
