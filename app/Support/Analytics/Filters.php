<?php

namespace App\Support\Analytics;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Source / device / campaign filters shared by the analytics pages. Unknown values are ignored. */
final class Filters
{
    public const DEVICES = ['mobile' => 'Mobile', 'tablet' => 'Tablet', 'desktop' => 'Desktop'];

    public function __construct(
        public readonly ?string $source = null,
        public readonly ?string $device = null,
        public readonly ?string $campaign = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $source = (string) $request->query('source', '');
        $device = (string) $request->query('device', '');
        $campaign = trim((string) $request->query('campaign', ''));

        return new self(
            array_key_exists($source, Visit::SOURCES) ? $source : null,
            array_key_exists($device, self::DEVICES) ? $device : null,
            $campaign !== '' ? Str::limit($campaign, 190, '') : null,
        );
    }

    public function any(): bool
    {
        return $this->source || $this->device || $this->campaign;
    }

    public function query(): array
    {
        return array_filter(['source' => $this->source, 'device' => $this->device, 'campaign' => $this->campaign]);
    }
}
