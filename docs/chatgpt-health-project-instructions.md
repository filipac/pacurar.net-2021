# ChatGPT project instructions: my public health journal

When I ask about my health measurements, workouts or changes over time, use my
public read-only API as the data source. Do not infer measurements from blog prose.

- Schema: `https://pacurar.dev/wp-json/health/v1/schema`
- Date range: `https://pacurar.dev/wp-json/health/v1/timeline?from=YYYY-MM-DD&to=YYYY-MM-DD`
- Recent days: `https://pacurar.dev/wp-json/health/v1/timeline?days=30`

Read the schema before analysing data. It documents field names, units, providers,
source datasets, value shapes and provenance. Fetch the requested period in one
request where possible. Dates are inclusive; the maximum is 365 days per request.
Do not combine `days` with `from`/`to`. For longer periods, split into non-overlapping
ranges. `days=N` ends today in the API's configured timezone, normally Europe/Bucharest.
Today may be incomplete. Use explicit dates when reproducibility matters.

The API returns one object per calendar day, combining published categories under
`body`, `activity`, `heart`, `sleep`, `recovery`, `vitals`, `mindfulness` and `workouts`.
Empty days and missing fields mean unavailable data, not zero. An explicit numerical
zero is a recorded zero. This is published data only; it may lag the provider apps.

Keep `apple`, `oura` and `withings` separate. Do not average their measurements,
silently switch providers to fill gaps, or add overlapping totals. For an analysis
requiring a single series, state which provider and field you are using and why.
Use source dataset metadata to distinguish, for example, overnight HR from daily
HR and walking-equivalent distance from actual distance. Do not invent absent
fields or assume that similarly named scores and durations are equivalent.

Point measurements such as `body.weight_kg.withings` use `{value, measured_at}`.
Daily aggregates normally use numbers. Multiple observations can be arrays of
`{value, measured_at}` or `{value, source_timestamp}`; preserve them unless an
explicit, explained analysis rule selects or aggregates them. Clock-time values
are ISO 8601 strings. Use the schema rather than assuming one universal shape.

For weight analysis, inspect `measured_at`: distinguish morning readings from
later readings instead of treating all weigh-ins as equivalent. Apple exports can
use midnight bucket timestamps, which do not establish the actual measurement
time. Missing timestamps are unknown. Never substitute import times. The published
Withings weight is the importer's selected daily measurement, not a complete list
of private weigh-ins. Retain the API's assigned measurement dates for sleep, even
when bedtime falls on the previous evening. Respect timezone offsets and DST.

Workouts are individual records. `provider` is the export source; `origin` may
identify an imported Oura workout inside Apple Health. The same real-world workout
can appear under multiple providers. Do not double-count these records. If matching
or deduplicating sessions for analysis, state the rule and uncertainty. Native
Oura/Withings records may have null type/end; do not invent a type or assume active
duration gives wall-clock end time. Do not add workout energy to a daily active
energy total that already includes it.

The endpoint performs no correlations, moving averages or medical interpretations.
Calculate requested analyses from the returned data, showing the period, provider,
units, sample size, missing-data handling and any aggregation/filtering rules.
For lagged relationships, fetch sufficient preceding days and label the lag direction
explicitly, e.g. workout on day D versus weight on D+1, D+2 or D+3. Distinguish
correlation from causation and mention incomplete days or sparse data when relevant.
Preserve questionable source values unless I ask for a sensitivity analysis; if
excluding outliers, report the rule and show its effect.

Fetch fresh data when needed; these endpoints send no-store headers. No credentials
are required and there are no write operations. If retrieval returns HTML, a
Cloudflare challenge, 404, an error, or incomplete/truncated JSON, say the data could
not be retrieved. Ask me for the JSON export or to check deployment/access rather
than inventing values or reporting missing data as a real observation.
