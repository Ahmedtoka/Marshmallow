<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique()->nullable();
            $table->string('parent_name');
            $table->string('phone', 30)->index();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('child_name')->nullable();
            $table->date('child_dob')->nullable();
            $table->unsignedSmallInteger('child_age_months')->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('camp_id')->nullable()->constrained()->nullOnDelete();
            $table->string('interest', 30)->default('enrollment')->index();
            $table->dateTime('preferred_tour_at')->nullable();
            $table->text('message')->nullable();
            $table->string('heard_from')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->string('priority', 20)->default('warm');
            $table->string('lost_reason')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('next_follow_up_at')->nullable()->index();
            $table->dateTime('last_contacted_at')->nullable();
            $table->dateTime('enrolled_at')->nullable();
            $table->string('channel', 30)->default('website');
            $table->string('visitor_uuid', 36)->nullable()->index();
            $table->unsignedBigInteger('visit_id')->nullable();
            $table->string('source', 40)->nullable()->index();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->text('referrer')->nullable();
            $table->string('landing_path')->nullable();
            $table->string('form_path')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30)->index();
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20)->default('call');
            $table->dateTime('due_at')->index();
            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable()->index();
            $table->text('outcome')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
