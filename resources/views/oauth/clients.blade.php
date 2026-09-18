<x-layouts.master title="OAuth clients · Filip Pacurar">
    <div class="oauth-clients" id="oauth-clients" data-endpoint="{{ route('oauth.clients.mutate') }}" data-token="{{ csrf_token() }}">
        <header class="oauth-heading">
            <div><p class="oauth-eyebrow">ACCOUNT / CONNECTED APPLICATIONS</p><h1>OAuth clients</h1></div>
            <p>Manage the applications that connect to your blog.<br>Signed in as <strong>{{ wp_get_current_user()->user_login }}</strong>.</p>
        </header>
        <div id="oauth-status" role="status" aria-live="polite" tabindex="-1"></div>
        <section class="oauth-create" aria-labelledby="oauth-create-heading">
            <div><h2 id="oauth-create-heading">A new connection</h2><p>Create a client for an application using OAuth. Automatically registered clients appear below too.</p></div>
            <details><summary>Create a client <span aria-hidden="true">＋</span></summary>
                <form data-oauth-form>
                    <input type="hidden" name="operation" value="create">
                    <label for="oauth-name">Application name</label><input id="oauth-name" name="name" required maxlength="255" placeholder="For example, ChatGPT">
                    <label for="oauth-redirects">Callback URLs — one per line</label><textarea id="oauth-redirects" name="redirects" rows="3" required placeholder="https://example.com/oauth/callback"></textarea>
                    <p class="oauth-help">Use the exact HTTPS callback supplied by the application. HTTP is allowed for localhost and loopback callbacks.</p>
                    <label class="oauth-checkbox"><input type="checkbox" name="confidential" value="1"> Confidential client (requires a secret)</label>
                    <p class="oauth-help">Leave unchecked for a public PKCE client. New clients use authorization-code and refresh-token grants.</p>
                    <button class="oauth-primary">Create client ↗</button>
                </form>
            </details>
        </section>
        <div class="oauth-list-heading"><h2>Registered applications</h2><span>{{ $total }} {{ $total === 1 ? 'client' : 'clients' }}</span></div>
        <div class="oauth-list">
            @forelse($clients as $client)
                <article class="oauth-client">
                    <header><h3>{{ $client->name }}</h3><span class="oauth-badge {{ $client->revoked ? 'is-revoked' : '' }}">{{ $client->revoked ? 'Revoked' : 'Active' }}</span></header>
                    <div class="oauth-client-grid">
                        <dl><dt>Client ID</dt><dd><code>{{ $client->getKey() }}</code></dd><dt>Type</dt><dd>{{ $client->confidential() ? 'Confidential' : 'Public · no secret' }}</dd><dt>Created</dt><dd>{{ $client->created_at?->format('d M Y, H:i') }}</dd></dl>
                        <dl><dt>Callback URLs</dt><dd>@forelse($client->redirect_uris as $uri)<code class="oauth-uri">{{ $uri }}</code>@empty None @endforelse</dd><dt>Grants</dt><dd class="oauth-grants">{{ implode(', ', $client->grant_types) }}</dd></dl>
                    </div>
                    @if(!$client->revoked)
                        <div class="oauth-actions">
                            @if($client->hasGrantType('authorization_code'))
                                <details><summary>Edit client</summary><form data-oauth-form>
                                    <input type="hidden" name="operation" value="update"><input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                                    <label>Name<input name="name" required maxlength="255" value="{{ $client->name }}"></label>
                                    <label>Callback URLs<textarea name="redirects" required rows="3">{{ implode("\n", $client->redirect_uris) }}</textarea></label>
                                    <button class="oauth-primary">Save changes</button>
                                </form></details>
                            @endif
                            @if($client->confidential())
                                <form data-oauth-form data-confirm="Rotate this client's secret? Applications using the old secret will stop connecting until you update them.">
                                    <input type="hidden" name="operation" value="rotate"><input type="hidden" name="client_id" value="{{ $client->getKey() }}"><button>Rotate secret</button>
                                </form>
                            @endif
                            <form data-oauth-form data-confirm="Revoke this client and all of its tokens? Every user of this application will be disconnected. This cannot be undone here.">
                                <input type="hidden" name="operation" value="revoke"><input type="hidden" name="client_id" value="{{ $client->getKey() }}"><button class="oauth-danger">Revoke client</button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <p class="oauth-empty">No clients yet. Create your first connection above.</p>
            @endforelse
        </div>
        <footer class="oauth-footer"><p>Revoking disconnects a client for all users. Health tools also require the user’s <code>edit_posts</code> capability.</p>
            <nav aria-label="Client pages">@if($page > 1)<a href="{{ route('oauth.clients', ['page'=>$page-1]) }}">← Previous</a>@endif<span>{{ $page }} / {{ $pages }}</span>@if($page < $pages)<a href="{{ route('oauth.clients', ['page'=>$page+1]) }}">Next →</a>@endif</nav>
        </footer>
        <dialog id="oauth-secret-dialog" aria-labelledby="oauth-secret-title">
            <p class="oauth-eyebrow">CONNECTION CREDENTIALS</p><h2 id="oauth-secret-title">Save your client secret</h2>
            <p>This secret is shown only now. Copy it into the application's OAuth settings before closing.</p>
            <label for="oauth-result-id">Client ID</label><input id="oauth-result-id" readonly autocomplete="off">
            <label for="oauth-result-secret">Client secret</label><input id="oauth-result-secret" readonly autocomplete="off" spellcheck="false">
            <p class="oauth-help">Rotating replaces the old secret. Existing access tokens remain valid until expiry or client revocation.</p>
            <button type="button" id="oauth-secret-done" class="oauth-primary">I've saved it — close</button>
        </dialog>
        <noscript><p>Enable JavaScript to create or change clients.</p></noscript>
    </div>
    @vite('resources/js/oauth-clients.js')
</x-layouts.master>
