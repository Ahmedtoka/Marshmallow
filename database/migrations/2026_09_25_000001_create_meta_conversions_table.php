<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The same id goes to the browser pixel and to the Conversions API so Meta counts the action once.
        Schema::table('leads', function (Blueprint $table) {
            $table->string('meta_event_id', 64)->nullable()->after('user_agent');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('meta_event_id', 64)->nullable()->after('visitor_uuid');
        });

        // One row per server-side event: what was sent and what Meta answered. No personal data is kept here.
        Schema::create('meta_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 40);
            $table->string('event_id', 64)->index();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('queued')->index(); // queued, retrying, sent, failed
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->json('fields')->nullable();         // names of the user_data fields sent, never their values
            $table->boolean('test_event')->default(false);
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->unsignedSmallInteger('events_received')->nullable();
            $table->string('fbtrace_id', 60)->nullable();
            $table->text('error')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_conversions');

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('meta_event_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('meta_event_id');
        });
    }
};
