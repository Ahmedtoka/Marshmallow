<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Notifications\LeadAssignedNotification;
use App\Notifications\NewLeadNotification;
use App\Support\ClassFinder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * The only place leads are created, assigned or moved between stages,
 * so every change lands in the lead's timeline.
 */
class LeadService
{
    /** A parent submitted a form on the website. Links the lead to their tracked journey. */
    public function createFromWebsite(array $data, Request $request): Lead
    {
        $visitorUuid = $request->cookie('mm_vid');
        $visitUuid = $request->cookie('mm_sid');
        $visitor = Str::isUuid((string) $visitorUuid) ? Visitor::where('uuid', $visitorUuid)->first() : null;
        $visit = $visitor && Str::isUuid((string) $visitUuid)
            ? Visit::where('uuid', $visitUuid)->where('visitor_id', $visitor->id)->first()
            : null;
        $visit ??= $visitor?->visits()->first();

        $attribution = [
            'channel' => 'website',
            'visitor_uuid' => $visitor?->uuid,
            'visit_id' => $visit?->id,
            'source' => $visit?->source ?? $visitor?->first_source,
            'utm_source' => $visit?->utm_source,
            'utm_medium' => $visit?->utm_medium,
            'utm_campaign' => $visit?->utm_campaign,
            'utm_content' => $visit?->utm_content,
            'utm_term' => $visit?->utm_term,
            'referrer' => $visit?->referrer,
            'landing_path' => $visit?->landing_path ?? $visitor?->first_landing_path,
            'form_path' => Str::limit((string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH), 250, '') ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            // Shared by the thank-you page pixel and the Conversions API so Meta counts the booking once.
            'meta_event_id' => (string) Str::uuid(),
        ];

        $lead = $this->create($data + $attribution);

        if ($visitor) {
            $visitor->update(['lead_id' => $lead->id]);
            $visit?->update(['converted' => true, 'is_bounce' => false]);
        }

        app(MetaConversions::class)->trackLead($lead, $request);

        return $lead;
    }

    /** Create a lead from the website or manually from the dashboard. */
    public function create(array $data, ?User $by = null): Lead
    {
        $lead = DB::transaction(function () use ($data, $by) {
            $data = $this->withClassroom($data);
            $data['status'] ??= 'new';
            $data['channel'] ??= $by ? 'phone' : 'website';

            $lead = Lead::create($data);
            $this->log($lead, 'created', $by ? 'Added by '.$by->name : 'Submitted the website form', [
                'channel' => $lead->channel,
                'interest' => $lead->interest,
            ], $by);

            if ($lead->assigned_to) {
                $this->log($lead, 'assigned', 'Assigned to '.$lead->assignee->name, ['to' => $lead->assigned_to], $by);
            } elseif (Setting::get('lead_auto_assign', '1') === '1') {
                $this->autoAssign($lead);
            }

            return $lead;
        });

        $this->notifyNewLead($lead->fresh(['assignee', 'branch', 'classroom']));

        return $lead;
    }

    /** Assign to the active sales agent in the lead's branch with the fewest open leads. */
    public function autoAssign(Lead $lead): ?User
    {
        $candidates = User::active()->where('role', 'sales')
            ->when($lead->branch_id, fn ($q) => $q->where('branch_id', $lead->branch_id))
            ->withCount(['leads as open_leads_count' => fn ($q) => $q->open()])
            ->orderBy('open_leads_count')
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty() && $lead->branch_id) {
            $candidates = User::active()->where('role', 'sales')->whereNull('branch_id')
                ->withCount(['leads as open_leads_count' => fn ($q) => $q->open()])
                ->orderBy('open_leads_count')->get();
        }

        $agent = $candidates->first();
        if ($agent) {
            $lead->update(['assigned_to' => $agent->id]);
            $this->log($lead, 'assigned', 'Automatically assigned to '.$agent->name, ['to' => $agent->id, 'auto' => true]);
        }

        return $agent;
    }

    public function assign(Lead $lead, ?User $to, User $by): void
    {
        if ($lead->assigned_to === $to?->id) {
            return;
        }

        $lead->update(['assigned_to' => $to?->id]);
        $this->log($lead, 'assigned', $to ? 'Assigned to '.$to->name : 'Unassigned', ['to' => $to?->id], $by);

        if ($to && $to->id !== $by->id) {
            $this->safeNotify($to, new LeadAssignedNotification($lead, $by));
        }
    }

    public function changeStatus(Lead $lead, string $status, User $by, ?string $note = null, ?string $lostReason = null): void
    {
        abort_unless(array_key_exists($status, Lead::STATUSES), 422, 'Unknown status.');

        $from = $lead->status;
        if ($from === $status && ! $note) {
            return;
        }

        $lead->fill([
            'status' => $status,
            'lost_reason' => $status === 'lost' ? ($lostReason ?: $lead->lost_reason) : null,
            'enrolled_at' => $status === 'enrolled' ? ($lead->enrolled_at ?? now()) : null,
        ]);
        if (in_array($status, ['enrolled', 'lost'], true)) {
            $lead->next_follow_up_at = null;
            $lead->followUps()->whereNull('completed_at')->update(['completed_at' => now(), 'outcome' => 'Closed: '.Lead::STATUSES[$status]]);
        }
        if ($from === 'new' && $status !== 'new') {
            $lead->last_contacted_at ??= now();
        }
        $lead->save();

        $this->log($lead, 'status_changed', $note, [
            'from' => $from,
            'to' => $status,
            'lost_reason' => $status === 'lost' ? $lostReason : null,
        ], $by);
    }

    public function log(Lead $lead, string $type, ?string $body = null, array $meta = [], ?User $by = null): LeadActivity
    {
        if (in_array($type, ['call', 'whatsapp', 'email'], true)) {
            $lead->update(['last_contacted_at' => now()]);
        }

        return $lead->activities()->create([
            'user_id' => $by?->id,
            'type' => $type,
            'body' => $body,
            'meta' => array_filter($meta, fn ($v) => $v !== null) ?: null,
        ]);
    }

    /** Keep the lead's "next follow-up" in sync with its earliest pending follow-up. */
    public function syncNextFollowUp(Lead $lead): void
    {
        $lead->update(['next_follow_up_at' => $lead->followUps()->whereNull('completed_at')->min('due_at')]);
    }

    private function withClassroom(array $data): array
    {
        if (! empty($data['child_dob']) && empty($data['classroom_id'])) {
            $year = $data['academic_year'] ?? ClassFinder::academicYears()[0];
            $result = ClassFinder::find(Carbon::parse($data['child_dob']), $year);
            $data['academic_year'] = $year;
            $data['child_age_months'] = $result['months'];
            $data['classroom_id'] = $result['classroom']?->id;
            if ($result['status'] === 'too_young' && ($data['interest'] ?? 'enrollment') === 'enrollment') {
                $data['interest'] = 'waitlist';
            }
        }

        return $data;
    }

    private function notifyNewLead(Lead $lead): void
    {
        $recipients = User::active()->whereIn('role', ['admin', 'sales_manager'])->get();
        if ($lead->assignee) {
            $recipients->push($lead->assignee);
        }

        foreach ($recipients->unique('id') as $user) {
            $this->safeNotify($user, new NewLeadNotification($lead));
        }
    }

    private function safeNotify(User $user, $notification): void
    {
        try {
            Notification::send($user, $notification);
        } catch (\Throwable $e) {
            // A mail outage must never lose a lead; the in-dashboard notification is stored first.
            Log::warning('Lead notification failed: '.$e->getMessage());
        }
    }
}
