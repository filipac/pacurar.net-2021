<?php

namespace App\Providers;

use App\Health\{EntryContract, JournalApi, MetricCatalog};
use Illuminate\Support\ServiceProvider;

class HealthJournalProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action('init', [$this, 'registerContent']);
        add_action('added_post_meta', [\App\Health\DailySummary::class, 'sync'], 10, 4);
        add_action('updated_post_meta', [\App\Health\DailySummary::class, 'sync'], 10, 4);
        add_action('deleted_post_meta', [\App\Health\DailySummary::class, 'remove'], 10, 3);
        add_action('rest_api_init', function () {
            $api = new JournalApi;
            register_rest_route('pacurar2020/v1', '/health-connection', ['methods' => 'GET', 'permission_callback' => [$api, 'permission'],
                'callback' => static fn () => ['schema_version' => 1, 'can_publish' => true, 'username' => wp_get_current_user()->user_login, 'site_url' => home_url()]]);
            register_rest_route('pacurar2020/v1', '/health-entries', [
                ['methods' => 'GET', 'callback' => [$api, 'read'], 'permission_callback' => [$api, 'permission']],
                ['methods' => 'POST', 'callback' => [$api, 'upsert'], 'permission_callback' => [$api, 'permission']],
            ]);
            register_rest_field('health_entry', 'health_data', [
                'get_callback' => static fn ($post) => get_post_status($post['id']) === 'publish' || current_user_can('edit_post', $post['id']) ? get_post_meta($post['id'], '_health_data', true) : null,
                'schema' => ['description' => 'Versioned, sanitized numerical health measurements.', 'type' => 'object', 'context' => ['view', 'edit']],
            ]);
        });
        add_action('pre_get_posts', function ($query) {
            if (is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive('health_entry')) return;
            $query->set('posts_per_page', config('health.per_page', 18));
            $query->set('meta_key', '_health_date');
            $query->set('orderby', ['meta_value' => 'DESC', 'ID' => 'DESC']);
            $filters = [];
            foreach (['topic' => 'health_category', 'source' => 'health_source'] as $param => $taxonomy) {
                if (! empty($_GET[$param]) && is_string($_GET[$param])) $filters[] = ['taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => sanitize_key($_GET[$param])];
            }
            if ($filters) $query->set('tax_query', $filters);
        });
    }

    public function registerContent(): void
    {
        register_post_type('health_entry', [
            'labels' => ['name' => 'Health journal', 'singular_name' => 'Health entry', 'add_new_item' => 'Add health entry'],
            'public' => true, 'show_in_rest' => true, 'rest_base' => 'health-entries',
            'has_archive' => config('health.archive_slug', 'health'),
            'rewrite' => ['slug' => config('health.archive_slug', 'health'), 'with_front' => false],
            'supports' => ['title', 'editor', 'author', 'revisions'], 'menu_icon' => 'dashicons-heart',
            'capability_type' => ['health_entry', 'health_entries'], 'map_meta_cap' => true,
            'delete_with_user' => false,
        ]);
        foreach (['health_category' => ['Health categories', true], 'health_source' => ['Health sources', false]] as $name => [$label, $hierarchical]) {
            register_taxonomy($name, 'health_entry', ['label' => $label, 'public' => false, 'show_ui' => true, 'show_in_rest' => true,
                'hierarchical' => $hierarchical, 'rewrite' => false,
                'capabilities' => ['manage_terms' => 'manage_health_entries', 'edit_terms' => 'manage_health_entries', 'delete_terms' => 'manage_health_entries', 'assign_terms' => 'edit_health_entries']]);
        }
        foreach (['_health_data' => 'object', '_health_daily_summary' => 'object', '_health_date' => 'string', '_health_identity' => 'string', '_health_revision' => 'string', '_health_fingerprint' => 'string'] as $key => $type) {
            register_post_meta('health_entry', $key, ['type' => $type, 'single' => true, 'show_in_rest' => false, 'auth_callback' => '__return_false']);
        }
        if (get_option('pacurar_health_version') !== '2') {
            $caps = ['read' => true, 'edit_health_entries' => true, 'edit_published_health_entries' => true, 'publish_health_entries' => true];
            add_role('health_publisher', 'Health publisher', $caps);
            $role = get_role('health_publisher');
            foreach ($caps as $cap => $grant) $role?->add_cap($cap, $grant);
            $admin = get_role('administrator');
            foreach (array_merge(array_keys($caps), ['edit_others_health_entries', 'read_private_health_entries', 'edit_private_health_entries', 'delete_health_entries', 'delete_published_health_entries', 'delete_private_health_entries', 'delete_others_health_entries', 'manage_health_entries']) as $cap) $admin?->add_cap($cap);
            foreach (MetricCatalog::TOPICS as $slug => $label) if (! term_exists($slug, 'health_category')) wp_insert_term($label, 'health_category', ['slug' => $slug]);
            foreach (MetricCatalog::SOURCES as $slug => $label) if (! term_exists($slug, 'health_source')) wp_insert_term($label, 'health_source', ['slug' => $slug]);
            flush_rewrite_rules(false);
            update_option('pacurar_health_version', '2', false);
        }
    }
}
