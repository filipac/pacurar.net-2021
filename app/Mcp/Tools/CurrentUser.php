<?php

namespace App\Mcp\Tools;

use App\Models\WordpressUser;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsDestructive(false)]
#[IsIdempotent]
#[IsOpenWorld(false)]
final class CurrentUser extends Tool
{
    protected string $name = 'wordpress_current_user';

    protected string $description = 'Return only the email and username of the WordPress user authenticated by the current MCP access token. Takes no arguments and cannot look up other users.';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user('api');
        if (! $user instanceof WordpressUser) {
            return Response::error('WordPress authentication is required.');
        }
        if ($request->all() !== []) {
            return Response::error('This tool accepts no arguments.');
        }

        return Response::structured([
            'email' => (string) $user->user_email,
            'username' => (string) $user->user_login,
        ]);
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'email' => $schema->string()->description('WordPress user_email.')->required(),
            'username' => $schema->string()->description('WordPress user_login.')->required(),
        ];
    }

    public function toArray(): array
    {
        $result = parent::toArray();
        $result['securitySchemes'] = [['type' => 'oauth2', 'scopes' => ['mcp:use']]];
        $result['_meta']['securitySchemes'] = $result['securitySchemes'];
        $result['inputSchema']['additionalProperties'] = false;
        $result['outputSchema']['additionalProperties'] = false;

        return $result;
    }
}
