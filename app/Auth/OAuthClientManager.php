<?php

namespace App\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

final class OAuthClientManager
{
    public function __construct(private ClientRepository $clients) {}

    public function mutate(array $input): array
    {
        $data = Validator::make($input, [
            'operation' => 'required|in:create,update,rotate,revoke',
            'client_id' => 'required_unless:operation,create|string|max:80',
        ])->validate();
        $operation = $data['operation'];
        $values = $redirects = [];
        if (in_array($operation, ['create', 'update'], true)) {
            $values = Validator::make($input, [
                'name' => 'required|string|max:255',
                'redirects' => 'required|string|max:20000',
                'confidential' => 'sometimes|boolean',
            ])->validate();
            $redirects = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/', $values['redirects'])))));
            if (count($redirects) < 1 || count($redirects) > 10) {
                throw ValidationException::withMessages(['redirects' => 'Enter 1–10 callback URLs, one per line.']);
            }
            foreach ($redirects as $uri) {
                $parts = parse_url($uri);
                $https = ($parts['scheme'] ?? '') === 'https';
                $loopback = ($parts['scheme'] ?? '') === 'http' && in_array($parts['host'] ?? '', ['localhost', '127.0.0.1', '[::1]'], true);
                if (!filter_var($uri, FILTER_VALIDATE_URL) || strlen($uri) > 2048 || (!$https && !$loopback)
                    || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || str_contains($uri, '*')) {
                    throw ValidationException::withMessages(['redirects' => 'Use exact HTTPS callback URLs without credentials, fragments or wildcards. HTTP is allowed only for localhost or loopback addresses.']);
                }
            }
        }

        if ($operation === 'create') {
            $client = $this->clients->createAuthorizationCodeGrantClient(trim($values['name']), $redirects, (bool) ($values['confidential'] ?? false));

            return ['message' => 'Client created.', 'client_id' => $client->getKey(), 'secret' => $client->plainSecret];
        }

        return Passport::client()->getConnection()->transaction(function () use ($data, $operation, $values, $redirects) {
            $client = Passport::client()->newQuery()->lockForUpdate()->findOrFail($data['client_id']);
            if ($client->revoked) {
                throw ValidationException::withMessages(['client_id' => 'This client has already been revoked.']);
            }
            if ($operation === 'revoke') {
                Passport::refreshToken()->newQuery()->whereIn('access_token_id', $client->tokens()->select('id'))->update(['revoked' => true]);
                $client->tokens()->update(['revoked' => true]);
                $client->authCodes()->update(['revoked' => true]);
                $client->forceFill(['revoked' => true])->save();

                return ['message' => 'Client and its access tokens, refresh tokens and authorization codes revoked.'];
            }
            if ($operation === 'rotate') {
                if (!$client->confidential()) {
                    throw ValidationException::withMessages(['client_id' => 'Public clients do not have a secret.']);
                }
                $this->clients->regenerateSecret($client);

                return ['message' => 'Client secret rotated. Update the connecting application now.', 'client_id' => $client->getKey(), 'secret' => $client->plainSecret];
            }
            if (!$client->hasGrantType('authorization_code')) {
                throw ValidationException::withMessages(['client_id' => 'Only authorization-code clients can edit callback URLs here.']);
            }
            $this->clients->update($client, trim($values['name']), $redirects);

            return ['message' => 'Client updated.'];
        });
    }
}
