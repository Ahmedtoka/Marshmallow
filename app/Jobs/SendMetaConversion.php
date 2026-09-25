<?php

namespace App\Jobs;

use App\Models\MetaConversion;
use App\Services\MetaConversions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Delivers one event to the Meta Conversions API and records Meta's answer on its meta_conversions row.
 * Network problems, rate limits and Meta outages are retried; a rejected event (bad token, bad data)
 * fails straight away because sending it again would get the same answer.
 */
class SendMetaConversion implements ShouldQueue
{
    use Queueable;

    /** Seconds to wait before attempts 2, 3, 4 and 5. Meta accepts events up to 7 days late. */
    public const BACKOFF = [60, 300, 900, 3600];

    public int $tries = 5;

    public int $timeout = 40;

    /** @param  array  $event  Ready to send: personal data is already hashed. */
    public function __construct(public int $conversionId, public array $event) {}

    public function handle(MetaConversions $meta): void
    {
        $row = MetaConversion::find($this->conversionId);
        if (! $row || $row->status === 'sent') {
            return;
        }

        $row->increment('attempts');

        if (! MetaConversions::enabled()) {
            $this->finish($row, false, null, 'The Meta token or pixel id was removed before this event could be sent.');

            return;
        }

        try {
            $response = $meta->post($this->event);
        } catch (ConnectionException $e) {
            $this->finish($row, true, null, 'Could not reach Meta: '.MetaConversions::scrub($e->getMessage()));

            return;
        }

        if ($response->successful()) {
            $row->update([
                'status' => 'sent',
                'response_code' => $response->status(),
                'events_received' => $response->json('events_received'),
                'fbtrace_id' => $response->json('fbtrace_id'),
                'error' => null,
                'sent_at' => now(),
            ]);
            Log::info('Meta CAPI: event sent', $this->context($row) + ['events_received' => $response->json('events_received')]);

            return;
        }

        $error = (array) $response->json('error');
        $message = trim(($error['message'] ?? 'HTTP '.$response->status()).' '.($error['error_user_msg'] ?? ''));
        $transient = $response->serverError() || $response->status() === 429 || ! empty($error['is_transient']);

        $this->finish($row, $transient, $response->status(), MetaConversions::scrub($message), $error['fbtrace_id'] ?? null);
    }

    /** Anything unexpected that escaped handle() (the worker gave up): make sure the dashboard shows it. */
    public function failed(?Throwable $e): void
    {
        MetaConversion::whereKey($this->conversionId)->whereIn('status', ['queued', 'retrying'])->update([
            'status' => 'failed',
            'error' => Str::limit(MetaConversions::scrub((string) $e?->getMessage()) ?: 'The queue gave up on this event.', 1000),
        ]);
    }

    private function finish(MetaConversion $row, bool $transient, ?int $code, string $message, ?string $trace = null): void
    {
        $retry = $transient && $this->attempts() < $this->tries;

        $row->update([
            'status' => $retry ? 'retrying' : 'failed',
            'response_code' => $code,
            'fbtrace_id' => $trace,
            'error' => Str::limit($message, 1000),
        ]);
        Log::warning($retry ? 'Meta CAPI: will retry' : 'Meta CAPI: event failed', $this->context($row) + ['code' => $code, 'error' => $message]);

        if ($retry) {
            $this->release(self::BACKOFF[$this->attempts() - 1] ?? 3600);
        } else {
            // Recorded in failed_jobs as well, so `php artisan queue:retry` can resend it once the cause is fixed.
            $this->fail(new RuntimeException("Meta CAPI {$row->event_name} failed: {$message}"));
        }
    }

    /** What we log: never the hashed or raw customer data, only which event it was. */
    private function context(MetaConversion $row): array
    {
        return [
            'conversion' => $row->id,
            'event' => $row->event_name,
            'event_id' => $row->event_id,
            'attempt' => $this->attempts(),
            'test' => $row->test_event,
        ];
    }
}
