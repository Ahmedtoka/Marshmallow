<?php

namespace App\Support;

use FacebookAds\ParamBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Meta's Parameter Builder, run once per request.
 *
 * It reads (and when needed creates) the _fbp browser id and the _fbc ad-click id, and picks the best
 * client IP, all in the exact format Meta expects. The middleware writes its cookies from the server
 * so they outlive Safari's 7-day limit on cookies set by JavaScript; the Conversions API reads the
 * same values for its events.
 */
class MetaParams
{
    private const ATTRIBUTE = 'meta_params';

    private const VISITOR_ATTRIBUTE = 'meta_visitor_id';

    public static function for(Request $request): ParamBuilder
    {
        if ($builder = $request->attributes->get(self::ATTRIBUTE)) {
            return $builder;
        }

        // Our own domain, so the cookies land on marshmallowchilddevelopmentcenter.com and cover www too.
        $domain = parse_url((string) config('app.url'), PHP_URL_HOST);
        $builder = new ParamBuilder($domain ? [$domain] : null);

        $builder->processRequest(
            $request->getHost(),
            $request->query(),
            $request->cookies->all(),
            $request->headers->get('referer'),
            $request->headers->get('x-forwarded-for'),
            $request->ip(),
        );

        $request->attributes->set(self::ATTRIBUTE, $builder);

        return $builder;
    }

    /**
     * The tracker's visitor id (mm_vid), created here when the browser has none yet so the very first
     * page already carries it. It is the external_id both the pixel and the server send to Meta.
     */
    public static function visitorId(Request $request): string
    {
        if ($id = $request->attributes->get(self::VISITOR_ATTRIBUTE)) {
            return $id;
        }

        $id = (string) $request->cookie('mm_vid');
        if (! Str::isUuid($id)) {
            $id = (string) Str::uuid();
        }
        $request->attributes->set(self::VISITOR_ATTRIBUTE, $id);

        return $id;
    }

    /** SHA-256 of the lowercased visitor id, identical in the browser and the server. */
    public static function externalId(Request $request): string
    {
        return hash('sha256', strtolower(self::visitorId($request)));
    }
}
