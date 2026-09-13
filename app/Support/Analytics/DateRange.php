<?php

namespace App\Support\Analytics;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * The reporting window of an analytics page, read from ?range=… (and ?from=/?to= for custom).
 * All dates are in the app timezone (Africa/Cairo), which is also how the tracker stores them.
 */
final class DateRange
{
    public const PRESETS = [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        '90d' => 'Last 90 days',
        'month' => 'This month',
        'custom' => 'Custom range',
    ];

    public const DEFAULT = '30d';

    public const MAX_DAYS = 400;

    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly string $key = 'custom',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $key = (string) $request->query('range', self::DEFAULT);
        if (! array_key_exists($key, self::PRESETS)) {
            $key = self::DEFAULT;
        }

        $today = CarbonImmutable::today();

        if ($key === 'custom') {
            $from = self::parse($request->query('from'));
            $to = self::parse($request->query('to'));
            if (! $from || ! $to) {
                return self::preset(self::DEFAULT, $today);
            }
            if ($from->greaterThan($to)) {
                [$from, $to] = [$to, $from];
            }
            if ($from->diffInDays($to) > self::MAX_DAYS) {
                $from = $to->subDays(self::MAX_DAYS);
            }

            return new self($from->startOfDay(), $to->endOfDay(), 'custom');
        }

        return self::preset($key, $today);
    }

    public static function preset(string $key, ?CarbonImmutable $today = null): self
    {
        $today ??= CarbonImmutable::today();

        [$from, $to] = match ($key) {
            'today' => [$today, $today],
            'yesterday' => [$today->subDay(), $today->subDay()],
            '7d' => [$today->subDays(6), $today],
            '90d' => [$today->subDays(89), $today],
            'month' => [$today->startOfMonth(), $today],
            default => [$today->subDays(29), $today],
        };

        return new self($from->startOfDay(), $to->endOfDay(), array_key_exists($key, self::PRESETS) ? $key : self::DEFAULT);
    }

    private static function parse(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }

    /** The period of equal length right before this one. */
    public function previous(): self
    {
        $days = $this->days();

        return new self($this->from->subDays($days)->startOfDay(), $this->from->subDay()->endOfDay(), 'previous');
    }

    public function days(): int
    {
        return (int) round($this->from->startOfDay()->diffInDays($this->to->startOfDay())) + 1;
    }

    public function isSingleDay(): bool
    {
        return $this->days() === 1;
    }

    /** @return array{0: string, 1: string} */
    public function bounds(): array
    {
        return [$this->from->toDateTimeString(), $this->to->toDateTimeString()];
    }

    /** @return list<CarbonImmutable> */
    public function dates(): array
    {
        $dates = [];
        for ($d = $this->from->startOfDay(); $d->lessThanOrEqualTo($this->to); $d = $d->addDay()) {
            $dates[] = $d;
        }

        return $dates;
    }

    public function label(): string
    {
        if ($this->isSingleDay()) {
            return $this->from->format('D j M Y');
        }

        $sameYear = $this->from->year === $this->to->year;

        return $this->from->format($sameYear ? 'j M' : 'j M Y').' – '.$this->to->format('j M Y');
    }

    public function presetLabel(): string
    {
        return $this->key === 'custom' ? $this->label() : (self::PRESETS[$this->key] ?? $this->label());
    }

    /** Query-string parameters that reproduce this range. */
    public function query(): array
    {
        return $this->key === 'custom'
            ? ['range' => 'custom', 'from' => $this->from->toDateString(), 'to' => $this->to->toDateString()]
            : ['range' => $this->key];
    }
}
