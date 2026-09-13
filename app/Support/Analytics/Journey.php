<?php

namespace App\Support\Analytics;

use App\Models\Lead;
use App\Models\PageView;
use App\Models\TrackingEvent;
use App\Models\Visit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Builds visitor behaviour badges and per-visit timelines for the journey screens. */
final class Journey
{
    public const KEY_EVENTS = ['call_click', 'whatsapp_click', 'map_click', 'class_finder', 'form_start', 'form_submit'];

    public const EVENT_ICONS = [
        'call_click' => 'phone',
        'whatsapp_click' => 'whatsapp',
        'map_click' => 'map-pin',
        'email_click' => 'mail',
        'class_finder' => 'search',
        'cta_click' => 'pointer',
        'form_start' => 'pencil',
        'form_submit' => 'check',
        'form_error' => 'alert',
        'faq_open' => 'help',
        'gallery_open' => 'image',
        'video_play' => 'camera',
        'outbound_click' => 'external',
        'section_view' => 'layers',
    ];

    /**
     * Key-behaviour summary for many visitors in one query.
     *
     * @param  array<int>  $visitorIds
     * @return Collection<int, Collection> visitor_id → rows of (name, label, n, last_id)
     */
    public static function summaries(array $visitorIds): Collection
    {
        if (! $visitorIds) {
            return collect();
        }

        return DB::table('tracking_events')
            ->whereIn('visitor_id', $visitorIds)
            ->whereIn('name', self::KEY_EVENTS)
            ->selectRaw('visitor_id, name, label, COUNT(*) AS n, MAX(id) AS last_id')
            ->groupBy('visitor_id', 'name', 'label')
            ->get()
            ->groupBy('visitor_id');
    }

    /**
     * @param  Collection|null  $rows  rows from summaries() for one visitor
     * @return list<array{label: string, class: string, icon: string, url?: string}>
     */
    public static function badges(?Collection $rows, ?Lead $lead = null): array
    {
        $rows ??= collect();
        $has = fn (string $name) => $rows->contains('name', $name);
        $badges = [];

        if ($lead) {
            $badges[] = ['label' => 'Became lead · '.$lead->reference, 'class' => 'badge-pink', 'icon' => 'star', 'url' => route('admin.crm.leads.show', $lead)];
        }
        if ($has('call_click')) {
            $badges[] = ['label' => 'Called', 'class' => 'badge-green', 'icon' => 'phone'];
        }
        if ($has('whatsapp_click')) {
            $badges[] = ['label' => 'WhatsApp', 'class' => 'badge-green', 'icon' => 'whatsapp'];
        }
        if ($has('map_click')) {
            $badges[] = ['label' => 'Opened map', 'class' => 'badge-muted', 'icon' => 'map-pin'];
        }
        if ($finder = $rows->where('name', 'class_finder')->sortByDesc('last_id')->first()) {
            $badges[] = ['label' => 'Class finder → '.($finder->label ?: 'result'), 'class' => 'badge-muted', 'icon' => 'search'];
        }
        $startedEnroll = $rows->where('name', 'form_start')->filter(fn ($r) => $r->label !== 'careers')->isNotEmpty();
        $submittedEnroll = $rows->where('name', 'form_submit')->filter(fn ($r) => $r->label !== 'careers')->isNotEmpty();
        if ($startedEnroll && ! $submittedEnroll && ! $lead) {
            $badges[] = ['label' => "Started form, didn't submit", 'class' => 'badge-red', 'icon' => 'alert'];
        }

        return $badges;
    }

    /**
     * Chronological items for one visit: page views, events (consecutive section views are grouped) and the lead.
     *
     * @return list<array<string, mixed>>
     */
    public static function timeline(Visit $visit, ?Lead $lead = null): array
    {
        $items = [];

        foreach ($visit->pageViews as $pv) {
            /** @var PageView $pv */
            $items[] = [
                'type' => 'page',
                'at' => $pv->entered_at,
                'sort' => $pv->entered_at->getTimestamp() * 10,
                'icon' => 'file',
                'title' => PageName::for($pv->path),
                'subtitle' => $pv->path.($pv->query ? $pv->query : ''),
                'duration' => (int) $pv->duration_seconds,
                'scroll' => (int) $pv->max_scroll,
                'highlight' => $pv->path === '/thank-you',
            ];
        }

        foreach ($visit->events as $event) {
            /** @var TrackingEvent $event */
            $items[] = [
                'type' => 'event',
                'name' => $event->name,
                'at' => $event->created_at,
                'sort' => $event->created_at->getTimestamp() * 10 + 1,
                'icon' => self::EVENT_ICONS[$event->name] ?? 'pointer',
                'title' => $event->label(),
                'subtitle' => self::eventDetail($event),
                'highlight' => $event->name === 'form_submit',
                'key' => in_array($event->name, ['call_click', 'whatsapp_click', 'map_click', 'class_finder', 'form_start'], true),
            ];
        }

        if ($lead && $lead->visit_id === $visit->id && $lead->created_at) {
            $items[] = [
                'type' => 'lead',
                'at' => $lead->created_at,
                'sort' => $lead->created_at->getTimestamp() * 10 + 2,
                'icon' => 'star',
                'title' => 'Lead created · '.$lead->reference,
                'subtitle' => trim($lead->parent_name.($lead->child_name ? ' for '.$lead->child_name : '')),
                'highlight' => true,
            ];
        }

        usort($items, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        // Group consecutive homepage section views into one readable line.
        $grouped = [];
        foreach ($items as $item) {
            $last = array_key_last($grouped);
            if (($item['name'] ?? null) === 'section_view') {
                $section = PageName::section($item['subtitle']);
                if ($last !== null && ($grouped[$last]['name'] ?? null) === 'section_views') {
                    $grouped[$last]['sections'][] = $section;
                    $grouped[$last]['subtitle'] = implode(' · ', $grouped[$last]['sections']);
                    continue;
                }
                $grouped[] = [
                    'type' => 'event', 'name' => 'section_views', 'at' => $item['at'], 'icon' => 'layers',
                    'title' => 'Scrolled through sections', 'sections' => [$section], 'subtitle' => $section, 'highlight' => false, 'muted' => true,
                ];
                continue;
            }
            $grouped[] = $item;
        }

        return $grouped;
    }

    public static function eventDetail(TrackingEvent $event): ?string
    {
        $props = $event->properties ?? [];

        return match ($event->name) {
            'class_finder' => collect([
                $event->label,
                isset($props['months']) ? ((int) $props['months']).' months old' : null,
                $props['year'] ?? null,
            ])->filter()->implode(' · '),
            'section_view' => $event->label,
            'form_start', 'form_submit' => match ($event->label) {
                'enroll' => 'Enrollment form',
                'careers' => 'Careers form',
                default => $event->label,
            },
            'outbound_click' => $props['url'] ?? $event->label,
            default => $event->label,
        };
    }

    /** "Facebook · l.facebook.com · summer_camp" */
    public static function visitSource(Visit $visit): string
    {
        return collect([$visit->sourceLabel(), $visit->referrer_host, $visit->utm_campaign])->filter()->unique()->implode(' · ');
    }
}
