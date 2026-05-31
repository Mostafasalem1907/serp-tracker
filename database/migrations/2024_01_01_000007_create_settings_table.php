<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول الإعدادات — key/value مع دعم التشفير للبيانات الحساسة
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            // is_encrypted: true للـ API keys وكلمات المرور
            $table->boolean('is_encrypted')->default(false);
            $table->string('group')->default('general'); // تجميع الإعدادات: general, api, ...
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
