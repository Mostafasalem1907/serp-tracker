<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تغيير نوع عمود competition من decimal لـ string
     * DataForSEO يرجع القيمة كـ "LOW" / "MEDIUM" / "HIGH" وليس رقماً
     */
    public function up(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->string('competition', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->decimal('competition', 5, 4)->nullable()->change();
        });
    }
};
