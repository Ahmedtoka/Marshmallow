<?php

namespace Tests\Feature;

use App\Jobs\SendMetaConversion;
use App\Models\Branch;
use App\Models\JobApplication;
use App\Models\Lead;
use App\Models\MetaConversion;
use App\Models\Setting;
use App\Services\MetaConversions;
use App\Support\ClassFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MetaConversionsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private const PIXEL = '2078524479437850';

    private const TOKEN = 'test-capi-token-that-must-never-leak';

    protected function setUp(): void
    {
        parent::setUp();

        Setting::put(['meta_pixel_id' => self::PIXEL]);
        config(['services.meta.capi_token' => self::TOKEN, 'services.meta.test_event_code' => null]);
    }

    public function test_the_browser_and_the_server_send_the_same_event_id_for_a_booking(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1, 'fbtrace_id' => 'AbC123'])]);

        $this->withUnencryptedCookie('_fbp', 'fb.1.1758700000000.1234567890')
            ->withUnencryptedCookie('mm_fbclid', '1758700000000.IwAR0abcdefghijk')
            ->post('/enroll', $this->booking(['interest' => 'tour', 'tour_date' => $this->nextSunday(), 'tour_time' => '10:00']))
            ->assertRedirect(route('enroll.thanks'));

        $lead = Lead::firstOrFail();
        $this->assertTrue(Str::isUuid($lead->meta_event_id));

        // Server: Lead and Schedule, both with the lead's event id.
        $events = $this->sentEvents();
        $this->assertSame(['Lead', 'Schedule'], array_column($events, 'event_name'));
        $this->assertSame([$lead->meta_event_id, $lead->meta_event_id], array_column($events, 'event_id'));

        // Browser: the thank-you page fires the same events with the same id.
        $this->get(route('enroll.thanks'))
            ->assertOk()
            ->assertSee("var eventId = '{$lead->meta_event_id}'", false)
            ->assertSee("window.mmPixel('Lead', details, true, eventId)", false)
            ->assertSee("window.mmPixel('Schedule', details, true, eventId)", false);

        $this->assertSame(2, MetaConversion::where('lead_id', $lead->id)->where('status', 'sent')->count());
        $this->assertSame('AbC123', MetaConversion::first()->fbtrace_id);
    }

    public function test_customer_data_is_normalized_and_hashed(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $this->withUnencryptedCookie('_fbp', 'fb.1.1758700000000.1234567890')
            ->withUnencryptedCookie('mm_fbclid', '1758700000000.IwAR0abcdefghijk')
            ->withHeader('User-Agent', 'TestBrowser/1.0')
            ->post('/enroll', $this->booking());

        $user = $this->sentEvents()[0]['user_data'];

        $this->assertSame([hash('sha256', '201005557788')], $user['ph']);
        $this->assertSame([hash('sha256', 'mona@example.com')], $user['em']);
        $this->assertSame(hash('sha256', 'mona'), $user['fn']);
        $this->assertSame(hash('sha256', 'ali'), $user['ln']);
        $this->assertSame(hash('sha256', 'giza'), $user['ct']);
        $this->assertSame(hash('sha256', 'eg'), $user['country']);
        $this->assertSame('TestBrowser/1.0', $user['client_user_agent']);
        $this->assertSame('fb.1.1758700000000.1234567890', $user['fbp']);
        $this->assertSame('fb.1.1758700000000.IwAR0abcdefghijk', $user['fbc']);

        Http::assertSent(function (HttpRequest $request) {
            $body = $request->body();

            return $request->url() === 'https://graph.facebook.com/'.config('services.meta.graph_version').'/'.self::PIXEL.'/events'
                && ! str_contains($request->url(), self::TOKEN)   // token in the body, never the URL
                && $request['access_token'] === self::TOKEN
                && ! str_contains($body, '01005557788')
                && ! str_contains($body, 'Mona@Example.com')
                && ! str_contains($body, 'Mona');
        });

        // Only an enrollment: no Schedule.
        $this->assertSame(['Lead'], MetaConversion::pluck('event_name')->all());
    }

    public function test_nothing_is_sent_without_a_token(): void
    {
        config(['services.meta.capi_token' => null]);
        Http::fake();
        Queue::fake();

        $this->post('/enroll', $this->booking(['interest' => 'tour', 'tour_date' => $this->nextSunday(), 'tour_time' => '10:00']))
            ->assertRedirect(route('enroll.thanks'));

        $this->assertSame(1, Lead::count(), 'The booking itself must still be saved.');
        Queue::assertNothingPushed();
        Http::assertNothingSent();
        $this->assertSame(0, MetaConversion::count());
        $this->get(route('enroll.thanks'))->assertOk()->assertSee("window.mmPixel('Lead'", false);
    }

    public function test_a_careers_application_sends_submit_application_with_the_browser_event_id(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $this->followingRedirects()
            ->post('/careers', ['name' => 'Sara Hassan', 'phone' => '0111 222 3333', 'position' => 'other', 'position_other' => 'Teacher'])
            ->assertOk()
            ->assertSee('mmPixel(\'SubmitApplication\'', false)
            ->assertSee(JobApplication::firstOrFail()->meta_event_id);

        $event = $this->sentEvents()[0];
        $this->assertSame('SubmitApplication', $event['event_name']);
        $this->assertSame(JobApplication::firstOrFail()->meta_event_id, $event['event_id']);
        $this->assertSame([hash('sha256', '201112223333')], $event['user_data']['ph']);
    }

    public function test_a_meta_outage_is_recorded_and_retried(): void
    {
        [$job, $conversion] = $this->queuedJob();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Service temporarily unavailable', 'is_transient' => true, 'fbtrace_id' => 'T1']], 503)]);

        $job->withFakeQueueInteractions()->handle(app(MetaConversions::class));

        $job->assertReleased(SendMetaConversion::BACKOFF[0]);
        $conversion->refresh();
        $this->assertSame('retrying', $conversion->status);
        $this->assertSame(503, $conversion->response_code);
        $this->assertStringContainsString('temporarily unavailable', $conversion->error);
    }

    public function test_a_rejected_event_fails_without_retrying_and_never_stores_the_token(): void
    {
        [$job, $conversion] = $this->queuedJob();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid OAuth access token '.self::TOKEN, 'code' => 190]], 400)]);

        $job->withFakeQueueInteractions()->handle(app(MetaConversions::class));

        $job->assertFailed();
        $job->assertNotReleased();
        $conversion->refresh();
        $this->assertSame('failed', $conversion->status);
        $this->assertStringContainsString('Invalid OAuth access token', $conversion->error);
        $this->assertStringNotContainsString(self::TOKEN, $conversion->error);
    }

    public function test_the_test_event_code_is_sent_only_when_set(): void
    {
        config(['services.meta.test_event_code' => 'TEST12345']);
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $this->post('/enroll', $this->booking());

        Http::assertSent(fn (HttpRequest $request) => $request['test_event_code'] === 'TEST12345');
        $this->assertTrue(MetaConversion::firstOrFail()->test_event);
    }

    public function test_phone_and_name_normalizing(): void
    {
        $this->assertSame('201012345678', MetaConversions::normalizePhone('010 1234 5678'));
        $this->assertSame('201012345678', MetaConversions::normalizePhone('+20 10 1234 5678'));
        $this->assertSame('201012345678', MetaConversions::normalizePhone('٠١٠١٢٣٤٥٦٧٨'));
        $this->assertNull(MetaConversions::normalizePhone(''));
        $this->assertSame(['mona', 'ali'], MetaConversions::splitName('  Mona  El-Sayed Ali '));
        $this->assertSame(['mona', null], MetaConversions::splitName('Mona'));
        $this->assertSame(['منى', 'علي'], MetaConversions::splitName('منى علي'));
    }

    public function test_the_tracking_settings_page_shows_the_last_result(): void
    {
        MetaConversion::create(['event_name' => 'Lead', 'event_id' => 'abc', 'status' => 'failed', 'error' => 'Invalid OAuth access token']);
        $admin = \App\Models\User::where('email', 'admin@marshmallownursery.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.content.settings.edit', 'tracking'))
            ->assertOk()
            ->assertSee('Meta Conversions API')
            ->assertSee('Invalid OAuth access token')
            ->assertDontSee(self::TOKEN);
    }

    /** @return array{0: SendMetaConversion, 1: MetaConversion} */
    private function queuedJob(): array
    {
        Queue::fake();
        $this->post('/enroll', $this->booking());

        $job = Queue::pushed(SendMetaConversion::class)->first();
        $this->assertNotNull($job);

        return [$job, MetaConversion::findOrFail($job->conversionId)];
    }

    /** The events posted to Meta, in order. */
    private function sentEvents(): array
    {
        return Http::recorded()
            ->map(fn ($pair) => $pair[0]['data'][0])
            ->values()
            ->all();
    }

    private function booking(array $overrides = []): array
    {
        return $overrides + [
            'parent_name' => 'Mona El-Sayed Ali',
            'phone' => '0100 555 7788',
            'whatsapp_same' => '1',
            'email' => 'Mona@Example.com',
            'child_name' => 'Layla',
            'child_dob' => now()->subYears(3)->toDateString(),
            'academic_year' => ClassFinder::academicYears()[0],
            'branch_id' => Branch::where('slug', 'sheikh-zayed')->value('id'),
            'interest' => 'enrollment',
        ];
    }

    private function nextSunday(): string
    {
        return now()->next('Sunday')->toDateString();
    }
}
