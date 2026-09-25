<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Site;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Visitor tracking
|--------------------------------------------------------------------------
*/
// Stateless on purpose: the tracker's pagehide beacon fires while a form is being submitted. If it shared the
// session it would consume or overwrite the flash data, and a parent who just booked would land back on /enroll.
Route::post('/t/collect', [TrackingController::class, 'collect'])->name('track.collect')
    ->middleware('throttle:240,1')
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, // already exempt, but it would still read the session
    ]);

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::get('/', [Site\HomeController::class, 'index'])->name('home');
Route::get('/about', [Site\PageController::class, 'about'])->name('about');
Route::get('/safety', [Site\PageController::class, 'safety'])->name('safety');
Route::get('/branches', [Site\PageController::class, 'branches'])->name('branches');
Route::get('/reviews', [Site\PageController::class, 'reviews'])->name('reviews');
// One page for "where are you and is my child safe with you", linked from the main menu.
Route::get('/visit', [Site\PageController::class, 'visit'])->name('visit');

Route::get('/classes', [Site\ClassController::class, 'index'])->name('classes.index');
Route::post('/classes/find', [Site\ClassController::class, 'find'])->name('classes.find')->middleware('throttle:60,1');
Route::get('/classes/{classroom:slug}', [Site\ClassController::class, 'show'])->name('classes.show');

Route::get('/activities', [Site\ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{activity:slug}', [Site\ActivityController::class, 'show'])->name('activities.show');

Route::get('/camps', [Site\CampController::class, 'index'])->name('camps.index');
Route::get('/camps/{camp:slug}', [Site\CampController::class, 'show'])->name('camps.show');

Route::get('/gallery', [Site\GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{album:slug}', [Site\GalleryController::class, 'show'])->name('gallery.show');

Route::get('/careers', [Site\CareerController::class, 'index'])->name('careers');
Route::post('/careers', [Site\CareerController::class, 'apply'])->name('careers.apply')->middleware('throttle:15,1');

Route::get('/enroll', [Site\EnrollController::class, 'create'])->name('enroll');
Route::post('/enroll', [Site\EnrollController::class, 'store'])->name('enroll.store')->middleware('throttle:30,1');
Route::get('/thank-you', [Site\EnrollController::class, 'thanks'])->name('enroll.thanks');

Route::get('/sitemap.xml', [Site\SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [Site\SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:10,1');
    });

    Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        Route::get('notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{id}', [Admin\NotificationController::class, 'open'])->name('notifications.open');
        Route::post('notifications/read-all', [Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');

        /* ---------- CRM (all roles; sales agents are scoped to their own leads) ---------- */
        Route::prefix('crm')->name('crm.')->group(function () {
            Route::get('leads', [Admin\Crm\LeadController::class, 'index'])->name('leads.index');
            Route::get('leads/board', [Admin\Crm\LeadController::class, 'board'])->name('leads.board');
            Route::get('leads/export', [Admin\Crm\LeadController::class, 'export'])->name('leads.export')->middleware('role:admin,sales_manager');
            Route::get('leads/create', [Admin\Crm\LeadController::class, 'create'])->name('leads.create');
            Route::post('leads', [Admin\Crm\LeadController::class, 'store'])->name('leads.store');
            Route::get('leads/{lead}', [Admin\Crm\LeadController::class, 'show'])->name('leads.show');
            Route::get('leads/{lead}/edit', [Admin\Crm\LeadController::class, 'edit'])->name('leads.edit');
            Route::put('leads/{lead}', [Admin\Crm\LeadController::class, 'update'])->name('leads.update');
            Route::delete('leads/{lead}', [Admin\Crm\LeadController::class, 'destroy'])->name('leads.destroy')->middleware('role:admin,sales_manager');
            Route::patch('leads/{lead}/status', [Admin\Crm\LeadController::class, 'updateStatus'])->name('leads.status');
            Route::patch('leads/{lead}/assign', [Admin\Crm\LeadController::class, 'assign'])->name('leads.assign')->middleware('role:admin,sales_manager');
            Route::post('leads/{lead}/activities', [Admin\Crm\LeadActivityController::class, 'store'])->name('leads.activities.store');
            Route::post('leads/{lead}/follow-ups', [Admin\Crm\FollowUpController::class, 'store'])->name('leads.follow-ups.store');

            Route::get('follow-ups', [Admin\Crm\FollowUpController::class, 'index'])->name('follow-ups.index');
            Route::patch('follow-ups/{followUp}/complete', [Admin\Crm\FollowUpController::class, 'complete'])->name('follow-ups.complete');
            Route::delete('follow-ups/{followUp}', [Admin\Crm\FollowUpController::class, 'destroy'])->name('follow-ups.destroy');

            Route::get('reports', [Admin\Crm\ReportController::class, 'index'])->name('reports')->middleware('role:admin,sales_manager');
        });

        /* ---------- Analytics (admin + sales manager) ---------- */
        Route::prefix('analytics')->name('analytics.')->middleware('role:admin,sales_manager')->group(function () {
            Route::get('/', [Admin\Analytics\OverviewController::class, 'index'])->name('overview');
            Route::get('visitors', [Admin\Analytics\VisitorController::class, 'index'])->name('visitors.index');
            Route::get('visitors/{visitor}', [Admin\Analytics\VisitorController::class, 'show'])->name('visitors.show');
            Route::get('pages', [Admin\Analytics\BehaviourController::class, 'pages'])->name('pages');
            Route::get('sources', [Admin\Analytics\BehaviourController::class, 'sources'])->name('sources');
            Route::get('actions', [Admin\Analytics\BehaviourController::class, 'actions'])->name('actions');
            Route::get('funnel', [Admin\Analytics\BehaviourController::class, 'funnel'])->name('funnel');
        });

        /* ---------- Website content (admin only) ---------- */
        Route::prefix('content')->name('content.')->middleware('role:admin')->group(function () {
            Route::get('settings/{group?}', [Admin\Content\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings/{group}', [Admin\Content\SettingController::class, 'update'])->name('settings.update');

            Route::get('sections', [Admin\Content\SectionController::class, 'index'])->name('sections.index');
            Route::post('sections/reorder', [Admin\Content\SectionController::class, 'reorder'])->name('sections.reorder');
            Route::get('sections/{section}/edit', [Admin\Content\SectionController::class, 'edit'])->name('sections.edit');
            Route::put('sections/{section}', [Admin\Content\SectionController::class, 'update'])->name('sections.update');
            Route::patch('sections/{section}/toggle', [Admin\Content\SectionController::class, 'toggle'])->name('sections.toggle');

            Route::resource('branches', Admin\Content\BranchController::class)->except('show');
            Route::resource('classrooms', Admin\Content\ClassroomController::class)->except('show');
            Route::post('classrooms/{classroom}/activities', [Admin\Content\ClassroomActivityController::class, 'store'])->name('classrooms.activities.store');
            Route::get('classroom-activities/{classroomActivity}/edit', [Admin\Content\ClassroomActivityController::class, 'edit'])->name('classroom-activities.edit');
            Route::put('classroom-activities/{classroomActivity}', [Admin\Content\ClassroomActivityController::class, 'update'])->name('classroom-activities.update');
            Route::delete('classroom-activities/{classroomActivity}', [Admin\Content\ClassroomActivityController::class, 'destroy'])->name('classroom-activities.destroy');
            Route::post('classrooms/{classroom}/activities/reorder', [Admin\Content\ClassroomActivityController::class, 'reorder'])->name('classrooms.activities.reorder');
            Route::resource('activities', Admin\Content\ActivityController::class)->except('show');
            Route::resource('camps', Admin\Content\CampController::class)->except('show');
            Route::resource('albums', Admin\Content\AlbumController::class)->except('show');
            Route::resource('testimonials', Admin\Content\TestimonialController::class)->except('show');
            Route::resource('partners', Admin\Content\PartnerController::class)->except('show');
            Route::resource('faqs', Admin\Content\FaqController::class)->except('show');
            Route::resource('highlights', Admin\Content\HighlightController::class)->except('show');
            Route::resource('jobs', Admin\Content\JobOpeningController::class)->except('show');
            Route::get('seo', [Admin\Content\SeoController::class, 'index'])->name('seo.index');
            Route::get('seo/{seoPage}/edit', [Admin\Content\SeoController::class, 'edit'])->name('seo.edit');
            Route::put('seo/{seoPage}', [Admin\Content\SeoController::class, 'update'])->name('seo.update');

            Route::get('applications', [Admin\Content\JobApplicationController::class, 'index'])->name('applications.index');
            Route::get('applications/{application}', [Admin\Content\JobApplicationController::class, 'show'])->name('applications.show');
            Route::patch('applications/{application}', [Admin\Content\JobApplicationController::class, 'update'])->name('applications.update');
            Route::delete('applications/{application}', [Admin\Content\JobApplicationController::class, 'destroy'])->name('applications.destroy');

            Route::post('photos', [Admin\Content\PhotoController::class, 'store'])->name('photos.store');
            Route::post('photos/reorder', [Admin\Content\PhotoController::class, 'reorder'])->name('photos.reorder');
            Route::patch('photos/{photo}', [Admin\Content\PhotoController::class, 'update'])->name('photos.update');
            Route::delete('photos/{photo}', [Admin\Content\PhotoController::class, 'destroy'])->name('photos.destroy');

            Route::resource('users', Admin\Content\UserController::class)->except('show');
        });
    });
});
