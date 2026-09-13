<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name')->nullable();
            $table->string('phone', 30);
            $table->string('whatsapp', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('address_note')->nullable();
            $table->string('map_url')->nullable();
            $table->text('map_embed_url')->nullable();
            $table->string('working_hours')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('phone')->constrained()->nullOnDelete();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->longText('body')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon', 40)->default('star');
            $table->string('color', 20)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->unsignedSmallInteger('min_months');
            $table->unsignedSmallInteger('max_months')->nullable();
            $table->string('age_label')->nullable();
            $table->string('color', 20)->default('#E8177F');
            $table->string('icon', 40)->default('cupcake');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->json('goals')->nullable();
            $table->json('daily_routine')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->string('teacher_ratio')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 40)->index();
            $table->string('icon', 40)->default('star');
            $table->string('color', 20)->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('classroom_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('frequency')->nullable();
            $table->text('details')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['classroom_id', 'activity_id']);
        });

        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->morphs('photoable');
            $table->string('path');
            $table->string('caption')->nullable();
            $table->string('alt')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('camps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('season', 20);
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedTinyInteger('age_from')->default(4);
            $table->unsignedTinyInteger('age_to')->default(12);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('schedule')->nullable();
            $table->string('meals')->nullable();
            $table->string('badge')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->json('activities')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 40)->index();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->date('event_date')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('parent_name');
            $table->string('relation')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('video_url')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 30)->default('school');
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('category', 40)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('label');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('position');
            $table->text('message')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->string('visitor_uuid', 36)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        foreach ([
            'job_applications', 'job_openings', 'seo_pages', 'faqs', 'partners', 'testimonials',
            'gallery_albums', 'camps', 'photos', 'classroom_activity', 'activities', 'classrooms',
            'highlights', 'sections', 'branches', 'settings',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
