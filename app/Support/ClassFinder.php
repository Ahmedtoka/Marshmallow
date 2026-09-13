<?php

namespace App\Support;

use App\Models\Classroom;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Works out which class a child belongs to.
 *
 * Age is measured on 1 October of the chosen academic year (the Egyptian school cut-off).
 * If that date has already passed, the child joins mid-year so today's age is used instead.
 * Class ranges are [min, max) in months, so a child exactly on a boundary moves up.
 */
class ClassFinder
{
    public const CUTOFF_MONTH = 10;

    public const CUTOFF_DAY = 1;

    /** @return list<string> e.g. ['2026-2027', '2027-2028'] */
    public static function academicYears(): array
    {
        $configured = collect(explode(',', (string) Setting::get('admission_years', '')))
            ->map(fn ($y) => trim($y))
            ->filter(fn ($y) => preg_match('/^\d{4}-\d{4}$/', $y))
            ->values()
            ->all();

        if ($configured) {
            return $configured;
        }

        $start = now()->month >= 7 ? now()->year : now()->year - 1;

        return [$start.'-'.($start + 1), ($start + 1).'-'.($start + 2)];
    }

    public static function referenceDate(string $academicYear): CarbonImmutable
    {
        $startYear = (int) substr($academicYear, 0, 4) ?: now()->year;
        $cutoff = CarbonImmutable::create($startYear, self::CUTOFF_MONTH, self::CUTOFF_DAY)->startOfDay();
        $today = CarbonImmutable::today();

        return $cutoff->lessThan($today) ? $today : $cutoff;
    }

    public static function ageInMonths(\DateTimeInterface $dob, \DateTimeInterface $on): int
    {
        return max(0, (int) floor(CarbonImmutable::instance($dob)->diffInMonths(CarbonImmutable::instance($on))));
    }

    /**
     * @return array{status: 'match'|'too_young'|'too_old', months: int, reference_date: string, classroom: ?Classroom, next: ?Classroom}
     */
    public static function find(\DateTimeInterface $dob, string $academicYear, ?Collection $classrooms = null): array
    {
        $classrooms ??= Classroom::active()->get();
        $reference = self::referenceDate($academicYear);
        $months = self::ageInMonths($dob, $reference);

        $match = $classrooms->first(fn (Classroom $c) => $c->fitsAge($months));
        $youngest = $classrooms->sortBy('min_months')->first();

        $status = match (true) {
            $match !== null => 'match',
            $youngest && $months < $youngest->min_months => 'too_young',
            default => 'too_old',
        };

        return [
            'status' => $status,
            'months' => $months,
            'reference_date' => $reference->toDateString(),
            'classroom' => $match,
            'next' => $status === 'too_young' ? $youngest : null,
        ];
    }

    /** Data the browser-side finder needs to give the same answer as the server. */
    public static function clientConfig(Collection $classrooms): array
    {
        return [
            'years' => self::academicYears(),
            'cutoff' => ['month' => self::CUTOFF_MONTH, 'day' => self::CUTOFF_DAY],
            'classes' => $classrooms->map(fn (Classroom $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'min' => $c->min_months,
                'max' => $c->max_months,
                'age' => $c->ageRangeLabel(),
                'color' => $c->color,
                'icon' => $c->icon,
                'tagline' => $c->tagline,
                'url' => route('classes.show', $c),
            ])->values()->all(),
        ];
    }
}
