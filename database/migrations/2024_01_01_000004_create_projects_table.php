<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول المشاريع (المواقع) — كل مشروع ينتمي لعميل
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('domain');
            $table->foreignId('country_id')->constrained('countries');
            $table->enum('language', ['ar', 'en'])->default('ar');
            $table->enum('device', ['desktop', 'mobile'])->default('desktop');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
