# OAuth client management

Open `/oauth/clients` on the blog (locally, `https://blog.test/oauth/clients`).
This is a theme page, outside WordPress admin. Guests return here after native
WordPress login. Both reading and changing clients require `manage_options`.
Having only `edit_posts` grants health MCP access, not client administration.

The page lists all Passport clients, including clients registered dynamically by
ChatGPT, with 20 clients per page. It supports:

- Creating public PKCE or confidential authorization-code clients with refresh tokens.
- Editing authorization-code client names and exact callback URLs.
- Rotating confidential client secrets. Update the consuming application's secret;
  existing access tokens are unaffected.
- Revoking a client, its access/refresh tokens and pending authorization codes.
  This disconnects every user of that client. Revoked clients remain visible and
  cannot be reactivated on this page.

New secrets are returned only in the successful creation/rotation response and shown
once in a dialog. Only their hashes are stored by Passport. Secrets are never included
in the list or stored in browser local/session storage. The page and JSON responses
are no-store; W3 Total Cache excludes this route. Changes use the Laravel web group,
CSRF protection and rate limiting. Native WordPress login works for administrators
other than user ID 1 without requiring a wallet cookie on this route.

No new database migration is needed; this uses the existing Passport tables.
Run `npm run production` when deploying to include the page's JavaScript and styles.

Focused regression checks: `php tests/auth/oauth-clients.php` (isolated SQLite,
no changes to the actual WordPress database).
