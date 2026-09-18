<?php

namespace App\Mcp;

use App\Health\AnalyticsCatalog;
use App\Health\TimelineApi;

/** Same canonical service as WordPress REST; no self-domain HTTP requests. */
final class WordPressHealthApiClient implements HealthApiClientInterface
{
    public function schema(): array
    {
        try {
            return AnalyticsCatalog::schema();
        } catch (\Throwable $error) {
            throw new HealthToolException('upstream_unavailable', 'The public health schema is unavailable.');
        }
    }

    public function timeline(string $from, string $to): array
    {
        try {
            $request = new \WP_REST_Request('GET', '/health/v1/timeline');
            $request->set_query_params(['from' => $from, 'to' => $to]);
            $response = (new TimelineApi)->timeline($request);
            if (is_wp_error($response) || $response->get_status() !== 200) {
                throw new HealthToolException('upstream_unavailable', 'Published health data could not be read. Try again later.');
            }

            return $response->get_data();
        } catch (HealthToolException $error) {
            throw $error;
        } catch (\Throwable $error) {
            throw new HealthToolException('upstream_unavailable', 'The public health API is unavailable.');
        }
    }
}
