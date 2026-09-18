<?php

namespace App\Mcp\Tools;

use App\Health\AnalyticsCatalog;
use App\Health\MetricCatalog;
use App\Mcp\HealthOutputSchema;
use App\Mcp\HealthToolException;
use App\Mcp\HealthTools;
use App\Models\WordpressUser;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

abstract class HealthTool extends Tool
{
    public function handle(Request $request, HealthTools $tools): ResponseFactory
    {
        try {
            $user = $request->user('api');
            if (! $user instanceof WordpressUser || (int) $user->getAuthIdentifier() <= 0) {
                throw new HealthToolException('unauthenticated', 'WordPress authentication is required.');
            }
            // Check the token owner, never the ambient WordPress browser session.
            if (! user_can((int) $user->getAuthIdentifier(), 'edit_posts')) {
                throw new HealthToolException('forbidden', 'The authenticated WordPress user must have the edit_posts capability.');
            }

            $data = $tools->execute(substr($this->name, 7), $request->all());
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (strlen($json) > (int) config('health_mcp.max_response_bytes', 262144)) {
                throw new HealthToolException('response_too_large', 'Narrow the date range or select fewer fields/providers. No observations were truncated.');
            }

            return Response::structured($data);
        } catch (HealthToolException $error) {
            $data = ['error' => ['code' => $error->errorCode, 'message' => $error->getMessage()]];
        } catch (\Throwable $error) {
            report($error);
            $data = ['error' => ['code' => 'upstream_unavailable', 'message' => 'The health service could not complete this request. Try again later.']];
        }

        return Response::make(Response::error(json_encode($data, JSON_THROW_ON_ERROR)))->withStructuredContent($data);
    }

    public function schema(JsonSchema $schema): array
    {
        $operation = substr($this->name, 7);
        $providers = array_values(AnalyticsCatalog::PROVIDERS);
        $range = ['from' => $schema->string()->description('Inclusive first date, YYYY-MM-DD in the WordPress timezone.')->required(),
            'to' => $schema->string()->description('Inclusive last date, YYYY-MM-DD. At most 365 calendar days.')->required()];
        $providerList = $schema->array()->items($schema->string()->enum($providers))->min(1)->max(3)->unique()->description('Optional providers; omitted means all. Never merges providers.');
        $metrics = $schema->array()->items($schema->string())->min(1)->max(50)->unique()->description('Exact scalar metric paths from health_schema, for example body.weight_kg.');

        return match ($operation) {
            'schema' => [],
            'timeline' => $range + ['fields' => $metrics, 'providers' => $providerList, 'include_workouts' => $schema->boolean()->description('Defaults to true. Set false when only scalar metrics are needed.')],
            'metric','summary' => $range + ['metric' => $schema->string()->description('Exact scalar metric path from health_schema.')->required(), 'provider' => $schema->string()->enum($providers)->required()],
            'latest' => ['metrics' => $metrics->required(), 'providers' => $providerList],
            'workouts' => $range + ['type' => $schema->string()->enum(array_keys(MetricCatalog::WORKOUT_TYPES)), 'provider' => $schema->string()->enum($providers), 'origin' => $schema->string()->enum($providers)],
        };
    }

    public function toArray(): array
    {
        $result = parent::toArray();
        $result['inputSchema']['additionalProperties'] = false;
        $result['outputSchema'] = HealthOutputSchema::for(substr($this->name, 7));

        return $result;
    }
}
