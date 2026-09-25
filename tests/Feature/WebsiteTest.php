<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_public_pages_load(): void
    {
        foreach (['/', '/visit', '/about', '/safety', '/branches', '/classes', '/classes/cupcake', '/activities', '/camps', '/gallery', '/careers', '/enroll', '/sitemap.xml', '/robots.txt'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_enrollment_form_creates_an_assigned_lead_in_the_right_class(): void
    {
        $zayed = Branch::where('slug', 'sheikh-zayed')->firstOrFail();
        $startYear = (int) substr(\App\Support\ClassFinder::academicYears()[0], 0, 4);

        $this->post('/enroll', [
            'parent_name' => 'Test Parent',
            'phone' => '+20 100 555 7788',
            'whatsapp_same' => '1',
            'child_name' => 'Layla',
            // Exactly 2 years on 1 October: sits on the Cupcake/Popcorn boundary and must move up.
            'child_dob' => ($startYear - 2).'-10-01',
            'academic_year' => \App\Support\ClassFinder::academicYears()[0],
            'branch_id' => $zayed->id,
            'interest' => 'enrollment',
        ])->assertRedirect(route('enroll.thanks'));

        // The thank-you page is what tells Meta and Google a booking happened.
        $this->get(route('enroll.thanks'))
            ->assertOk()
            ->assertSee("mmPixel('Lead'", false);

        $lead = Lead::firstOrFail();
        $this->assertSame('01005557788', $lead->phone);
        $this->assertSame('popcorn', $lead->classroom?->slug);
        $this->assertSame('sales.zayed@marshmallownursery.com', $lead->assignee?->email);
    }

    public function test_the_tracker_beacon_does_not_eat_the_thank_you_page(): void
    {
        // Submitting the form hides the page, so the tracker's pagehide beacon lands between the booking
        // and the thank-you page. It must not touch the session, or the parent is sent back to /enroll.
        $this->post('/enroll', [
            'parent_name' => 'Test Parent',
            'phone' => '01005557788',
            'whatsapp_same' => '1',
            'branch_id' => Branch::where('slug', 'sheikh-zayed')->value('id'),
            'interest' => 'enrollment',
        ])->assertRedirect(route('enroll.thanks'));

        // The beacon is still recorded, just without a session.
        $this->postJson('/t/collect', ['type' => 'pageview', 'path' => '/enroll', 'vid' => (string) Str::uuid(), 'sid' => (string) Str::uuid()])
            ->assertOk()
            ->assertJson(['ok' => true]);
        $this->assertSame(1, PageView::count());

        $this->get(route('enroll.thanks'))->assertOk()->assertSee("mmPixel('Lead'", false);
    }

    public function test_dashboard_users_do_not_get_the_tracker(): void
    {
        $this->get('/')->assertSee('name="mm-track"', false);

        $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->get('/')->assertDontSee('name="mm-track"', false);
    }

    public function test_dashboard_requires_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/login')->assertOk();
    }
}
