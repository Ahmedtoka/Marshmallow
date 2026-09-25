# Marshmallow Child Development Center

Website, content dashboard, sales CRM and visitor analytics for Marshmallow nursery (Hadayek Al Ahram & Sheikh Zayed).

- **Stack:** Laravel 12 · Blade · Tailwind CSS 4 · Alpine.js · MySQL/MariaDB
- **Website:** `/`
- **Dashboard:** `/admin`

## Dashboard accounts (seeded)

| Role | Email | What they can do |
|---|---|---|
| Admin | admin@marshmallownursery.com | Everything: website content, settings, team, CRM, analytics |
| Sales manager | manager@marshmallownursery.com | All leads, assigning, reports, analytics |
| Sales (Hadayek) | sales.hadayek@marshmallownursery.com | Only the leads assigned to them |
| Sales (Zayed) | sales.zayed@marshmallownursery.com | Only the leads assigned to them |

Seeded password: `Marshmallow@2026`. **Change every password from Dashboard → Team & access before going live.**

## Local setup (XAMPP)

```bash
composer install
cp .env.example .env   # then set APP_ENV=local, APP_DEBUG=true, APP_URL, DB_*
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

## How classes are assigned

| Class | Age |
|---|---|
| Cupcake | 6 months – 2 years |
| Popcorn | 2 – 2.5 years |
| Candy | 2.5 – 3 years |
| Ice Cream | 3 – 3.5 years |
| Lollipop | 3.5 – 4 years |
| Cotton Candy | 4 years – school age |

- The child's age is measured on **1 October** of the school year the parent picks (if that date has passed, today's age is used).
- A child exactly on a boundary moves **up** to the older class.
- Under 6 months: the lead is saved as **waitlist**.
- Ranges, names, colors, activities and photos are all editable in Dashboard → Classes. Active classes cannot overlap.

Logic lives in `app/Support/ClassFinder.php` (server) and is mirrored in the class finder on the website.

## Photos

Upload from the dashboard — no code changes needed:
- **Classes → edit class:** class photos, and photos for each activity *inside that class* (e.g. Cupcake's gymnastics).
- **Activities → edit:** general photos for an activity.
- **Camps**, **Gallery albums**, **Homepage sections**, **Settings → About** also take images.

Large photos are resized automatically to 1920px and stored in `public/media`, which is committed, so
photos travel with the code on deploy.

When the nursery sends a folder of photos (class / activity / gallery), import it in one go instead of
uploading them one by one:

```bash
php artisan photos:import "storage/client-photos/Website"
```

Expected folder shape: `<Class>/<Activity>/*.jpg`, `<Class>/*.jpg`, `Gallery/<Album>/*.jpg`,
`Parents Reviews/*.jpg`. Folder names are matched loosely (Fine and Gross Motor Skills both feed the
"Fine & gross motor skills" activity), owners that already have photos are skipped, and `--fresh`
replaces them. Afterwards regenerate `database/seeders/PhotoSeeder.php` so the server gets the same
library on deploy.

## Visitor analytics

The website tracks visits first-party (no third-party service needed): source (Facebook, Google, WhatsApp, ads, UTM campaigns), pages, engaged time, scroll depth, calls/WhatsApp/map taps, class finder results and enrollment form starts/submits. When a parent submits a form, their whole journey appears on the lead in the CRM.

- Code: `resources/js/tracker.js` → `POST /t/collect` → `app/Http/Controllers/TrackingController.php`
- Logged-in dashboard users and bots are not tracked.
- Optional GA4 and Meta Pixel IDs can be added in Dashboard → Settings → Tracking.
- Tip: use UTM links in Facebook posts/ads, e.g. `https://yoursite.com/?utm_source=facebook&utm_medium=post&utm_campaign=admissions_2026`

## Meta Conversions API

Ad blockers, iPhones and browser tracking prevention hide many pixel events, so the key conversions are
also sent from the server:

| Event | When |
|---|---|
| `Lead` | every booking made through the website form |
| `Schedule` | the booking is a visit (`interest = tour`) |
| `SubmitApplication` | a careers application |

- **Deduplication:** each lead (and job application) gets a `meta_event_id` when it is created. The
  thank-you / careers page pixel and the server event both use it, so Meta counts the action once.
- **Customer data:** phone (Egyptian `01…` sent as `201…`), email, first and last name, city (Giza) and
  country (EG) are normalized and SHA-256 hashed before they are queued, plus IP, user agent and the
  `_fbp` / `_fbc` cookies. When the pixel could not write `_fbc`, it is built from the ad click id the
  tracker keeps in `mm_fbclid`. Raw values are never logged.
- **Credentials** live only in `.env`: `META_CAPI_TOKEN` (Events Manager → Settings → Conversions API →
  Generate access token) and, only while checking in Events Manager → Test Events, `META_TEST_EVENT_CODE`.
  The pixel id is the "Meta Pixel ID" in Dashboard → Settings → Tracking. No token = nothing is sent.
- **Delivery** is queued (`jobs` table) and sent by the queue worker the scheduler starts every minute.
  Network errors, rate limits and Meta outages retry after 1, 5, 15 and 60 minutes; a rejected event
  (bad token, bad data) fails straight away and lands in `failed_jobs` (`php artisan queue:retry all`
  resends them once fixed).
- **Health:** Dashboard → Settings → Tracking shows the last results and Meta's last error, and each lead
  shows whether its events reached Meta. Everything is also in `storage/logs/laravel.log` ("Meta CAPI").
- Code: `app/Services/MetaConversions.php`, `app/Jobs/SendMetaConversion.php`.

## Deploying to Cloudways

1. **Create the app:** a PHP (Laravel) application on PHP 8.2+ with MySQL.
2. **Point the webroot** to `public_html/public` (Application Settings → Webroot).
3. **Deploy code** via Deployment via Git (connect the GitHub repo and pull the branch).
   `public/build` is committed, so Node is not needed on the server. Run `npm run build` locally and commit before pushing whenever you change styles or scripts.
4. **Domain & SSL:** point the domain at the server in Cloudways (Domain Management), make
   `marshmallowchilddevelopmentcenter.com` the primary domain with `www` as an alias, then issue the
   Let's Encrypt certificate for **both** names and turn on Force HTTPS. The app builds every link
   from `APP_URL`, so set that to the https address.
5. **Environment:** over SSH in the app folder:
   ```bash
   cp .env.example .env         # fill in APP_URL, DB_* from Cloudways Access Details, MAIL_* for email alerts
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. **Cron job** (Cloudways → Cron Job Management), for follow-up reminders and the queue worker:
   ```
   * * * * * cd /home/master/applications/APP_ID/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```
   Paste it in the **Advanced** tab: the Basic form only accepts a PHP file name. `crontab -l` over SSH
   shows nothing even when it works. To check it runs, send the output to `storage/logs/scheduler.log`
   for a few minutes instead of `/dev/null`.
7. **After each later deploy** (pull from Git, then over SSH in the app folder):
   ```bash
   bash deploy.sh
   ```
   It puts the site in maintenance mode, installs dependencies, migrates, rebuilds caches and brings the site back up.

Repository: `git@github.com:Ahmedtoka/Marshmallow.git` (branch `main`).

### Tests

```bash
php artisan test
```
Covers every public page, the enrollment form (phone normalizing, class boundary rule, auto-assignment) and dashboard login protection. Runs on an in-memory SQLite database, so it never touches real data.

Do **not** run `db:seed` again after launch except for a specific seeder you mean to re-run. Never run the demo data seeder in production.

## Scheduled tasks (need the cron above)

| Command | When | What |
|---|---|---|
| `crm:follow-up-reminders` | every 15 min | Notifies an agent when a follow-up is due within the hour |
| `crm:follow-up-digest` | 08:00 daily | Morning notice to agents who have overdue follow-ups |
| `analytics:prune` | weekly | Deletes tracking data older than 400 days (keeps visits that became leads) |
| `queue:work --stop-when-empty` | every minute | Sends queued jobs (Meta Conversions API events), then exits |

## Demo data (local preview only)

```bash
php artisan db:seed --class=DemoDataSeeder   # 60 days of visits + ~166 leads with follow-ups
```

Remove it before launch (or simply start the live database fresh with `migrate --seed`):

```bash
php artisan tinker --execute="App\Models\Lead::withTrashed()->where('utm_content','demo')->forceDelete(); DB::table('visits')->where('ip_hash','demo')->delete(); App\Models\Visitor::whereNull('lead_id')->doesntHave('visits')->delete();"
```
