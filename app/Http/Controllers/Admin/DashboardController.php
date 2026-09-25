<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\Visit;
use App\Support\Scheduler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isManager = ! $user->isSales();
        $visible = fn () => Lead::query()->visibleTo($user);

        $myDay = FollowUp::pending()
            ->where('due_at', '<=', now()->endOfDay())
            ->when($isManager, fn ($q) => $q, fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('lead', fn (Builder $q) => $q->visibleTo($user))
            ->with(['lead.classroom:id,name,color', 'user:id,name'])
            ->orderBy('due_at')
            ->limit(20)
            ->get();
        $overdueCount = $myDay->filter->isOverdue()->count();

        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        // Compare with the same number of days last month so the trend is fair mid-month.
        $lastMonthSameDay = $lastMonthStart->copy()->addDays(now()->day - 1)->endOfDay()->min($lastMonthStart->copy()->endOfMonth());

        $kpiRow = $visible()->toBase()->selectRaw("
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS this_month,
            SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END) AS last_month,
            SUM(CASE WHEN status NOT IN ('enrolled','lost') THEN 1 ELSE 0 END) AS open_leads,
            SUM(CASE WHEN status = 'enrolled' AND enrolled_at >= ? THEN 1 ELSE 0 END) AS enrolled_month", [$monthStart, $lastMonthStart, $lastMonthSameDay, $monthStart])->first();

        $toursThisWeek = LeadActivity::query()
            ->where('type', 'status_changed')
            ->where('created_at', '>=', now()->startOfWeek())
            ->whereHas('lead', fn (Builder $q) => $q->visibleTo($user))
            ->get(['lead_id', 'meta'])
            ->filter(fn ($a) => ($a->meta['to'] ?? null) === 'tour_booked')
            ->unique('lead_id')->count();

        $thisMonth = (int) $kpiRow->this_month;
        $lastMonth = (int) $kpiRow->last_month;

        $kpis = [
            'new_month' => $thisMonth,
            'new_change' => $lastMonth ? (int) round(($thisMonth - $lastMonth) / $lastMonth * 100) : null,
            'open' => (int) $kpiRow->open_leads,
            'tours_week' => $toursThisWeek,
            'enrolled_month' => (int) $kpiRow->enrolled_month,
        ];

        $pipeline = $visible()->toBase()->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');

        $unassigned = $isManager
            ? Lead::whereNull('assigned_to')->open()->with(['branch:id,name,short_name', 'classroom:id,name,color'])->latest('id')->limit(6)->get()
            : collect();

        $recent = $visible()->with(['classroom:id,name,color', 'assignee:id,name', 'branch:id,name,short_name'])->latest('id')->limit(8)->get();

        $website = null;
        if ($isManager) {
            $weekAgo = now()->subDays(6)->startOfDay();
            $topSource = Visit::where('started_at', '>=', $weekAgo)->toBase()
                ->selectRaw('source, COUNT(*) AS c')->groupBy('source')->orderByDesc('c')->first();
            $website = [
                'visits_today' => Visit::where('started_at', '>=', now()->startOfDay())->count(),
                'visitors_7d' => Visit::where('started_at', '>=', $weekAgo)->distinct()->count('visitor_id'),
                'top_source' => $topSource ? (Visit::SOURCES[$topSource->source] ?? ucfirst($topSource->source)) : null,
                'top_source_visits' => $topSource?->c,
                'leads_today' => Lead::where('channel', 'website')->where('created_at', '>=', now()->startOfDay())->count(),
            ];
        }

        return view('admin.dashboard', [
            'user' => $user,
            'isManager' => $isManager,
            // Only people who can act on it see the cron warning.
            'schedulerDown' => $isManager && ! Scheduler::isRunning(),
            'myDay' => $myDay,
            'overdueCount' => $overdueCount,
            'kpis' => $kpis,
            'pipeline' => $pipeline,
            'unassigned' => $unassigned,
            'recent' => $recent,
            'website' => $website,
            'agents' => $isManager ? User::active()->whereIn('role', ['sales', 'sales_manager', 'admin'])->orderByRaw("CASE role WHEN 'sales' THEN 1 WHEN 'sales_manager' THEN 2 ELSE 3 END")->orderBy('name')->pluck('name', 'id') : collect(),
        ]);
    }
}
