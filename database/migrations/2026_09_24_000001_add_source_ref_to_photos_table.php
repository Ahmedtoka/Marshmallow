<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            // Where this photo came from, e.g. "fb:2721813104565699" — keeps imports idempotent.
            $table->string('source_ref', 100)->nullable()->unique()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('source_ref');
        });
    }
};
