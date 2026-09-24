<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $lead = Lead::firstOrFail();
        $this->assertSame('01005557788', $lead->phone);
        $this->assertSame('popcorn', $lead->classroom?->slug);
        $this->assertSame('sales.zayed@marshmallownursery.com', $lead->assignee?->email);
    }

    public function test_dashboard_requires_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/login')->assertOk();
    }
}
