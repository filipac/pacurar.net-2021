<?php

namespace App\Health;

class JournalApi
{
    public function permission(): bool
    {
        return current_user_can('publish_health_entries');
    }

    private function find(string $topic, string $date): ?\WP_Post
    {
        $posts = get_posts(['post_type' => 'health_entry', 'post_status' => array_keys(get_post_stati()),
            'meta_key' => '_health_identity', 'meta_value' => $topic.':'.$date, 'posts_per_page' => 1, 'suppress_filters' => true]);
        return $posts[0] ?? null;
    }

    public function read(\WP_REST_Request $request)
    {
        $topic = $request->get_param('topic'); $date = $request->get_param('date');
        if (! is_string($topic) || ! isset(MetricCatalog::TOPICS[$topic]) || ! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) return new \WP_Error('health_invalid', 'Invalid topic or date.', ['status' => 422]);
        $post = $this->find($topic, $date);
        if ($post && ! current_user_can('edit_post', $post->ID)) return new \WP_Error('health_forbidden', 'You cannot update this entry.', ['status' => 403]);
        if ($post && $post->post_status !== 'publish') return new \WP_Error('health_hidden', 'This entry is unpublished or trashed. Resolve it in WordPress before publishing.', ['status' => 409]);
        return ['schema_version' => 1, 'entry' => $post ? get_post_meta($post->ID, '_health_data', true) : null,
            'revision' => $post ? $this->revision($post->ID) : null, 'url' => $post ? get_permalink($post) : null];
    }

    private function revision(int $id): string
    {
        // Include the current post and taxonomies so manual WordPress edits invalidate previews too.
        $post = get_post($id);
        return hash('sha256', wp_json_encode([get_post_meta($id, '_health_data', true), $post->post_title, $post->post_content, $post->post_status,
            wp_get_object_terms($id, ['health_category', 'health_source'], ['fields' => 'tt_ids'])]));
    }

    public function upsert(\WP_REST_Request $request)
    {
        if (strlen($request->get_body()) > 4 * 1024 * 1024) return new \WP_Error('health_large', 'Health entry is too large.', ['status' => 413]);
        $input = $request->get_json_params();
        try {
            if (! is_array($input) || ! array_key_exists('expected_revision', $input) || (! is_null($input['expected_revision']) && (! is_string($input['expected_revision']) || ! preg_match('/^[a-f0-9]{64}$/D', $input['expected_revision'])))) throw new \InvalidArgumentException;
            $entry = EntryContract::normalize($input);
        } catch (\Throwable) { return new \WP_Error('health_invalid', 'Invalid health entry. Only recognized numerical metrics and timestamps are accepted.', ['status' => 422]); }
        global $wpdb;
        $lock = 'health:'.substr(hash('sha256', $wpdb->prefix.$entry['topic'].$entry['date']), 0, 50);
        if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lock)) !== 1) return new \WP_Error('health_busy', 'Another request is publishing this entry. Retry shortly.', ['status' => 503]);
        $id = null;
        try {
            $post = $this->find($entry['topic'], $entry['date']);
            if ($post && (! current_user_can('edit_post', $post->ID) || $post->post_status !== 'publish')) return new \WP_Error('health_forbidden', 'This entry cannot be updated by this request.', ['status' => 403]);
            $fingerprint = EntryContract::fingerprint($entry);
            // A lost response is safe to retry even with the original expected revision.
            if ($post && $fingerprint === EntryContract::fingerprint(get_post_meta($post->ID, '_health_data', true))) return $this->result($post->ID, 'unchanged');
            if (($post ? $this->revision($post->ID) : null) !== $input['expected_revision']) return new \WP_Error('health_conflict', 'Entry changed after preview. Fetch again.', ['status' => 409]);
            if ($post && EntryContract::fingerprint(EntryContract::merge(get_post_meta($post->ID, '_health_data', true), $entry)) !== $fingerprint) return new \WP_Error('health_conflict', 'Existing provider data must be included in the preview.', ['status' => 409]);
            $wpdb->query('START TRANSACTION');
            $values = ['post_type' => 'health_entry', 'post_status' => 'publish', 'post_title' => MetricCatalog::TOPICS[$entry['topic']].' · '.$entry['date'],
                'post_content' => $this->content($entry), 'post_name' => $entry['topic'].'-'.$entry['date'], 'comment_status' => 'closed', 'ping_status' => 'closed'];
            if ($post) $values['ID'] = $post->ID;
            else $values['post_author'] = get_current_user_id();
            $id = wp_insert_post(wp_slash($values), true);
            if (is_wp_error($id)) throw new \RuntimeException('Post write failed.');
            foreach (['_health_data' => $entry, '_health_date' => $entry['date'], '_health_identity' => $entry['topic'].':'.$entry['date'], '_health_fingerprint' => $fingerprint] as $key => $value) {
                update_post_meta($id, $key, wp_slash($value));
                if (get_post_meta($id, $key, true) != $value) throw new \RuntimeException('Metadata write failed.');
            }
            foreach (['health_category' => [$entry['topic']], 'health_source' => array_keys($entry['providers'])] as $taxonomy => $slugs) {
                $ids = [];
                foreach ($slugs as $slug) {
                    $term = get_term_by('slug', $slug, $taxonomy);
                    if (! $term) throw new \RuntimeException('Health taxonomy setup is incomplete.');
                    $ids[] = (int) $term->term_id;
                }
                if (is_wp_error(wp_set_object_terms($id, $ids, $taxonomy))) throw new \RuntimeException('Taxonomy write failed.');
            }
            $wpdb->query('COMMIT');
            clean_post_cache($id);
            if (function_exists('w3tc_flush_post')) w3tc_flush_post($id);
            if (function_exists('w3tc_flush_url')) w3tc_flush_url(get_post_type_archive_link('health_entry'));
            return $this->result($id, $post ? 'updated' : 'created');
        } catch (\Throwable) {
            $wpdb->query('ROLLBACK');
            if (is_int($id)) clean_post_cache($id);
            return new \WP_Error('health_write', 'The entry could not be saved. Please retry.', ['status' => 500]);
        } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock)); }
    }

    private function result(int $id, string $operation): array
    {
        return ['schema_version' => 1, 'id' => $id, 'url' => get_permalink($id), 'revision' => $this->revision($id), 'operation' => $operation];
    }

    private function content(array $entry): string
    {
        $html = '<p>'.esc_html($entry['date'].' · '.$entry['timezone']).'</p>';
        foreach ($entry['providers'] as $source => $section) {
            $html .= '<h2>'.esc_html(ucfirst($source)).'</h2><ul>';
            foreach ($section['metrics'] as $metric) $html .= '<li>'.esc_html($metric['label'].': '.$metric['value'].' '.$metric['unit']).'</li>';
            $html .= '</ul>';
        }
        return $html;
    }
}
