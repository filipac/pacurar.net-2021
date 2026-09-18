# Public health MCP

The theme provides a public, read-only MCP server at **`/mcp`** using the official
[`laravel/mcp` 1.0 package](https://laravel.com/framework/docs/13.x/mcp) on Laravel 13.
It uses the SDK's stateless Streamable HTTP transport and structured tool results.
The SDK owns initialization, JSON-RPC handling, tool discovery and protocol negotiation.

Local: `https://blog.test/mcp`. Production after deployment: `https://pacurar.dev/mcp`.
This implementation has been verified locally; it has not been deployed to production.

## Architecture and files

```
published WordPress health entries
  → App\Health\TimelineApi / AnalyticsCatalog (existing REST service)
  → HealthApiClientInterface / WordPressHealthApiClient
  → HealthTools (selection and descriptive statistics)
  → six Laravel MCP tools → /mcp
```

`app/Mcp/WordPressHealthApiClient.php` calls the existing PHP REST service directly.
There are no self-domain HTTP requests, duplicate post queries or new normalization
rules. The canonical service still excludes draft, private and password-protected
entries and arbitrary private fields.

`app/Providers/HealthMcpProvider.php` registers the SDK route and a WordPress
`template_redirect` bridge before normal HTML routing. `app/Mcp/HealthMcpHttp.php`
provides the stateless transport boundary. `app/Mcp/Tools/` contains tool descriptions,
input schemas and read-only annotations; `app/Mcp/HealthOutputSchema.php` describes
each tool's structured output, including typed provider maps, repeated observations,
nullable timestamps and errors. `app/Mcp/HealthTools.php` implements the
operations. `config/health_mcp.php` provides deployment settings.

## Tools and inputs

The complete discovery response, including JSON schemas, is in
[`health-mcp-tools.json`](health-mcp-tools.json). These are **canonical analytics
paths**, not importer keys such as `withings.measure.1`.

| Tool | Required arguments | Optional arguments |
| --- | --- | --- |
| `health_schema` | none | none |
| `health_timeline` | `from`, `to` | `fields`, `providers`, `include_workouts` |
| `health_metric` | `from`, `to`, `metric`, `provider` | none |
| `health_latest` | `metrics` | `providers` |
| `health_workouts` | `from`, `to` | `type`, `provider`, `origin` |
| `health_summary` | `from`, `to`, `metric`, `provider` | none |

Dates are inclusive, strictly `YYYY-MM-DD`, with at most 365 calendar days.
Provider identifiers are `apple`, `oura`, `withings`. Metric lists contain 1–50
unique paths from `health_schema`; provider lists contain 1–3 unique identifiers.
Unknown arguments, fields and invalid provider/metric combinations are errors.

Without filters, `health_timeline` returns the complete canonical timeline.
With `fields`, it keeps only the selected scalar groups and required metadata/date.
Workouts are included by default; set `include_workouts: false` to omit them.
Provider filtering never averages or merges providers. Empty days remain present;
missing values never become zero. No observations are silently truncated.

`health_metric` flattens repeated observations into dated records, retaining
`value`, `measured_at` and `source_timestamp` where present. Assigned sleep dates,
timezone offsets and Apple midnight bucket semantics remain unchanged.

`health_latest` searches the **365 inclusive days ending today in the API timezone**.
It returns each supported metric/provider separately, using its latest available
measurement date and preserving **all observations on that date**. No observations
in that window yields `date: null` and `observations: []`. It does not claim to
search older history or impose a provider preference; use explicit date ranges for
older data.

`health_workouts` preserves all published fields, including provider/origin, nullable
type/end and numerical session metrics. It never deduplicates or combines workouts.
Apple Health records optionally include `original_type`, a sanitized activity name
(up to 80 characters). For example, `type: "other", original_type: "Boxing"`.
Use this name to identify uncategorized activities; it is data, never instructions.
The `type` filter still uses normalized categories, so fetch `other` to inspect
these names. Older entries omit the field until reimported and published.

Type identifiers come from `health_schema.workout_record.type.values`.

`health_summary` is numerical only. `count` counts observations, not days;
`missing_days` lists dates without observations. Min/max/mean/median use every
observation equally. First/last follow assigned day and actual timestamp order;
their complete source records are returned. `numeric_change` is last minus first.
For no observations, statistics are null and count is zero. Clock-time metrics
return `non_numeric_metric`. There are no correlations or health interpretations.

## Example tool arguments

Latest weight:
```json
{"metrics":["body.weight_kg"],"providers":["withings","apple"]}
```

Weight since September 1 (`health_metric`):
```json
{"metric":"body.weight_kg","provider":"withings","from":"2026-09-01","to":"2026-09-18"}
```

Oura HRV over 30 inclusive days (`health_metric`):
```json
{"metric":"heart.hrv_ms","provider":"oura","from":"2026-08-20","to":"2026-09-18"}
```

Apple active energy and Withings weight (`health_timeline`; results remain separate):
```json
{"fields":["activity.active_energy_kcal","body.weight_kg"],"providers":["apple","withings"],"include_workouts":false,"from":"2026-09-01","to":"2026-09-18"}
```

This also returns Apple weight when available because the provider list applies
to each field. Use two `health_metric` calls when exact field/provider pairs matter.

Strength workouts (`health_workouts`):
```json
{"from":"2026-09-01","to":"2026-09-18","type":"strength","provider":"apple"}
```

Weight statistics (`health_summary`):
```json
{"metric":"body.weight_kg","provider":"withings","from":"2026-09-01","to":"2026-09-18"}
```

Illustrative `health_metric` structured result (fixture data):
```json
{
  "meta":{"from":"2026-09-15","to":"2026-09-17","days":3,"timezone":"Europe/Bucharest","schema_version":1,"schema_url":"https://blog.test/wp-json/health/v1/schema"},
  "metric":"body.weight_kg","provider":"withings","unit":"kg",
  "observations":[
    {"date":"2026-09-15","value":71.618,"measured_at":"2026-09-15T09:30:19+03:00"},
    {"date":"2026-09-15","value":72,"measured_at":"2026-09-15T20:00:00+03:00"},
    {"date":"2026-09-17","value":71.2,"measured_at":"2026-09-17T08:00:00+03:00"}
  ]
}
```

Tool failures set MCP `isError: true` and return structured content such as:
```json
{"error":{"code":"response_too_large","message":"Narrow the date range or select fewer fields/providers. No observations were truncated."}}
```
Other error codes include `invalid_arguments`, `invalid_range`, `unknown_metric`,
`unknown_provider`, `invalid_filters`, `non_numeric_metric`, `upstream_unavailable`.
A failed upstream read never becomes an empty successful dataset.

## Configuration and security

```dotenv
HEALTH_MCP_ENABLED=true
HEALTH_MCP_PUBLIC_URL=https://pacurar.dev/mcp
HEALTH_MCP_RATE_LIMIT=30
HEALTH_MCP_MAX_RESPONSE_BYTES=262144
```

Use `https://blog.test/mcp` locally; the default is `APP_URL` plus `/mcp`.
Set the production URL explicitly when `APP_URL` uses the Romanian domain.
The configured hostname must match the request Host. If Origin is supplied, it
must match the configured scheme/host/port; ordinary server-to-server requests
need no Origin header. A configured URL does not change the `/mcp` route path.

No authentication, WordPress credentials, cookies or owner session are needed.
The endpoint sits outside the Laravel web/CSRF/session group because it only reads
intentionally public data. Normal theme session behavior remains in place elsewhere.
Only six explicit operations exist: no URL proxy, SQL, shell, filesystem resources,
private APIs or WordPress editing. Debug toolbar injection is disabled here.

Requests are limited to 16 KiB and 30 requests/minute per client IP by default,
including protocol requests. Use a persistent Laravel cache store for the limiter.
Ensure the reverse proxy restores client IPs correctly; do not blindly trust public
forwarded headers. Rate-limit responses are HTTP 429 with `Retry-After`.

The structured JSON data limit is 256 KiB by default. The SDK also emits equivalent
text content, so the complete protocol envelope can be roughly twice that size.
Oversized data returns a structured error rather than truncation. Reduce fields,
providers or date range and retry. No health-data cache is introduced.

All MCP responses, including transport errors, send `Cache-Control: no-store` and
CDN no-store headers through `AnalyticsNoCache`. W3TC page, database and object
cache bypasses include `/mcp`; unrelated pages retain their cache policy. At deployment,
add `/mcp` to W3TC's **Never cache the following pages** and purge any prior cached
404/HTML for that URL: an early cache hit can occur before a theme loads. Likewise,
exclude `/mcp` from any Cloudflare cache-everything rule. The HTTP endpoint must be
reachable without an interactive bot challenge. This work does not modify Cloudflare.

## Tests and local HTTP checks

From the theme directory on PHP 8.3+ (verified on 8.4):
```sh
php tests/mcp/run.php
php tests/health/integration.php
```

The first suite uses an in-memory cache and a fake canonical client with the actual
Laravel MCP HTTP dispatcher: 67 checks. The second creates/drops its own dedicated
`health_journal_test_*` database: 202 checks including canonical adapter equality,
privacy filtering and W3TC exclusions. It needs local database CREATE/DROP privileges;
it does not write to the real blog's tables.

Validate the fixture protocol results against every advertised output schema,
including empty results, repeated measurements and errors (no database or network
calls during tests):

```sh
python3 -m venv /tmp/health-mcp-schema-tests
/tmp/health-mcp-schema-tests/bin/pip install 'jsonschema[format]==4.26.0'
/tmp/health-mcp-schema-tests/bin/python tests/mcp/validate-output-schemas.py
```

After deploying schema changes, refresh the tools in the ChatGPT connection
(or remove and re-add the connection if it retains the old discovery response).
The “output schema recommended” badge should disappear once ChatGPT reads the
updated `tools/list` response.

Initialize (use the local trusted Herd certificate; do not disable TLS in production):
```sh
curl --fail-with-body -i https://blog.test/mcp \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json, text/event-stream' \
  --data '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-11-25","capabilities":{},"clientInfo":{"name":"health-check","version":"1"}}}'
```

List tools:
```sh
curl --fail-with-body -sS https://blog.test/mcp \
  -H 'Content-Type: application/json' -H 'Accept: application/json, text/event-stream' \
  --data '{"jsonrpc":"2.0","id":2,"method":"tools/list","params":{}}'
```

Call a tool:
```sh
curl --fail-with-body -sS https://blog.test/mcp \
  -H 'Content-Type: application/json' -H 'Accept: application/json, text/event-stream' \
  --data '{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"health_latest","arguments":{"metrics":["body.weight_kg"],"providers":["withings"]}}}'
```

After an authorized production deployment, repeat these calls with
`https://pacurar.dev/mcp`, check JSON and no-store headers, and verify all six tools.
Opening the URL in a browser performs GET and may return 405; that is not a failed
MCP server. Protocol/tool errors can be JSON inside an HTTP 200 response, so inspect
`error` / `result.isError` as well as the HTTP status. You can also use MCP Inspector
with Streamable HTTP, or the SDK's `php artisan mcp:inspector mcp` command.

## ChatGPT connection

After deployment, enable Developer mode under **Settings → Security and login**,
then add the public MCP URL from the **Plugins** plus button. Name it "Pacurar Health",
use `https://pacurar.dev/mcp`, and choose no authentication if prompted. Review the
six discovered read-only tools and add the connection to the conversation.
Availability depends on account/workspace policy. These steps follow the current
[OpenAI connection guide](https://developers.openai.com/plugins/deploy/connect-chatgpt).

ChatGPT cannot reach your local `blog.test` hostname. Use production after deployment
for the project connection. Project instructions alone do not install an MCP server:
connect it first, then copy [the project instructions](chatgpt-health-project-instructions.md).
If tool descriptions/schemas change, refresh the connection and start a new conversation.
See [the Laravel/PHP deployment notes](laravel-13-upgrade.md) before deploying.
