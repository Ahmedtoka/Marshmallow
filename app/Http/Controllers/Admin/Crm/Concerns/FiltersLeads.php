<?php

namespace App\Http\Controllers\Admin\Crm\Concerns;

use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Lead;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Shared lead filtering for the list, the board and the CSV export. */
trait FiltersLeads
{
    public const VIEWS = [
        'open' => 'Open',
        'new' => 'New',
        'overdue' => 'Overdue follow-ups',
        'tour_booked' => 'Tours booked',
        'enrolled' => 'Enrolled',
        'lost' => 'Not interested',
        'all' => 'All',
    ];

    /** Visible leads narrowed by the query-string filters (not the quick view tab). */
    protected function filteredLeads(Request $request): Builder
    {
        $user = $request->user();
        $query = Lead::query()->visibleTo($user);

        if ($q = trim((string) $request->query('q'))) {
            $digits = preg_replace('/\D/', '', $q);
            $query->where(function (Builder $w) use ($q, $digits) {
                $w->where('reference', 'like', "%{$q}%")
                    ->orWhere('parent_name', 'like', "%{$q}%")
                    ->orWhere('child_name', 'like', "%{$q}%");
                if (strlen($digits) >= 3) {
                    $w->orWhere('phone', 'like', "%{$digits}%")->orWhere('whatsapp', 'like', "%{$digits}%");
                }
            });
        }

        if (($status = $request->query('status')) && array_key_exists($status, Lead::STATUSES)) {
            $query->where('status', $status);
        }
        if ($branch = $request->integer('branch')) {
            $query->where('branch_id', $branch);
        }
        if ($class = $request->integer('class')) {
            $query->where('classroom_id', $class);
        }
        if (($interest = $request->query('interest')) && array_key_exists($interest, Lead::INTERESTS)) {
            $query->where('interest', $interest);
        }
        if ($source = (string) $request->query('source')) {
            [$kind, $value] = array_pad(explode(':', $source, 2), 2, '');
            if ($kind === 'src') {
                $query->where('source', $value);
            } elseif ($kind === 'ch') {
                $query->where('channel', $value);
            }
        }
        if (! $user->isSales() && ($assignee = $request->query('assignee'))) {
            $assignee === 'none' ? $query->whereNull('assigned_to') : $query->where('assigned_to', (int) $assignee);
        }
        if ($from = $this->dateParam($request, 'from')) {
            $query->where('created_at', '>=', $from->startOfDay());
        }
        if ($to = $this->dateParam($request, 'to')) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        return $query;
    }

    protected function applyView(Builder $query, string $view): Builder
    {
        return match ($view) {
            'open' => $query->open(),
            'new' => $query->where('status', 'new'),
            'overdue' => $query->open()->where('next_follow_up_at', '<', now()),
            'tour_booked' => $query->where('status', 'tour_booked'),
            'enrolled' => $query->where('status', 'enrolled'),
            'lost' => $query->where('status', 'lost'),
            default => $query,
        };
    }

    /** All tab counts in a single aggregate query. */
    protected function viewCounts(Builder $base): array
    {
        $row = (clone $base)->toBase()->selectRaw("
            SUM(CASE WHEN status NOT IN ('enrolled','lost') THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
            SUM(CASE WHEN status NOT IN ('enrolled','lost') AND next_follow_up_at < ? THEN 1 ELSE 0 END) AS overdue_count,
            SUM(CASE WHEN status = 'tour_booked' THEN 1 ELSE 0 END) AS tour_booked_count,
            SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) AS enrolled_count,
            SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) AS lost_count,
            COUNT(*) AS all_count", [now()])->first();

        return collect(array_keys(self::VIEWS))->mapWithKeys(fn ($v) => [$v => (int) ($row->{$v.'_count'} ?? 0)])->all();
    }

    /** Source filter options: tracked website sources and manual channels. */
    protected function sourceOptions(): array
    {
        $options = [];
        foreach (Visit::SOURCES as $key => $label) {
            $options['src:'.$key] = 'Website · '.$label;
        }
        foreach (Lead::CHANNELS as $key => $label) {
            $options['ch:'.$key] = $label;
        }

        return $options;
    }

    protected function filterOptions(): array
    {
        return [
            'branches' => Branch::orderBy('sort_order')->pluck('name', 'id')->all(),
            'classrooms' => Classroom::orderBy('min_months')->get(['id', 'name', 'color']),
            'agents' => $this->assignableUsers()->pluck('name', 'id')->all(),
            'sources' => $this->sourceOptions(),
        ];
    }

    protected function assignableUsers()
    {
        return User::active()->whereIn('role', ['sales', 'sales_manager', 'admin'])
            ->orderByRaw("CASE role WHEN 'sales' THEN 1 WHEN 'sales_manager' THEN 2 ELSE 3 END")->orderBy('name')->get();
    }

    protected function sourceLabel(Lead $lead): string
    {
        if ($lead->channel === 'website') {
            return 'Website'.($lead->source ? ' · '.(Visit::SOURCES[$lead->source] ?? ucfirst($lead->source)) : '');
        }

        return Lead::CHANNELS[$lead->channel] ?? ucfirst((string) $lead->channel);
    }

    /** 404 for leads the user is not allowed to see (sales agents only see their own). */
    protected function ensureVisible(Request $request, Lead $lead): void
    {
        $user = $request->user();
        abort_if($user->isSales() && (int) $lead->assigned_to !== (int) $user->id, 404);
    }

    private function dateParam(Request $request, string $key): ?Carbon
    {
        $value = (string) $request->query($key);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
