# Health journal setup

The weight app owns provider credentials, OAuth tokens, fetching and previews. The
`pacurar2020` theme owns the public post type, permissions, REST endpoints and pages.
No weight history or Notes content is written by this flow.

## Connect Oura and Withings

Set `APP_URL=https://weight.test`, `OURA_CLIENT_ID`, `OURA_CLIENT_SECRET`, and
`OURA_REDIRECT_ROUTE=/callback-oura` in the weight app's `.env`. Register exactly
`https://weight.test/callback-oura` with Oura. Open `/login-oura`, sign in and grant
`daily heartrate workout session spo2 stress heart_health`. If a scope is absent, use Reconnect beside
Oura; this replaces the cached authorization. The callback supports scope lists
returned in either the callback or token response, and the standard OAuth default
when neither specifies a reduced scope list. Oura may return `extapi:`-prefixed
permissions; these are normalized on both storage and reading existing tokens.
“Sleep, Readiness and Activity Data” is the consent label for `daily`. Reconnect
from the app to add stress and heart-health permissions. A portal example URL
without the app-generated state cannot complete the protected callback.

Withings uses its existing `/w` login. Reconnect once to add `user.activity` for
activity and sleep; older tokens still work for measurements. Unknown measurement
types are excluded rather than treated as weight. Body composition is taken from
the same measurement group as the day's minimum weight.

The app uses persistent **file cache** for tokens, previews and locks; rate-limit
counters also use file cache. This prevents parallel fetching from contending with
SQLite weight data. `storage/framework/cache` must be writable. Do not use an
`array` cache outside tests. Oura tokens are encrypted with `APP_KEY`; preserve
that key. Clearing application cache disconnects providers and expires previews.
When changing an existing installation from database cache, copy the `withings`
and encrypted `health:oura:tokens` cache keys to file cache before switching;
never print their contents. No scheduler or queue worker is needed.

## Apple Health exports

Export **JSON** from Health Auto Export, then copy the whole export folder into
`storage/health` in the weight app. No Apple login or API credential is needed.
`APPLE_HEALTH_DIRECTORY` optionally overrides this location. Personal exports and
GPX route files are ignored by Git.

Only the **most recently modified immediate subfolder** is read (filename descending
breaks ties). The UI identifies that folder. All JSON files inside it are scanned
recursively, including nested directories; older sibling folders are never combined
or used as fallback. Finish copying an export before fetching. If the newest folder
is empty or invalid, the preview reports the problem instead of selecting old data.
Non-JSON files, symbolic links and route/GPX data are ignored.

The newest export is read once when **Fetch health data** starts, and its normalized
snapshot is kept privately for the preview's 30-minute lifetime. Adding or editing
folders afterward cannot change that preview. Fetch again to use a new export.
The UI shows coverage, counts and unsupported-unit/metric warnings. Folder selection
uses filesystem modification time, not the measurement dates or folder-name format.

The current mappings cover all 40 health metric types found in the supplied export:
activity/energy/steps, running and walking metrics, body composition and exported
body mass, heart rates/HRV/VO₂ max, sleep stages/timing, oxygen/respiration/temperature,
noise exposure and handwashing. Values keep their exported aggregation; exported
body mass is labelled separately from the Withings minimum weigh-in. Each category
reads both today and yesterday on every fetch. Workouts and ECG are separate collections,
so today's step count does not hide yesterday's workouts. Older measurements remain
in the private export but are not automatically backfilled.

Workouts include allowed type labels, start/end, active duration, distance, energy,
heart rate, cadence, intensity, elevation/environment metrics where present, numerical
splits/segments/activity parts, and time-series charts. Units are checked and converted
(e.g. kJ to kcal, metres to kilometres). Unknown units are never relabelled as known
ones. Sleep follows the export's assigned date; timestamps use Europe/Bucharest and
retain daylight-saving offsets. ECG includes numerical summaries and voltage samples,
with subsecond timestamps. No device names, export IDs, arbitrary workout names,
notes, classifications, source identifiers or routes enter the public contract.

Repeated observations in multiple JSON files are replaced by the latest file's
observation, never summed. A workout's start time and allowed type identify it across
retries. Oura-origin workouts with matching start/duration use the richer Apple Health
export; duplicate Oura API workout scalars are removed. Other provider summaries
remain separate, with no totals added together across providers. Missing workouts
and provider measurements from a partial retry are retained in the reviewed entry.

Limits: 100 JSON files per selected folder, 64 MB per file, 128 MB total, 100 workouts
per daily entry and 20,000 points per series. Use grouped samples or shorter exports
if a limit is reached. Schema-invalid exports produce a visible error. Neither the
private weight history nor Notes is changed by importing or publishing.

Deploy the updated theme before publishing Apple Health data to production. It adds
the Apple Health source term and accepts the new optional structured workout section.
The existing version-1 entries remain valid.

## Connect the two blogs

Set these variables in the **weight app**, not in the theme:

```dotenv
BLOG_LOCAL_URL=https://blog.test
BLOG_LOCAL_USERNAME=your_local_publisher
BLOG_LOCAL_APPLICATION_PASSWORD="your local application password"
BLOG_PRODUCTION_URL=https://pacurar.dev
BLOG_PRODUCTION_USERNAME=your_production_publisher
BLOG_PRODUCTION_APPLICATION_PASSWORD="your production application password"
```

Application Passwords containing spaces must be quoted. Use different credentials
for each destination. HTTPS verification stays enabled; the local certificate
must be trusted by PHP as well as your browser.

On each WordPress installation, deploy/activate the updated `pacurar2020` theme.
It registers a **Health publisher** role, post type, taxonomies and endpoints once.
Create a dedicated user with that role, then open Users → that user → Application
Passwords and generate a password for the weight app. Administrators can publish
as well. Use the same publishing user on later runs: restricted publishers cannot
edit another author's entries.

If deployment caches Laravel configuration, rebuild that cache so the new
HealthJournalProvider is loaded. Visit WordPress once to apply the theme's
idempotent role/taxonomy setup and rewrite refresh. `/health/` is the public archive.
Keep this theme active: it also owns registration of the journal data.

Choose Local or Production in the weight app, then click **Test blog connection**.
This performs an authenticated read against `/wp-json/pacurar2020/v1/health-connection`
and checks permission to publish health entries. It creates no content. A 404 means
the updated theme is missing at that destination; 401/403 indicates credentials or
role permissions. Never turn off HTTPS verification to fix a connection failure.

## Preview and publish

1. Choose the destination. The app remembers your last selection in persistent
   cache, including across browser sessions. Local is used when no selection is
   saved or the cache is cleared.
2. Fetch health data. Every source collection requests **both today and yesterday**
   in Europe/Bucharest on every run. Each date is independent: existing data, empty
   results or errors for one day never prevent checking the other. Zero values count
   as data. Each source shows its outcome separately for each requested date.
   Both dates are frozen when fetching starts, even across midnight. Weight and body
   composition use that day's minimum weigh-in and the same measurement group.
   Sleep queries include the preceding night and preserve Oura's assigned day.
   Preview entries are grouped by their actual topic/date and ordered newest first.
   Publishing creates missing entries, updates existing entries and leaves identical
   entries unchanged. Run again later to fill yesterday's gaps and add today's newly
   available data; no duplicate topic/date posts are created. Missing provider data
   in a later fetch remains preserved in the reviewed entry.
3. Inspect source statuses, then expand each entry to see every numerical metric
   and chart that will be public. Unavailable collections do not block other data.
4. Select entries and click **Publish selected entries**. Only the stored preview
   is sent; credentials and raw provider responses never reach the browser/blog.
5. Follow the result links. Retry failed entries from the same preview; already
   successful entries are skipped. For a stale-revision error, fetch again.

Previews expire 30 minutes after fetching begins and are tied to the browser session,
destination and connection configuration. Changing destination discards the preview.
Repeating a publish safely updates one entry per topic/date. Absent data never deletes
previously published provider metrics; retained measurements are listed in the preview.
No backfill, automatic publishing, location data, provider notes or raw JSON downloads
are included. Apple Health ECG numerical samples can appear as charts and tables
when the recording belongs to today or yesterday; classifications are excluded. Account, ring and device identifiers are excluded.

## Contract and tests

The version-1 payload contains `topic`, `date`, `timezone`, `providers`,
`schema_version`, and `expected_revision`. Provider sections contain `fetched_at`,
`metrics`, `series` and optional `workouts`; workout types/origins are allowlisted and
all nested numerical fields use the same validation. Recognized keys, labels and units are defined in
`app/Health/metrics.json`. Keep this catalog, `MetricCatalog.php` and
`EntryContract.php` identical between the two repositories. Incoming free-text
labels are replaced with catalog labels. The public REST field is `health_data` at
`/wp-json/wp/v2/health-entries`; updates use the authenticated custom endpoint.

Run the weight app tests with `./vendor/bin/phpunit`, then `npm run build`.
The theme has a standalone isolated WordPress integration harness:
`php tests/health/integration.php`. It creates and drops only a fresh
`health_journal_test_*` database and requires permission to create that test database.
It tests REST permissions, schemas, taxonomy, partial updates, stale revisions and
three concurrent creates. It uses no real health data or production API.

For an **explicitly local** visual smoke test from the weight app:

```sh
php tests/health/local-blog-smoke.php seed
# Inspect https://blog.test/health/ and the synthetic 2001-01-03 entries.
php tests/health/local-blog-smoke.php cleanup
```

This opt-in script refuses production and existing entries on the fixture date.
It requires local credentials with permission to delete its own synthetic fixtures
(e.g. a local administrator). It records created IDs and checks their identities
before cleanup. Normal publishing uses the restricted role and needs no delete access.

Production deployment and publishing require a separate deliberate user action.
