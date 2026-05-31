<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول سجل طابور الفحص — لمتابعة حالة كل عملية فحص
     */
    public function up(): void
    {
        Schema::create('check_queue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('keyword_id')->nullable()->constrained('keywords')->nullOnDelete();
            $table->enum('status', ['pending', 'running', 'done', 'failed'])->default('pending');
            $table->text('error_message')->nullable(); // رسالة الخطأ لو فشل الفحص
            $table->integer('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_queue_logs');
    }
};
