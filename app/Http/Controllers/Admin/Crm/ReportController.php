<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public const PRESETS = ['7d' => 'Last 7 days', '30d' => 'Last 30 days', 'month' => 'This month', 'last_month' => 'Last month', 'custom' => 'Custom'];

    private const CONTACT_TYPES = ['call', 'whatsapp', 'email'];

    private const RESPONSE_TYPES = ['call', 'whatsapp', 'email', 'status_changed'];

    public function index(Request $request)
    {
        [$preset, $from, $to] = $this->range($request);

        $leads = Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->get(['id', 'status', 'source', 'channel', 'branch_id', 'classroom_id', 'assigned_to', 'lost_reason', 'created_at']);
        $ids = $leads->pluck('id');

        // Activity facts for the cohort, loaded once.
        $activities = $ids->isEmpty() ? collect() : LeadActivity::query()
            ->whereIn('lead_id', $ids)
            ->whereIn('type', self::RESPONSE_TYPES)
            ->orderBy('id')
            ->get(['lead_id', 'user_id', 'type', 'meta', 'created_at']);

        $contactedIds = $activities->whereIn('type', self::CONTACT_TYPES)->pluck('lead_id')->flip();
        $touredIds = $activities->where('type', 'status_changed')->filter(fn ($a) => ($a->meta['to'] ?? null) === 'tour_booked')->pluck('lead_id')->flip();
        $firstResponse = $activities->whereNotNull('user_id')->groupBy('lead_id')->map(fn ($g) => $g->first()->created_at);

        $isContacted = fn (Lead $l) => $l->status !== 'new' || isset($contactedIds[$l->id]);
        $isTour = fn (Lead $l) => in_array($l->status, ['tour_booked', 'toured', 'enrolled'], true) || isset($touredIds[$l->id]);
        $responseMinutes = fn (Lead $l) => isset($firstResponse[$l->id]) ? max(0, (int) round($l->created_at->diffInMinutes($firstResponse[$l->id]))) : null;

        $total = $leads->count();
        $enrolled = $leads->where('status', 'enrolled')->count();
        $responses = $leads->map($responseMinutes)->filter(fn ($m) => $m !== null)->values();

        $kpis = [
            'leads' => $total,
            'contacted_pct' => $this->pct($leads->filter($isContacted)->count(), $total),
            'tours' => $leads->filter($isTour)->count(),
            'enrolled' => $enrolled,
            'conversion_pct' => $this->pct($enrolled, $total),
            'median_response' => $this->duration($this->median($responses)),
            'overdue_now' => FollowUp::pending()->where('due_at', '<', now())->whereHas('lead')->count(),
        ];

        // Leads per week, stacked by where they are now.
        $weeks = [];
        for ($w = $from->startOfWeek(); $w <= $to; $w = $w->addWeek()) {
            $weeks[$w->toDateString()] = $w->format('j M');
        }
        $byWeek = $leads->groupBy(fn ($l) => CarbonImmutable::instance($l->created_at)->startOfWeek()->toDateString());
        $weekly = [
            'labels' => array_values($weeks),
            'datasets' => collect(Lead::STATUSES)->map(fn ($label, $status) => [
                'label' => $label,
                'backgroundColor' => Lead::STATUS_COLORS[$status],
                'data' => collect(array_keys($weeks))->map(fn ($wk) => ($byWeek[$wk] ?? collect())->where('status', $status)->count())->all(),
            ])->values()->all(),
        ];

        $sourceKey = fn (Lead $l) => $l->channel === 'website'
            ? ($l->source ? (Visit::SOURCES[$l->source] ?? ucfirst($l->source)) : 'Website (unknown)')
            : (Lead::CHANNELS[$l->channel] ?? ucfirst((string) $l->channel));
        $branchNames = Branch::pluck('name', 'id');
        $classes = Classroom::orderBy('min_months')->get(['id', 'name', 'color']);

        $charts = [
            'weekly' => $weekly,
            'source' => $this->leadsAndEnrolled($leads->groupBy($sourceKey)->sortByDesc(fn ($g) => $g->count())),
            'branch' => $this->leadsAndEnrolled($leads->groupBy(fn ($l) => $branchNames[$l->branch_id] ?? 'No branch')),
            'class' => $this->leadsAndEnrolled(
                $classes->mapWithKeys(fn ($c) => [$c->name => $leads->where('classroom_id', $c->id)])
                    ->put('No class yet', $leads->whereNull('classroom_id'))
                    ->filter(fn ($g) => $g->isNotEmpty())
            ),
        ];
        $charts['class']['colors'] = collect($charts['class']['labels'])->map(fn ($n) => $classes->firstWhere('name', $n)?->color ?? '#9B98B8')->all();

        // Agent performance.
        $overdueByUser = FollowUp::pending()->where('due_at', '<', now())->whereHas('lead')
            ->toBase()->selectRaw('user_id, COUNT(*) AS c')->groupBy('user_id')->pluck('c', 'user_id');
        $agentIds = $leads->pluck('assigned_to')->filter()->merge(User::active()->where('role', 'sales')->pluck('id'))->unique();
        $agentUsers = User::whereIn('id', $agentIds)->orderBy('name')->get(['id', 'name', 'role']);

        $agents = $agentUsers->map(function (User $agent) use ($leads, $isContacted, $isTour, $responseMinutes, $overdueByUser) {
            $mine = $leads->where('assigned_to', $agent->id);
            $times = $mine->map($responseMinutes)->filter(fn ($m) => $m !== null);

            return [
                'user' => $agent,
                'assigned' => $mine->count(),
                'contacted' => $mine->filter($isContacted)->count(),
                'tours' => $mine->filter($isTour)->count(),
                'enrolled' => $mine->where('status', 'enrolled')->count(),
                'conversion' => $this->pct($mine->where('status', 'enrolled')->count(), $mine->count()),
                'overdue' => (int) ($overdueByUser[$agent->id] ?? 0),
                'avg_response' => $this->duration($times->isEmpty() ? null : $times->avg()),
            ];
        })->sortByDesc('assigned')->values();

        $unassigned = $leads->whereNull('assigned_to')->count();

        $lostReasons = $leads->where('status', 'lost')
            ->groupBy(fn ($l) => $l->lost_reason ?: 'No reason given')
            ->map->count()->sortDesc();

        return view('admin.crm.reports.index', compact('preset', 'from', 'to', 'kpis', 'charts', 'agents', 'unassigned', 'lostReasons', 'total'));
    }

    /** @return array{0: string, 1: CarbonImmutable, 2: CarbonImmutable} */
    private function range(Request $request): array
    {
        $preset = array_key_exists($request->query('range'), self::PRESETS) ? $request->query('range') : 'month';
        $today = CarbonImmutable::now();

        [$from, $to] = match ($preset) {
            '7d' => [$today->subDays(6)->startOfDay(), $today->endOfDay()],
            '30d' => [$today->subDays(29)->startOfDay(), $today->endOfDay()],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'custom' => [$this->date($request->query('from')) ?? $today->startOfMonth(), ($this->date($request->query('to')) ?? $today)->endOfDay()],
            default => [$today->startOfMonth(), $today->endOfDay()],
        };

        if ($from > $to) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        return [$preset, $from, $to];
    }

    private function date($value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function leadsAndEnrolled(Collection $groups): array
    {
        return [
            'labels' => $groups->keys()->map(fn ($k) => (string) $k)->values()->all(),
            'leads' => $groups->map->count()->values()->all(),
            'enrolled' => $groups->map(fn ($g) => $g->where('status', 'enrolled')->count())->values()->all(),
        ];
    }

    private function pct(int $part, int $whole): ?int
    {
        return $whole ? (int) round($part / $whole * 100) : null;
    }

    private function median(Collection $values): ?float
    {
        $sorted = $values->sort()->values();
        $n = $sorted->count();
        if (! $n) {
            return null;
        }

        return $n % 2 ? $sorted[intdiv($n, 2)] : ($sorted[$n / 2 - 1] + $sorted[$n / 2]) / 2;
    }

    /** 135 → "2h 15m", 3000 → "2d 2h". */
    public static function formatMinutes(?float $minutes): ?string
    {
        if ($minutes === null) {
            return null;
        }
        $m = (int) round($minutes);
        if ($m < 60) {
            return $m.'m';
        }
        if ($m < 1440) {
            return intdiv($m, 60).'h'.($m % 60 ? ' '.($m % 60).'m' : '');
        }

        return intdiv($m, 1440).'d'.(intdiv($m % 1440, 60) ? ' '.intdiv($m % 1440, 60).'h' : '');
    }

    private function duration(?float $minutes): ?string
    {
        return self::formatMinutes($minutes);
    }
}
