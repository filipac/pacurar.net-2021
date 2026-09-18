(() => {
    const root = document.getElementById('oauth-clients');
    if (!root) return;
    const status = document.getElementById('oauth-status');
    const dialog = document.getElementById('oauth-secret-dialog');
    let busy = false;
    window.addEventListener('pagehide', () => {
        document.getElementById('oauth-result-secret').value = '';
    });
    root.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-oauth-form]');
        if (!form) return;
        event.preventDefault();
        if (busy || (form.dataset.confirm && !window.confirm(form.dataset.confirm))) return;
        busy = true;
        const buttons = root.querySelectorAll('form button');
        buttons.forEach(button => { button.disabled = true; });
        status.className = 'notice notice-info';
        status.textContent = 'Saving…';
        const body = new FormData(form);
        body.set('_token', root.dataset.token);
        try {
            const response = await fetch(root.dataset.endpoint, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json' }, body });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'The change could not be confirmed. Reload the page before trying again.');
            status.className = 'notice notice-success';
            status.textContent = result.message;
            if (result.secret) {
                document.getElementById('oauth-result-id').value = result.client_id;
                document.getElementById('oauth-result-secret').value = result.secret;
                dialog.showModal();
            } else {
                window.location.reload();
            }
        } catch (error) {
            status.className = 'notice notice-error';
            status.textContent = error.message;
            status.focus();
            busy = false;
            buttons.forEach(button => { button.disabled = false; });
        }
    });
    dialog.addEventListener('cancel', event => event.preventDefault());
    document.getElementById('oauth-secret-done').addEventListener('click', () => {
        document.getElementById('oauth-result-secret').value = '';
        dialog.close();
        window.location.reload();
    });
})();
