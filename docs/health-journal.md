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

1. Choose the destination (Local by default).
2. Fetch health data. Every source collection first requests today's Bucharest
   date. Only an empty result (no supported numerical measurements) triggers a
   second request for yesterday. Permission and request errors are reported without
   falling back. Zero values count as data. A partial result for today stays today;
   individual missing metrics are not filled with yesterday's values. Weight and
   body composition remain tied to the same minimum weigh-in. Both target dates
   are frozen when fetching starts, even if the process crosses midnight.
   Each source row shows all dates checked and the actual data date. Fallback
   entries retain yesterday's date and upsert yesterday's topic entry; they are
   never relabelled as today's data. Sources using different dates remain separate
   entries. Sleep queries include the preceding night and retain only Oura's
   assigned target day.
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
No backfill, automatic publishing, location data, provider notes, raw ECG waveforms or
raw JSON downloads are included. Account, ring and device identifiers are excluded.

## Contract and tests

The version-1 payload contains `topic`, `date`, `timezone`, `providers`,
`schema_version`, and `expected_revision`. Provider sections contain `fetched_at`,
`metrics` and `series`; recognized keys, labels and units are defined in
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
