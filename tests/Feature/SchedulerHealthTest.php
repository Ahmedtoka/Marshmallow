<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Scheduler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerHealthTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_the_scheduler_stamps_a_heartbeat_every_minute(): void
    {
        $heartbeat = collect(app(Schedule::class)->events())->first(fn ($event) => $event->description === 'scheduler:heartbeat');

        $this->assertNotNull($heartbeat);
        $this->assertSame('* * * * *', $heartbeat->expression);

        $heartbeat->run($this->app);
        $this->assertTrue(Scheduler::isRunning());
    }

    public function test_the_dashboard_warns_when_the_cron_stops(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        Scheduler::beat();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Background tasks are not running');

        $this->travel(Scheduler::STALE_MINUTES + 1)->minutes();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Background tasks are not running');
        $this->actingAs($admin)->get(route('admin.content.settings.edit', 'tracking'))->assertOk()->assertSee('Not running');
    }

    public function test_sales_agents_do_not_get_the_warning(): void
    {
        $agent = User::where('role', 'sales')->firstOrFail();

        $this->actingAs($agent)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Background tasks are not running');
    }
}
