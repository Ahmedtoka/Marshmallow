<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Camp;
use App\Models\Classroom;
use App\Models\ClassroomActivity;
use App\Models\GalleryAlbum;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Behind the host's proxy the app can read the wrong scheme and a stray :443, which then
        // shows up in canonical tags, the sitemap and share links. In production every URL is built
        // from APP_URL instead, so the site has exactly one address.
        if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }

        // Short, stable names stored in photos.photoable_type (also used by the dashboard upload form).
        Relation::enforceMorphMap([
            'classroom' => Classroom::class,
            'activity' => Activity::class,
            'classroom_activity' => ClassroomActivity::class,
            'camp' => Camp::class,
            'album' => GalleryAlbum::class,
            'user' => \App\Models\User::class,
            'lead' => \App\Models\Lead::class,
        ]);

        Paginator::defaultView('admin.partials.pagination');

        View::composer('layouts.admin', function ($view) {
            $user = auth()->user();
            $view->with('unreadNotifications', $user ? $user->unreadNotifications()->count() : 0);
        });
    }
}
