# Public health analytics API

The active `pacurar2020` theme registers two anonymous, read-only endpoints:

```sh
curl --fail-with-body 'https://pacurar.dev/wp-json/health/v1/timeline?from=2026-09-01&to=2026-09-03'
curl --fail-with-body 'https://pacurar.dev/wp-json/health/v1/timeline?days=30'
curl --fail-with-body 'https://pacurar.dev/wp-json/health/v1/schema'
```

Use `https://blog.test` for local development. Production URLs require deployment
of the updated theme. No credentials, provider requests or publishing are involved.

## Dates and public scope

`from` and `to` must both be valid `YYYY-MM-DD` dates; both are inclusive. Without
them, `days` defaults to 30 calendar days ending today in the configured WordPress
timezone (currently Europe/Bucharest). Do not combine the two forms. The maximum
range is 365 inclusive days. Invalid, reversed, partial or excessive ranges return
HTTP 400. Unsupported query parameters are rejected; standard WordPress `_fields`
and `rest_route` are allowed. There is no pagination: one request returns the range.

There is exactly one object for every calendar day in ascending order, including
days without measurements. Dates come from published measurement dates, not post
creation/import time. Sleep keeps its assigned date even when bedtime was the
previous evening. Only published, non-password-protected `health_entry` records
are eligible, even when the caller is an administrator. Making a record private
or correcting it is reflected by the next request.

Every day contains `body`, `activity`, `heart`, `sleep`, `recovery`, `vitals`,
`mindfulness` objects and a `workouts` array. Empty groups are `{}`; missing
measurements/providers are omitted. Zero always comes from a stored zero.

## Values and provenance

Common measurements have explicit field names and conversions (`weight_kg`,
`active_energy_kcal`, `total_sleep_minutes`, etc.). Additional catalogued scalar
metrics retain dataset-specific names documented in `/schema`. The schema lists
each field's unit, source key, source dataset, conversion factor, description,
possible providers, shape and provenance. Provider keys are `apple`, `oura`,
`withings`. No provider is preferred, averaged with another, or substituted.

Point measurements, including weight and body composition, use:

```json
{"weight_kg":{"withings":{"value":71.61,"measured_at":"2026-09-15T08:14:23+03:00"}}}
```

Daily aggregates use numbers. Clock-time fields such as `bedtime` use ISO 8601
strings. If the same provider has multiple observations of the same field on one
day, its value is an array of `{value, measured_at}` objects for point readings,
or `{value, source_timestamp}` for daily/session readings. Nothing is averaged,
summed or silently selected. Missing timestamps remain null.

Timestamps come from the stored source observation, never `fetched_at`. They are
converted to the configured WordPress timezone with their DST offset preserved.
An Apple daily export can use midnight as its bucket timestamp; this is not proof
of an actual midnight weigh-in. The API cannot recover precision discarded by an
export or importer. Weight still reflects the measurements originally published
by the importer, not every private Withings weigh-in.

Values receive only documented unit conversions (seconds/hours to minutes, metres
to kilometres, sleep-efficiency ratio to percent). Questionable values are neither
corrected nor clamped. Walking/running distance, cycling distance and Oura's
walking-equivalent distance remain distinct; score contributors are not sleep
stage durations. No BMI, cardiovascular-age delta, correlations, moving averages,
medical interpretation, new scores or statistics are calculated.

Provenance is conservative: `measured` for Withings weight, `provider_estimated`
for recognized provider body estimates/scores/energy, and `unspecified` when the
stored contract does not establish the method. Apple data does not retain device
or derivation metadata, so it remains unspecified. This is descriptive metadata,
not a quality ranking. Unit conversion alone does not make a measurement calculated.

## Workouts

Individual workouts retain provider, allowed type, origin, start/end and all
catalogued scalar session metrics, with numerical durations/distances/energy/HR.
Structured Apple workouts preserve their Oura origin when present. Provider
identifies the exporting source, while origin identifies the stored source.

Native Oura/Withings imports can contain only timestamped scalar metrics. Those
are joined across topics on exact provider and start timestamp; unavailable type
and end remain null. The API does not infer an end from active duration or borrow
a type from another provider. Repeated session fields remain arrays; split and
segment values are not reduced to totals. A real-world workout may appear under
multiple providers; clients must not blindly add them together.

The compact export omits raw sample series (including ECG waveforms) and does not
derive means or peaks from them. Stored scalar HR averages/minima/maxima remain
available. Arbitrary text, notes, credentials, account/device identifiers, locations
and routes are never serialized. The output uses the theme's numerical catalog
as its allowlist, not raw provider JSON.

## Architecture and caching

Files added or changed for this feature:

| File | Responsibility |
| --- | --- |
| `app/Health/TimelineApi.php` | Range validation and batched public reads |
| `app/Health/TimelineNormalizer.php` | Daily objects, timestamps and individual workouts |
| `app/Health/AnalyticsCatalog.php` | Field mappings, unit conversions and schema |
| `app/Health/AnalyticsNoCache.php` | W3TC exclusions and no-store headers |
| `app/Providers/HealthJournalProvider.php` | Public GET route registration |
| `tests/health/timeline.php` | Analytics integration tests |
| `tests/health/integration.php` | Test suite registration |
| `docs/health-analytics-api.md` | API contract and operating notes |
| `docs/health-journal.md` | Link from the journal setup guide |
| `docs/chatgpt-health-project-instructions.md` | Copy-ready ChatGPT project instructions |

`AnalyticsCatalog` owns field names, units and schema metadata. `TimelineNormalizer`
combines topics into days and preserves individual observations/workouts.
`TimelineApi` validates ranges and reads published metadata directly in keyset
batches of eight entries. This avoids N+1 WordPress post/meta lookups while bounding
memory for historical entries containing large ECG payloads. Raw entries are not
loaded into WordPress's object cache. The integration suite checks nine topic
entries require two data queries; a larger period uses one query per batch rather
than one query per post or metric. No database migration or derived export writes
occur on GET requests.

**Neither endpoint is response-cached.** `AnalyticsNoCache` sets W3TC's
`DONOTCACHEPAGE`, `DONOTCACHEDB` and `DONOTCACHEOBJECT` flags for analytics requests,
adds scoped page-cache reject patterns (including `?rest_route=` URLs), and rejects
page-cache writes through `w3tc_can_cache`. Both endpoints, including validation
errors, return:

```http
Cache-Control: no-store, no-cache, must-revalidate, max-age=0
CDN-Cache-Control: no-store
Cloudflare-CDN-Cache-Control: no-store
Pragma: no-cache
```

No transients, response cache or TTL are used. Other health pages retain their
normal cache policy. These are theme-level exclusions; if a server/CDN rule is
configured to override origin `no-store`, exclude `/wp-json/health/v1/*` there too.
An object served before WordPress loads cannot be changed by a theme callback;
purge any pre-existing copies when deploying over an older cached implementation.

## Validation

Run `php tests/health/integration.php` from the theme. The suite creates and drops
an isolated database and uses synthetic records. Analytics checks cover inclusive
date ranges, ordering, multiple providers, missing/zero data, numeric JSON,
source timestamps and repeated readings, workout serialization, privacy, invalid
and maximum ranges, local-day/DST boundaries, batched queries, fresh updates,
read-only routes, schema coverage and W3TC/no-store behavior.
