/**
 * Marshmallow first-party visitor tracker.
 *
 * Cookies:  mm_vid – visitor id (2 years)   mm_sid – visit id (renewed on activity, expires after 30 idle minutes)
 *           mm_fbclid – last Facebook ad click, for the Meta Conversions API (90 days)
 * Sends:    pageview on load, engagement pings (visible time + scroll depth), and events.
 *
 * Automatic events: call_click, whatsapp_click, map_click, email_click, outbound_click,
 *                   section_view ([data-track-section]), form_start ([data-track-form]).
 * Manual:   data-track="cta_click" data-track-label="Hero – Book a visit" on any element,
 *           or window.mmTrack('class_finder', 'Cupcake', { months: 14 }).
 */
const endpoint = document.querySelector('meta[name="mm-track"]')?.content;
const VISIT_MINUTES = 30;

const uuid = () =>
    crypto.randomUUID
        ? crypto.randomUUID()
        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
              const r = (Math.random() * 16) | 0;
              return (c === 'x' ? r : (r & 0x3) | 0x8).toString(16);
          });

const getCookie = (name) => document.cookie.split('; ').find((c) => c.startsWith(name + '='))?.split('=')[1];
const setCookie = (name, value, seconds) => {
    document.cookie = `${name}=${value}; max-age=${seconds}; path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
};

// Keep the Facebook ad click id so the server can still match a booking to the ad when the pixel is blocked
// and never wrote its own _fbc cookie. Stored as "<ms>.<fbclid>"; the latest ad click wins.
(() => {
    const fbclid = new URLSearchParams(location.search).get('fbclid');
    if (fbclid && /^[\w-]{10,500}$/.test(fbclid)) setCookie('mm_fbclid', `${Date.now()}.${fbclid}`, 60 * 60 * 24 * 90);
})();

function ids() {
    let vid = getCookie('mm_vid');
    if (!vid) vid = uuid();
    setCookie('mm_vid', vid, 60 * 60 * 24 * 730);

    let sid = getCookie('mm_sid');
    if (!sid) sid = uuid();
    setCookie('mm_sid', sid, VISIT_MINUTES * 60);

    return { vid, sid };
}

function send(payload, beacon = false) {
    if (!endpoint) return Promise.resolve(null);
    const body = JSON.stringify({ ...ids(), ...payload });

    if (beacon && navigator.sendBeacon) {
        navigator.sendBeacon(endpoint, new Blob([body], { type: 'application/json' }));
        return Promise.resolve(null);
    }

    return fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body,
        keepalive: true,
        credentials: 'same-origin',
    })
        .then((r) => (r.ok ? r.json() : null))
        .catch(() => null);
}

if (endpoint) {
    const params = new URLSearchParams(location.search);
    const utm = {};
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'].forEach((k) => {
        if (params.get(k)) utm[k] = params.get(k).slice(0, 190);
    });
    const clickId = params.has('gclid') ? 'gclid' : params.has('fbclid') ? 'fbclid' : null;

    let pageViewId = null;
    const queue = [];

    const pageview = send({
        type: 'pageview',
        path: location.pathname,
        title: document.title.slice(0, 190),
        query: location.search.slice(0, 190),
        referrer: document.referrer,
        screen: `${screen.width}x${screen.height}`,
        lang: navigator.language,
        utm,
        click_id: clickId,
    }).then((res) => {
        pageViewId = res?.pv ?? null;
        queue.splice(0).forEach((e) => send({ ...e, pv: pageViewId }));
    });

    // ---- Engagement: count only time the tab is actually visible ----
    let visibleMs = 0;
    let visibleSince = document.visibilityState === 'visible' ? Date.now() : null;
    let maxScroll = 0;

    const engagedSeconds = () => Math.round((visibleMs + (visibleSince ? Date.now() - visibleSince : 0)) / 1000);

    const measureScroll = () => {
        const doc = document.documentElement;
        const scrollable = doc.scrollHeight - window.innerHeight;
        const pct = scrollable <= 0 ? 100 : Math.round((window.scrollY / scrollable) * 100);
        maxScroll = Math.max(maxScroll, Math.min(100, pct));
    };
    window.addEventListener('scroll', measureScroll, { passive: true });
    measureScroll();

    const ping = (beacon = false) => {
        if (!pageViewId) return;
        send({ type: 'ping', pv: pageViewId, duration: engagedSeconds(), scroll: maxScroll }, beacon);
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            if (visibleSince) visibleMs += Date.now() - visibleSince;
            visibleSince = null;
            ping(true);
        } else {
            visibleSince = Date.now();
        }
    });
    window.addEventListener('pagehide', () => ping(true));
    setInterval(() => document.visibilityState === 'visible' && ping(), 15000);

    /* ---- Events ----
     * Each action is recorded in our own dashboard and, when the nursery has a Meta pixel or GA4
     * configured, forwarded there too: phone and WhatsApp taps as Contact, the class finder and the
     * rest as clearly named custom events. Nothing is sent when neither tool is set up.
     */
    const META = {
        call_click: ['Contact', true, (label) => ({ contact_method: 'phone', branch: branchOf(label) })],
        whatsapp_click: ['Contact', true, (label) => ({ contact_method: 'whatsapp', branch: branchOf(label) })],
        email_click: ['Contact', true, () => ({ contact_method: 'email' })],
        map_click: ['FindLocation', true, (label) => ({ branch: branchOf(label) })],
        class_finder: ['FindClass', false, (label, props) => ({ class_name: label, months: props && props.months })],
        form_start: ['StartForm', false, (label) => ({ form: label })],
        gallery_open: ['ViewGallery', false, (label) => ({ album: label })],
        faq_open: ['ViewFaq', false, (label) => ({ question: label })],
        cta_click: ['ClickCTA', false, (label) => ({ cta: label })],
    };

    // "Call Hadayek Al Ahram" is our own button label; Meta only needs the branch.
    const branchOf = (label) => String(label || '').replace(/^(call|whatsapp|map|directions)\s+/i, '').trim();

    const forward = (name, label, props) => {
        const mapping = META[name];
        if (!mapping) return;

        const [event, standard, params] = mapping;
        const payload = params ? params(label, props) : {};

        if (window.fbq) window.fbq(standard ? 'track' : 'trackCustom', event, payload);
        if (window.gtag) window.gtag('event', event, payload);
    };

    const track = (name, label = null, props = null, value = null) => {
        forward(name, label, props);

        const event = { type: 'event', name, label: label ? String(label).slice(0, 190) : null, value, props, path: location.pathname };
        if (pageViewId) return send({ ...event, pv: pageViewId });
        queue.push(event);
        return pageview;
    };
    window.mmTrack = track;

    const labelOf = (el) => el.dataset.trackLabel || el.getAttribute('aria-label') || el.textContent.trim().replace(/\s+/g, ' ').slice(0, 120);

    document.addEventListener(
        'click',
        (e) => {
            const el = e.target.closest('a, button, [data-track]');
            if (!el) return;

            if (el.dataset.track) {
                track(el.dataset.track, labelOf(el), el.dataset.trackProps ? JSON.parse(el.dataset.trackProps) : null);
                if (el.tagName !== 'A') return;
            }

            const href = el.getAttribute('href') || '';
            if (!href || el.dataset.track) return;
            if (href.startsWith('tel:')) return track('call_click', labelOf(el), { number: href.slice(4) });
            if (href.startsWith('mailto:')) return track('email_click', labelOf(el));
            if (/wa\.me|whatsapp\.com/.test(href)) return track('whatsapp_click', labelOf(el));
            if (/maps\.app\.goo\.gl|google\.[a-z.]+\/maps|goo\.gl\/maps/.test(href)) return track('map_click', labelOf(el));
            try {
                const url = new URL(href, location.href);
                if (url.host !== location.host) track('outbound_click', url.host, { url: url.href.slice(0, 300) });
            } catch (_) {}
        },
        { capture: true },
    );

    if ('IntersectionObserver' in window) {
        const seen = new Set();
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    const name = entry.target.dataset.trackSection;
                    if (entry.isIntersecting && !seen.has(name)) {
                        seen.add(name);
                        track('section_view', name);
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.4 },
        );
        document.querySelectorAll('[data-track-section]').forEach((el) => observer.observe(el));
    }

    document.querySelectorAll('form[data-track-form]').forEach((form) => {
        let started = false;
        form.addEventListener('focusin', () => {
            if (started) return;
            started = true;
            track('form_start', form.dataset.trackForm);
        });
    });
}
