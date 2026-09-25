<?php

namespace App\Services;

use App\Http\Requests\Site\EnrollRequest;
use App\Jobs\SendMetaConversion;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\JobApplication;
use App\Models\Lead;
use App\Models\MetaConversion;
use App\Support\MetaParams;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side copies of the pixel's conversions (Meta Conversions API).
 *
 * Ad blockers and browser tracking prevention drop a large share of pixel events; sending the same
 * event from the server with the same event_id lets Meta fill the gaps and count each action once.
 * Personal data is normalized and SHA-256 hashed here, before it is queued, and is never logged.
 */
class MetaConversions
{
    // Both branches are in Giza and the form does not ask for the parent's address.
    public const CITY = 'giza';

    public const COUNTRY = 'eg';

    /** Needs both the pixel id (dashboard setting) and the access token (.env). Otherwise nothing happens. */
    public static function enabled(): bool
    {
        return filled(config('services.meta.capi_token')) && filled(self::pixelId());
    }

    public static function pixelId(): ?string
    {
        return setting('meta_pixel_id');
    }

    /** The custom data for a booking, shared by the thank-you page pixel and the server event. */
    public static function leadParams(?Classroom $classroom, ?Branch $branch, ?string $interest): array
    {
        return [
            'content_name' => $classroom?->name ?? 'No class yet',
            'content_category' => Lead::INTERESTS[$interest ?? 'enrollment'] ?? 'Enrollment',
            'branch' => $branch?->name,
        ];
    }

    public static function applicationParams(): array
    {
        return ['content_name' => 'Careers form'];
    }

    /** A parent booked on the website: Lead always, and Schedule when they asked to visit. */
    public function trackLead(Lead $lead, Request $request): void
    {
        if (! self::enabled() || ! $lead->meta_event_id) {
            return;
        }

        $this->safely($lead->meta_event_id, function () use ($lead, $request) {
            $lead->loadMissing('classroom', 'branch');
            $user = $this->userData([$lead->phone, $lead->whatsapp], $lead->email, $lead->parent_name, $request);
            $params = self::leadParams($lead->classroom, $lead->branch, $lead->interest);
            $url = $this->sourceUrl($request, route('enroll'));

            $this->queue('Lead', $lead->meta_event_id, $user, $params, $url, $lead->created_at, ['lead_id' => $lead->id]);

            if ($lead->interest === 'tour') {
                $this->queue('Schedule', $lead->meta_event_id, $user, $params, $url, $lead->created_at, ['lead_id' => $lead->id]);
            }
        });
    }

    public function trackApplication(JobApplication $application, Request $request): void
    {
        if (! self::enabled() || ! $application->meta_event_id) {
            return;
        }

        $this->safely($application->meta_event_id, function () use ($application, $request) {
            $user = $this->userData([$application->phone], $application->email, $application->name, $request);

            $this->queue('SubmitApplication', $application->meta_event_id, $user, self::applicationParams(),
                $this->sourceUrl($request, route('careers')), $application->created_at, ['job_application_id' => $application->id]);
        });
    }

    /** Called by the queued job. The token travels in the body, so it never shows up in a URL or an error. */
    public function post(array $event): Response
    {
        $body = ['data' => [$event], 'access_token' => config('services.meta.capi_token')];
        if ($code = config('services.meta.test_event_code')) {
            $body['test_event_code'] = $code;
        }

        return Http::acceptJson()
            ->connectTimeout(10)
            ->timeout(20)
            ->post(sprintf('https://graph.facebook.com/%s/%s/events', config('services.meta.graph_version'), self::pixelId()), $body);
    }

    /** Remove the token from any text we are about to store or log. */
    public static function scrub(string $text): string
    {
        $token = (string) config('services.meta.capi_token');

        return $token === '' ? $text : str_replace($token, '[token]', $text);
    }

    /** Numbers for the Settings → Tracking panel. */
    public static function summary(): array
    {
        $recent = MetaConversion::where('created_at', '>=', now()->subDay());

        return [
            'enabled' => self::enabled(),
            'has_token' => filled(config('services.meta.capi_token')),
            'has_pixel' => filled(self::pixelId()),
            'test_mode' => filled(config('services.meta.test_event_code')),
            'sent_24h' => (clone $recent)->where('status', 'sent')->count(),
            'failed_24h' => (clone $recent)->where('status', 'failed')->count(),
            'last' => MetaConversion::latest('id')->first(),
            'last_sent' => MetaConversion::where('status', 'sent')->latest('sent_at')->first(),
            'last_error' => MetaConversion::whereNotNull('error')->latest('updated_at')->first(),
            // Events still waiting after 10 minutes mean the queue worker in the scheduler is not running.
            'stuck' => MetaConversion::where('status', 'queued')->where('created_at', '<', now()->subMinutes(10))->count(),
            'recent' => MetaConversion::with('lead:id,reference,parent_name')->latest('id')->limit(10)->get(),
        ];
    }

    /** Hashed customer details plus the browser signals Meta matches on. Keys follow Meta's user_data names. */
    private function userData(array $phones, ?string $email, ?string $name, Request $request): array
    {
        $phones = array_values(array_unique(array_filter(array_map([self::class, 'normalizePhone'], $phones))));
        [$first, $last] = self::splitName($name);
        $email = mb_strtolower(trim((string) $email));
        $params = MetaParams::for($request);

        return array_filter([
            'ph' => $phones ? array_map([self::class, 'hash'], $phones) : null,
            'em' => $email !== '' ? [self::hash($email)] : null,
            'fn' => $first ? self::hash($first) : null,
            'ln' => $last ? self::hash($last) : null,
            'ct' => self::hash(self::CITY),
            'country' => self::hash(self::COUNTRY),
            // The same hashed visitor id the pixel sends (see the site layout).
            'external_id' => [MetaParams::externalId($request)],
            // Meta's Parameter Builder picks a public IP (IPv6 first) and skips private ones like 127.0.0.1.
            'client_ip_address' => $params->getClientIpAddress(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $params->getFbp(),
            'fbc' => $params->getFbc() ?? $this->fbcFromTracker($request),
        ]);
    }

    /** Meta wants digits with the country code: Egyptian 010 1234 5678 becomes 201012345678. */
    public static function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/\D/', '', (string) EnrollRequest::normalizePhone($phone));

        if (preg_match('/^01\d{9}$/', $phone)) {
            return '2'.$phone;
        }
        if (str_starts_with($phone, '00')) {
            $phone = substr($phone, 2);
        }

        return strlen($phone) >= 8 ? $phone : null;
    }

    /** "Mona  El-Sayed Ali" → ["mona", "ali"]: lowercase letters only, as Meta asks. One word is a first name. */
    public static function splitName(?string $name): array
    {
        $parts = collect(preg_split('/\s+/u', trim((string) $name)))
            ->map(fn ($part) => preg_replace('/[^\p{L}\p{M}]+/u', '', mb_strtolower($part)))
            ->filter()
            ->values();

        return [$parts->first(), $parts->count() > 1 ? $parts->last() : null];
    }

    public static function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    /**
     * Fallback when neither the _fbc cookie nor the current URL has the ad click (cookie cleared or
     * blocked): rebuild it from the fbclid the tracker kept when the parent landed from an ad.
     */
    private function fbcFromTracker(Request $request): ?string
    {
        if (! preg_match('/^(\d{13})\.([\w-]{10,500})$/', (string) $request->cookie('mm_fbclid'), $m)) {
            return null;
        }

        return 'fb.1.'.$m[1].'.'.$m[2];
    }

    /** The page the form was on, without its query string (the enroll link can carry a child's birthday). */
    private function sourceUrl(Request $request, string $fallback): string
    {
        $referer = (string) $request->headers->get('referer');
        $host = parse_url($referer, PHP_URL_HOST);

        if ($host && ($host === parse_url(config('app.url'), PHP_URL_HOST) || $host === $request->getHost())) {
            return strtok($referer, '?#');
        }

        return $fallback;
    }

    private function queue(string $name, string $eventId, array $user, array $params, string $url, ?CarbonInterface $at, array $links): void
    {
        $conversion = MetaConversion::create($links + [
            'event_name' => $name,
            'event_id' => $eventId,
            'fields' => array_keys($user),
            'test_event' => filled(config('services.meta.test_event_code')),
        ]);

        SendMetaConversion::dispatch($conversion->id, [
            'event_name' => $name,
            'event_time' => ($at ?? now())->getTimestamp(),
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => $url,
            'user_data' => $user,
            'custom_data' => array_filter($params, fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    /** Meta is a bonus: nothing here may ever stop a parent's booking from being saved. */
    private function safely(string $eventId, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI: could not queue event', ['event_id' => $eventId, 'error' => self::scrub($e->getMessage())]);
        }
    }
}
