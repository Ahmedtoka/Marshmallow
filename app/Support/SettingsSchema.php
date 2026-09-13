<?php

namespace App\Support;

/**
 * Every site setting the dashboard can edit, grouped into tabs.
 * The public website reads these keys through setting('key').
 *
 * Field types: text, textarea, toggle, image, number, url, email, tel, code.
 */
class SettingsSchema
{
    public static function groups(): array
    {
        return [
            'general' => [
                'label' => 'General',
                'icon' => 'gear',
                'description' => 'Your nursery’s name, tagline and the images used across the website.',
                'fields' => [
                    self::field('site_name', 'Full name', 'text', 'Shown in the footer, page titles and search results.'),
                    self::field('short_name', 'Short name', 'text', 'Used where space is tight, like the mobile header and buttons.'),
                    self::field('tagline', 'Tagline', 'text', 'The short line shown next to the logo and in the footer.'),
                    self::field('footer_about', 'Footer description', 'textarea', 'The paragraph about the nursery at the bottom of every page.'),
                    self::field('hashtags', 'Hashtags', 'text', 'Shown in the footer. Separate them with spaces.'),
                    self::field('logo_path', 'Logo', 'image', 'Square logo, at least 300 × 300 px. If empty, the website uses the original Marshmallow logo.'),
                    self::field('default_og_image', 'Default sharing image', 'image', 'Shown when a page is shared on WhatsApp or Facebook and has no image of its own. 1200 × 630 px works best.'),
                ],
            ],
            'contact' => [
                'label' => 'Contact & social',
                'icon' => 'phone',
                'description' => 'The main phone, WhatsApp and social links used in the header, footer and contact buttons. Branch numbers are edited under Branches.',
                'fields' => [
                    self::field('main_phone', 'Main phone number', 'tel', 'Used for the “Call us” buttons across the website.'),
                    self::field('whatsapp_number', 'WhatsApp number', 'tel', 'Local format is fine, e.g. 01012666625. Used for the floating WhatsApp button.'),
                    self::field('email', 'Email address', 'email', 'Shown in the footer and on the branches page.'),
                    self::field('facebook_url', 'Facebook page', 'url'),
                    self::field('messenger_url', 'Messenger link', 'url', 'Usually https://m.me/YourPageName'),
                    self::field('instagram_url', 'Instagram', 'url', 'Leave empty to hide the icon.'),
                    self::field('tiktok_url', 'TikTok', 'url', 'Leave empty to hide the icon.'),
                    self::field('youtube_url', 'YouTube', 'url', 'Leave empty to hide the icon.'),
                ],
            ],
            'hours' => [
                'label' => 'Hours & school year',
                'icon' => 'clock',
                'description' => 'Opening times and school-year notes shown in the footer, on the safety page and in the FAQ area.',
                'fields' => [
                    self::field('working_days', 'Working days', 'text', 'e.g. Sunday – Thursday'),
                    self::field('working_hours', 'Working hours', 'text', 'e.g. 7:00 am – 4:00 pm'),
                    self::field('weekend', 'Closed on', 'text', 'e.g. Friday & Saturday'),
                    self::field('after_school', 'After-school care', 'text'),
                    self::field('tour_hours', 'Visit / camera tour hours', 'text', 'Shown on the enrollment page next to the visit request.'),
                    self::field('academic_year_note', 'School year note', 'text'),
                    self::field('teaching_language', 'Teaching language', 'text'),
                ],
            ],
            'admissions' => [
                'label' => 'Admissions',
                'icon' => 'calendar',
                'description' => 'Control whether admissions are open, the school years parents can apply for and the announcement bar.',
                'fields' => [
                    self::field('admissions_open', 'Admissions are open', 'toggle', 'When off, the website invites parents to join the waiting list instead.'),
                    self::field('admissions_label', 'Admissions label', 'text', 'The small badge shown in the hero, e.g. “Admissions open for 2026–2027”.'),
                    self::field('admission_years', 'School years parents can apply for', 'text', 'Comma separated, e.g. 2026-2027,2027-2028. Used by the class finder and the enrollment form.', [
                        'placeholder' => '2026-2027,2027-2028',
                    ]),
                    self::field('announcement', 'Announcement bar text', 'text', 'The thin bar at the very top of every page.'),
                    self::field('announcement_visible', 'Show the announcement bar', 'toggle'),
                    self::field('thank_you_message', 'Thank-you message', 'textarea', 'Shown to parents after they send the enrollment or visit form.'),
                ],
            ],
            'stats' => [
                'label' => 'Numbers',
                'icon' => 'chart',
                'description' => 'The proud numbers shown on the homepage and about page.',
                'fields' => [
                    self::field('years_experience', 'Years of experience', 'number', 'e.g. 14'),
                    self::field('recommend_percent', 'Parents who recommend us (%)', 'number', 'From your Facebook reviews, e.g. 96'),
                    self::field('reviews_count', 'Number of reviews', 'number', 'e.g. 329'),
                    self::field('followers', 'Followers', 'text', 'Free text, e.g. 50K+'),
                ],
            ],
            'about' => [
                'label' => 'About page',
                'icon' => 'heart',
                'description' => 'The story and philosophy shown on the About page.',
                'fields' => [
                    self::field('about_title', 'Page heading', 'text'),
                    self::field('about_intro', 'Introduction', 'text', 'One or two sentences under the heading.'),
                    self::field('about_story', 'Our story', 'textarea', 'Leave an empty line between paragraphs.', ['rows' => 7]),
                    self::field('about_philosophy', 'Our philosophy', 'textarea', 'Leave an empty line between paragraphs.', ['rows' => 7]),
                    self::field('about_image', 'About page photo', 'image', 'A warm photo of your team or classrooms. Landscape, at least 1200 px wide.'),
                ],
            ],
            'tracking' => [
                'label' => 'Tracking',
                'icon' => 'trending',
                'description' => 'Connect Google Analytics and Meta (Facebook) Pixel. The built-in dashboard analytics work without these.',
                'fields' => [
                    self::field('ga4_id', 'Google Analytics 4 measurement ID', 'text', 'Looks like G-XXXXXXX. Leave empty to turn off.', ['placeholder' => 'G-XXXXXXX']),
                    self::field('meta_pixel_id', 'Meta Pixel ID', 'text', 'Numbers only. Leave empty to turn off.'),
                    self::field('head_scripts', 'Extra code in <head>', 'code', 'Paste verification tags or chat widgets here.', [
                        'warning' => 'This code is injected into the <head> of every page on the website. A mistake here can break the whole site — only paste code from a trusted provider.',
                    ]),
                ],
            ],
            'crm' => [
                'label' => 'Leads',
                'icon' => 'users',
                'description' => 'How new enquiries from the website reach your sales team.',
                'fields' => [
                    self::field('lead_auto_assign', 'Assign new leads automatically', 'toggle', 'New website leads go to the sales agent of that branch with the fewest open leads.'),
                    self::field('lead_email_alerts', 'Email alerts for new leads', 'toggle', 'Send an email to the address below every time a parent fills in a form.'),
                    self::field('lead_notify_email', 'Alert email address', 'email', 'Where new lead alerts are sent.'),
                ],
            ],
        ];
    }

    public static function has(?string $group): bool
    {
        return $group !== null && array_key_exists($group, self::groups());
    }

    public static function group(string $group): array
    {
        return self::groups()[$group];
    }

    /** @return list<array{key: string, label: string, type: string, hint: ?string}> */
    public static function fields(string $group): array
    {
        return self::group($group)['fields'];
    }

    /** Laravel validation rules for one group. */
    public static function rules(string $group): array
    {
        $rules = [];

        foreach (self::fields($group) as $field) {
            $key = $field['key'];

            $rules[$key] = match ($field['type']) {
                'toggle' => ['nullable', 'boolean'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'number' => ['nullable', 'integer', 'min:0', 'max:1000000'],
                'url' => ['nullable', 'url', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'tel' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s+\-()]+$/'],
                'textarea' => ['nullable', 'string', 'max:10000'],
                'code' => ['nullable', 'string', 'max:50000'],
                default => ['nullable', 'string', 'max:255'],
            };

            if ($field['type'] === 'image') {
                $rules['remove_'.$key] = ['nullable', 'boolean'];
            }
        }

        // Field-specific rules.
        $extra = [
            'site_name' => ['required'],
            'recommend_percent' => ['max:100'],
            'ga4_id' => ['regex:/^G-[A-Z0-9]{4,20}$/i'],
            'meta_pixel_id' => ['regex:/^\d{5,20}$/'],
            'admission_years' => [function (string $attribute, mixed $value, \Closure $fail) {
                foreach (self::splitYears((string) $value) as $year) {
                    if (! preg_match('/^(\d{4})-(\d{4})$/', $year, $m) || (int) $m[2] !== (int) $m[1] + 1) {
                        $fail("“{$year}” is not a valid school year. Use the format 2026-2027 (two consecutive years).");

                        return;
                    }
                }
            }],
        ];

        foreach ($extra as $key => $more) {
            if (isset($rules[$key])) {
                $rules[$key] = $key === 'site_name'
                    ? array_merge(['required'], array_diff($rules[$key], ['nullable']))
                    : array_merge($rules[$key], $more);
            }
        }

        return $rules;
    }

    /** "2026-2027, 2027-2028" → ['2026-2027', '2027-2028'] */
    public static function splitYears(string $value): array
    {
        return array_values(array_filter(array_map(
            fn ($y) => str_replace(['–', '—', ' '], ['-', '-', ''], trim($y)),
            explode(',', $value)
        )));
    }

    private static function field(string $key, string $label, string $type = 'text', ?string $hint = null, array $extra = []): array
    {
        return ['key' => $key, 'label' => $label, 'type' => $type, 'hint' => $hint] + $extra;
    }
}
