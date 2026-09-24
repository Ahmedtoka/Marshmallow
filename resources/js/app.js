import Alpine from 'alpinejs';
import './tracker';

/* ------------------------------------------------------------------
 * Class finder — mirrors App\Support\ClassFinder exactly:
 *   reference date = 1 October of the school year's start year, or today if that has passed;
 *   months = whole months between birthday and reference date;
 *   class = first class where months >= min && (max === null || months < max).
 * ------------------------------------------------------------------ */
const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const pad = (n) => String(n).padStart(2, '0');

const parseYmd = (value) => {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
    return m ? { y: +m[1], m: +m[2], d: +m[3] } : null;
};
const todayParts = () => {
    const t = new Date();
    return { y: t.getFullYear(), m: t.getMonth() + 1, d: t.getDate() };
};
const compare = (a, b) => a.y - b.y || a.m - b.m || a.d - b.d;
const toYmd = (p) => `${p.y}-${pad(p.m)}-${pad(p.d)}`;
const addDays = (p, days) => {
    const d = new Date(p.y, p.m - 1, p.d + days);
    return { y: d.getFullYear(), m: d.getMonth() + 1, d: d.getDate() };
};

export const monthsBetween = (dob, ref) => {
    let months = (ref.y - dob.y) * 12 + (ref.m - dob.m);
    if (ref.d < dob.d) months--;
    return Math.max(0, months);
};

export const referenceDate = (year, cutoff = { month: 10, day: 1 }) => {
    const startYear = parseInt(String(year).slice(0, 4), 10) || new Date().getFullYear();
    const date = { y: startYear, m: cutoff.month, d: cutoff.day };
    const today = todayParts();
    return compare(date, today) < 0 ? today : date;
};

export const ageText = (months) => {
    const years = Math.floor(months / 12);
    const rest = months % 12;
    const parts = [];
    if (years) parts.push(`${years} ${years === 1 ? 'year' : 'years'}`);
    if (rest || !years) parts.push(`${rest} ${rest === 1 ? 'month' : 'months'}`);
    return parts.join(' ');
};

const dateText = (p) => `${p.d} ${MONTH_NAMES[p.m - 1]} ${p.y}`;

Alpine.data('classFinder', (config, options = {}) => ({
    dob: options.dob || '',
    year: options.year && config.years.includes(options.year) ? options.year : config.years[0],
    result: null,
    error: '',
    maxDate: toYmd(todayParts()),
    lastTracked: null,

    init() {
        if (this.dob) this.compute(false);
    },

    compute(track = true) {
        const dob = parseYmd(this.dob);
        this.error = '';
        if (!dob) {
            this.result = null;
            return;
        }
        if (compare(dob, todayParts()) > 0) {
            this.result = null;
            this.error = 'That birthday is in the future. Please check the date.';
            return;
        }

        const ref = referenceDate(this.year, config.cutoff);
        const months = monthsBetween(dob, ref);
        const classes = [...config.classes].sort((a, b) => a.min - b.min);
        const match = classes.find((c) => months >= c.min && (c.max === null || months < c.max)) || null;
        const youngest = classes[0] || null;
        const status = match ? 'match' : youngest && months < youngest.min ? 'too_young' : 'too_old';
        const isToday = compare(ref, todayParts()) === 0;

        const query = new URLSearchParams({ dob: this.dob, year: this.year });
        if (match) query.set('class', match.slug);
        if (status === 'too_young') query.set('interest', 'waitlist');

        this.result = {
            status,
            months,
            classroom: match,
            next: status === 'too_young' ? youngest : null,
            age: ageText(months),
            on: isToday ? 'today' : `on ${dateText(ref)}`,
            isToday,
            enrollUrl: `${config.enrollUrl}?${query.toString()}`,
            label: match ? match.name : status === 'too_young' ? 'Too young' : 'Too old',
        };

        const key = `${this.result.label}|${months}|${this.year}`;
        if (track && options.track !== false && key !== this.lastTracked && typeof window.mmTrack === 'function') {
            this.lastTracked = key;
            window.mmTrack('class_finder', this.result.label, { months, year: this.year });
        }
    },
}));

/* ------------------------------------------------------------------
 * Accessible photo lightbox. Items are read from [data-lightbox-item] children.
 * ------------------------------------------------------------------ */
Alpine.data('lightbox', (title = '') => ({
    open: false,
    index: 0,
    items: [],
    returnFocus: null,

    init() {
        this.items = [...this.$el.querySelectorAll('[data-lightbox-item]')].map((el) => ({
            src: el.dataset.src,
            alt: el.dataset.alt || '',
            caption: el.dataset.caption || '',
        }));
    },

    get current() {
        return this.items[this.index] || {};
    },

    show(i) {
        if (!this.items.length) return;
        this.returnFocus = document.activeElement;
        this.index = i;
        this.open = true;
        document.documentElement.style.overflow = 'hidden';
        if (typeof window.mmTrack === 'function') window.mmTrack('gallery_open', title, { photo: i + 1 });
        this.$nextTick(() => this.$refs.close?.focus());
    },

    close() {
        if (!this.open) return;
        this.open = false;
        document.documentElement.style.overflow = '';
        this.$nextTick(() => this.returnFocus?.focus());
    },

    next() {
        this.index = (this.index + 1) % this.items.length;
    },

    prev() {
        this.index = (this.index - 1 + this.items.length) % this.items.length;
    },

    keydown(e) {
        if (!this.open) return;
        if (e.key === 'Escape') this.close();
        else if (e.key === 'ArrowRight') this.next();
        else if (e.key === 'ArrowLeft') this.prev();
        else if (e.key === 'Tab') {
            const focusables = [...this.$refs.dialog.querySelectorAll('button')];
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    },
}));

/* ------------------------------------------------------------------
 * Enrollment form helpers (camp field, WhatsApp toggle, tour day check)
 * ------------------------------------------------------------------ */
Alpine.data('enrollForm', (initial = {}) => ({
    interest: initial.interest || 'enrollment',
    same: initial.same !== false,
    tourDate: initial.tourDate || '',
    minTour: toYmd(addDays(todayParts(), 1)),

    get tourWeekend() {
        const d = parseYmd(this.tourDate);
        if (!d) return false;
        const day = new Date(d.y, d.m - 1, d.d).getDay();
        return day === 5 || day === 6;
    },
}));


/* ------------------------------------------------------------------
 * Hero photo wall. Every few seconds one tile changes: the odd tiles
 * slide the new photo up, the even ones fade it in, so the wall is alive
 * without anything moving all at once. Paused when the tab is hidden and
 * for anyone who asked for reduced motion.
 * ------------------------------------------------------------------ */
Alpine.data('heroMosaic', (photos, count) => ({
    photos,
    count,
    current: [],
    next: [],
    swapping: [],
    cursor: 0,
    timer: null,

    init() {
        this.current = Array.from({ length: count }, (_, i) => photos[i % photos.length]);
        this.next = [...this.current];
        this.swapping = Array(count).fill(false);
        this.cursor = count;

        if (photos.length <= count || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        this.start();
        document.addEventListener('visibilitychange', () => (document.hidden ? this.stop() : this.start()));
    },

    start() {
        this.stop();
        this.timer = setInterval(() => this.swap(), 2600);
    },

    stop() {
        clearInterval(this.timer);
        this.timer = null;
    },

    swap() {
        const tile = Math.floor(Math.random() * this.count);
        if (this.swapping[tile]) return;

        const photo = this.photos[this.cursor % this.photos.length];
        this.cursor += 1;

        // Never show the same photo twice on the wall at the same moment.
        if (this.current.includes(photo)) return;

        this.next[tile] = photo;
        this.swapping[tile] = true;

        setTimeout(() => {
            this.current[tile] = photo;
            this.swapping[tile] = false;
        }, 700);
    },
}));

window.Alpine = Alpine;
Alpine.start();
