<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('source', 20)->default('manual')->after('quote');
            $table->string('source_url')->nullable()->after('source');
            $table->date('reviewed_at')->nullable()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['source', 'source_url', 'reviewed_at']);
        });
    }
};
