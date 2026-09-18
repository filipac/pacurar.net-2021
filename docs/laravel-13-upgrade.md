# Laravel 13 / PHP 8.4 upgrade

## Delivery state (18 September 2026)

The local theme runs Laravel **13.32.0** and official `laravel/mcp` **1.0.0** on
PHP **8.4.23**. Herd's `blog.test` site is isolated to PHP 8.4. The theme upgrade and
MCP implementation are included in this release; production deployment is separate.

All six changed dependency repositories are committed and pushed. Composer uses
public GitHub repositories and the committed references in `composer.lock`; no
local `/Users/...` path repositories remain. Install the lock file, do not resolve
fresh branches on production.

LaraWelP is pinned to the `v0.0.11` release at the same tested commit listed below,
rather than tracking `0.x-dev`.

| Repository | Branch | Published commit |
| --- | --- | --- |
| [larawelp/monorepo](https://github.com/larawelp/monorepo) | `0.x` | `8846a98030e0865a80a459c1df14cc0d42b72597` |
| [filipac/corcel](https://github.com/filipac/corcel) | `7.0` | `43fb9ace77399a605635ddcd473c95ddc33a650d` |
| [filipac/laravel-currency](https://github.com/filipac/laravel-currency) | `master` | `59c0d1dd5f93a815f6216beed26b9ef426ee322c` |
| [filipac/mx-sdk-php](https://github.com/filipac/mx-sdk-php) | `patch-1` | `9096d0daa9e107ed8b1c0eb3aa70658ae843f636` |
| [filipac/mx-sdk-laravel](https://github.com/filipac/mx-sdk-laravel) | `patch-1` | `393cad732aa31c149922bb6b5706dc8845d7806a` |
| [filipac/mx-sdk-php-network-providers](https://github.com/filipac/mx-sdk-php-network-providers) | `laravel-13` | `a3a8e32e0f3dc689e02544499b74898724bb6e07` |

`mx-sdk-php-network-providers` and `laravel-currency` were forked into `filipac`.
The network provider branch preserves the 1.3 API and allows Carbon 3; its Composer
alias remains 1.3.0 for existing SDK requirements. Corcel/SDK changes include real
compatibility fixes, not only widened version constraints. ACF and theme-blocks
needed no source changes.

LaraWelP's new compatibility CI passed on Laravel 12/PHP 8.2 and Laravel 13/PHP
8.3/8.4. The **Split monorepo in multiple repositories** workflow also passed after
`DEPLOY_TOKEN_SPLIT` was replaced and the failed run was retried. The component
repositories are synchronized. This theme consumes `larawelp/all` directly from
the monorepo.

## Important application changes

- Laravel 13 requires PHP 8.3+. PHP 8.4 is the selected deployment runtime.
- LaraWelP has explicit component autoload mappings, avoiding recursive scans of
  nested theme/test vendor folders. WordPress route validators/router restoration
  now uses `finally`; PHP 8.4 nullable signatures are explicit.
- Corcel and the theme's WordPress user provider support Laravel's password API
  without rehashing or rewriting WordPress-owned password hashes.
- The theme passes the actual session store to `setLaravelSession`, as required by
  the new framework. MCP bypasses native/Laravel sessions and language redirect work.
- Carbon 3 differences are handled for streak intervals and cached HTTP TTLs.
- The MultiversX HTTP client preserves its default cache middleware; decimal SDK
  conversions use explicit strings for current Brick Math compatibility.
- Browsershot is now 5.4 with Puppeteer 24; older compatible choices were blocked by
  dependency conflicts/security advisories. Livewire remains on supported 3.x.
- Composer scripts still apply LaraWelP's WordPress translation-helper adaptations.
  Do not omit those scripts from the completed deployment.

## Server installation already completed

On SSH host `filipaccloud-new` (Ubuntu 22.04 arm64), PHP **8.4.25** CLI/FPM was
installed alongside the existing versions, using the signed Ondrej PHP PPA.
Extensions include bcmath, curl, gd, gmp, intl, mbstring, MySQL, PostgreSQL, SQLite,
XML, ZIP, SOAP, Redis, Imagick, Memcached and OPcache.

`php8.4` works and `php8.4-fpm` is running. Its pool runs as `forge:forge`, with
max_children=20, start_servers=4, min_spare_servers=3, max_spare_servers=4. The socket
is `/run/php/php8.4-fpm.sock`, owned by www-data. FPM configuration validates.
`/etc/php/8.4/fpm/conf.d/99-forge.ini` sets memory 512M, execution 30s, uploads/post
100M and timezone UTC, matching the existing site's runtime settings. The WordPress
health timezone remains configured separately, normally Europe/Bucharest.

**The production site's Nginx configuration and global `php` alternative were not
switched.** `php` still resolves to 8.2.4, and the site continues using its 8.2 FPM
socket. No other Forge sites were migrated. The installed 8.4 runtime is ready for
a deliberate site deployment.

The apt refresh also exposed pre-existing failures in unrelated NodeSource 18 and
Ondrej Nginx Jammy repositories. The PHP repository and PHP installation succeeded;
those unrelated repository configurations were left unchanged.

## Manual deployment sequence

1. Make a release containing the theme source, both lock files and built assets.
   Preserve the previous release/vendor directory and current Nginx configuration
   for rollback. Check other plugins against PHP 8.4 in staging before switching.
2. As the Forge application user, in the release's theme directory, install using
   the explicit new PHP binary and the real Composer executable path:
   ```sh
   php8.4 /path/to/composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
   php8.4 /path/to/composer check-platform-reqs --no-dev
   ```
   `/path/to/composer` is a placeholder: use the executable reported on that host.
   Composer scripts must finish successfully. Do not run `composer update` during
   deployment. Refresh a stale Composer GitHub credential if authentication fails;
   never embed tokens in repository files or logs.
3. If building on the server, install locked JS dependencies with
   `npm ci --legacy-peer-deps`, then `npm run production`. The legacy flag is needed
   by the existing Vite/chunk-plugin peer dependency setup. Use a Node version
   supported by Puppeteer 24 and ensure Chrome is available for OG image jobs.
   Local Browsershot 5/Puppeteer 24 generated a valid PNG; production browser
   execution remains a deployment check. Keep the job's configured Node/Chrome paths
   aligned with the host.
4. Configure `HEALTH_MCP_PUBLIC_URL=https://pacurar.dev/mcp`, enable MCP, and keep
   production `APP_DEBUG=false`. See [MCP configuration](health-mcp.md).
   Clear/rebuild only release-local configuration/view caches as appropriate:
   ```sh
   php8.4 artisan config:clear
   php8.4 artisan view:clear
   ```
   This feature adds no database migrations. Do not run schema migrations just for MCP.
5. Add `/mcp` to W3TC never-cache pages and any CDN cache exclusions; purge any
   existing cached response for that URL. Ensure Cloudflare allows public MCP POSTs
   without a browser challenge. Verify client IP handling for the rate limiter.
6. In Forge/Nginx, switch only the relevant blog vhosts to the PHP 8.4 FPM socket
   while activating the compatible release. Include the blog's internal OG image
   vhost if it renders the theme. Validate Nginx before reload. Existing configurations
   observed were `/etc/nginx/sites-enabled/pacurar` and `og-image-local`, using the
   `/home/forge/pacurar.net` document root. Re-check actual host state at deployment.
7. Use explicit `php8.4` for this app's scheduler/queue commands and restart its
   workers after deployment. Do not change other sites' runtimes or global alternatives.
8. Verify homepage, blog archive, health archive/single/compare, WordPress login,
   GraphQL and any wallet/OG-image flows actually used in production. Run the MCP
   initialization/tool examples against `https://pacurar.dev/mcp`, checking JSON,
   all six tools, no-store headers and absence of bot challenges. Then connect ChatGPT.

Rollback requires the **previous theme source and its matching vendor/lock files**
and the previous PHP 8.2 socket configuration together. Do not run the new Laravel
13 vendor tree on PHP 8.2. No health data migration needs reversing.

## Validation completed locally

- 197 isolated WordPress integration checks, including privacy, publishing,
  concurrency, analytics and adapter/W3TC compatibility.
- 59 actual-SDK MCP HTTP tests with a fake canonical service and in-memory cache.
- All six tools successfully called over real local HTTPS; handshake negotiated
  protocol 2025-11-25. No session cookies or debugbar HTML in MCP responses.
- LaraWelP bridge/SDK tests on Laravel 10, 12 and 13; GitHub matrix passed 12/13.
- 26 focused fork compatibility checks on Laravel 10 and 13. These cover changed
  integrations; they are not a claim that every old upstream test suite was ported.
- Composer manifest/platform validation and autoload/package discovery passed.
  Composer audit reported no security advisories; existing abandoned-package notices
  remain. No blanket npm audit remediation was performed.
- Frontend production build passed; archive was inspected in the browser on PHP 8.4.
- Browsershot 5/Puppeteer 24 rendered a valid 600×200 PNG using installed Chrome.

Production health endpoints and all business flows must still be checked after the
separate deployment. This work did not deploy the theme or publish health entries.
