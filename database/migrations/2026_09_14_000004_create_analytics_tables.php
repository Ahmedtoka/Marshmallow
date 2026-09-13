<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at')->index();
            $table->unsignedInteger('visits_count')->default(0);
            $table->unsignedInteger('pageviews_count')->default(0);
            $table->string('first_source', 40)->nullable();
            $table->string('first_referrer_host')->nullable();
            $table->string('first_utm_source')->nullable();
            $table->string('first_utm_campaign')->nullable();
            $table->string('first_landing_path')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('browser', 40)->nullable();
            $table->string('os', 40)->nullable();
            $table->string('country', 5)->nullable();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->string('uuid', 36)->unique();
            $table->dateTime('started_at')->index();
            $table->dateTime('last_activity_at');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('pageviews')->default(0);
            $table->unsignedInteger('events_count')->default(0);
            $table->string('landing_path')->nullable();
            $table->string('exit_path')->nullable();
            $table->text('referrer')->nullable();
            $table->string('referrer_host')->nullable();
            $table->string('source', 40)->default('direct')->index();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable()->index();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('click_id', 20)->nullable();
            $table->string('device_type', 20)->nullable()->index();
            $table->string('browser', 40)->nullable();
            $table->string('os', 40)->nullable();
            $table->string('screen', 20)->nullable();
            $table->string('language', 20)->nullable();
            $table->string('country', 5)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->boolean('is_bounce')->default(true);
            $table->boolean('converted')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->string('path')->index();
            $table->string('title')->nullable();
            $table->string('query')->nullable();
            $table->dateTime('entered_at')->index();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedTinyInteger('max_scroll')->default(0);
            $table->timestamps();
        });

        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('page_view_id')->nullable();
            $table->string('name', 60)->index();
            $table->string('label')->nullable();
            $table->string('value')->nullable();
            $table->json('properties')->nullable();
            $table->string('path')->nullable();
            $table->dateTime('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('visitors');
    }
};
