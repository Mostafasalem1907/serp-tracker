<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول سجل الأنشطة — كل تغيير يُسجَّل هنا
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            // user_id: null في حالة الأنشطة التلقائية (Cron/System)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);       // نوع النشاط مثل project.created
            $table->string('model_type', 100)->nullable(); // الكيان: Client, Project, ...
            $table->unsignedBigInteger('model_id')->nullable(); // ID الكيان
            $table->json('old_values')->nullable(); // القيم قبل التعديل
            $table->json('new_values')->nullable(); // القيم بعد التعديل
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // فهرس للبحث السريع عن أنشطة كيان معين
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
