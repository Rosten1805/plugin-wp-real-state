<?php
/**
 * Columnas personalizadas del listado de Agentes.
 *
 * @package WPRealEstate\Admin
 */

namespace WPRealEstate\Admin;

use WPRealEstate\PostTypes\ListingType;

defined('ABSPATH') || exit;

class AgentColumns
{
    public function addColumns(array $columns): array
    {
        $result = [];
        foreach ($columns as $key => $label) {
            if ($key === 'cb') {
                $result[$key] = $label;
                $result['wpre_agent_photo'] = __('Foto', 'wp-real-estate');
                continue;
            }
            if ($key === 'title') {
                $result[$key] = $label;
                $result['wpre_agent_phone']    = __('Teléfono', 'wp-real-estate');
                $result['wpre_agent_email']    = __('Email', 'wp-real-estate');
                $result['wpre_agent_specialty'] = __('Especialidad', 'wp-real-estate');
                $result['wpre_agent_listings'] = __('Inmuebles', 'wp-real-estate');
                continue;
            }
            if ($key === 'taxonomy-agent_specialty') {
                continue;
            }
            $result[$key] = $label;
        }
        return $result;
    }

    public function renderColumn(string $column, int $postId): void
    {
        switch ($column) {
            case 'wpre_agent_photo':
                if (has_post_thumbnail($postId)) {
                    echo get_the_post_thumbnail($postId, [50, 50], ['class' => 'wpre-column-thumb wpre-column-thumb--round']);
                } else {
                    echo '<span class="wpre-column-thumb wpre-column-thumb--round wpre-column-thumb--empty dashicons dashicons-admin-users"></span>';
                }
                break;

            case 'wpre_agent_phone':
                $phone = get_post_meta($postId, '_wpre_agent_phone', true);
                echo $phone ? esc_html($phone) : '—';
                break;

            case 'wpre_agent_email':
                $email = get_post_meta($postId, '_wpre_agent_email', true);
                echo $email ? sprintf('<a href="mailto:%s">%s</a>', esc_attr($email), esc_html($email)) : '—';
                break;

            case 'wpre_agent_specialty':
                $terms = get_the_terms($postId, 'agent_specialty');
                echo ($terms && !is_wp_error($terms))
                    ? esc_html(implode(', ', wp_list_pluck($terms, 'name')))
                    : '—';
                break;

            case 'wpre_agent_listings':
                $count = $this->listingsCount($postId);
                echo $count > 0
                    ? sprintf(
                        '<a href="%s">%d</a>',
                        esc_url(admin_url('edit.php?post_type=' . ListingType::SLUG . '&meta_key=_wpre_agent_id&meta_value=' . $postId)),
                        $count
                    )
                    : '0';
                break;
        }
    }

    public function sortableColumns(array $columns): array
    {
        $columns['wpre_agent_listings'] = 'wpre_agent_listings';
        return $columns;
    }

    private function listingsCount(int $agentId): int
    {
        $query = new \WP_Query([
            'post_type'      => ListingType::SLUG,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => '_wpre_agent_id',
            'meta_value'     => $agentId,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        return $query->post_count;
    }
}
