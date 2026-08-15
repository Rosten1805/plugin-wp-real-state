<?php
/**
 * Columnas personalizadas del listado de Inmuebles.
 *
 * @package WPRealEstate\Admin
 */

namespace WPRealEstate\Admin;

use WPRealEstate\PostTypes\ListingType;
use WPRealEstate\Support\Formatting;

defined('ABSPATH') || exit;

class ListingColumns
{
    public function addColumns(array $columns): array
    {
        $result = [];
        foreach ($columns as $key => $label) {
            if ($key === 'cb') {
                $result[$key] = $label;
                $result['wpre_thumbnail'] = __('Imagen', 'wp-real-estate');
                continue;
            }
            if ($key === 'title') {
                $result[$key] = $label;
                $result['wpre_reference']    = __('Ref.', 'wp-real-estate');
                $result['wpre_price']        = __('Precio', 'wp-real-estate');
                $result['wpre_operation']    = __('Operación', 'wp-real-estate');
                $result['wpre_type']         = __('Tipo', 'wp-real-estate');
                $result['wpre_location']     = __('Ubicación', 'wp-real-estate');
                $result['wpre_availability'] = __('Disponibilidad', 'wp-real-estate');
                $result['wpre_agent']        = __('Agente', 'wp-real-estate');
                continue;
            }
            if (in_array($key, ['taxonomy-listing_type', 'taxonomy-listing_operation', 'taxonomy-listing_location'], true)) {
                continue;
            }
            $result[$key] = $label;
        }
        return $result;
    }

    public function renderColumn(string $column, int $postId): void
    {
        switch ($column) {
            case 'wpre_thumbnail':
                if (has_post_thumbnail($postId)) {
                    echo get_the_post_thumbnail($postId, [60, 60], ['class' => 'wpre-column-thumb']);
                } else {
                    echo '<span class="wpre-column-thumb wpre-column-thumb--empty dashicons dashicons-format-image"></span>';
                }
                break;

            case 'wpre_reference':
                echo esc_html(get_post_meta($postId, '_wpre_reference', true));
                break;

            case 'wpre_price':
                $price = get_post_meta($postId, '_wpre_price', true);
                $currency = get_post_meta($postId, '_wpre_currency', true) ?: 'EUR';
                echo $price ? esc_html(Formatting::formatPrice((float) $price, $currency)) : '—';
                break;

            case 'wpre_operation':
                $this->printTermList($postId, 'listing_operation');
                break;

            case 'wpre_type':
                $this->printTermList($postId, 'listing_type');
                break;

            case 'wpre_location':
                $terms = get_the_terms($postId, 'listing_location');
                if ($terms && !is_wp_error($terms)) {
                    $deepest = $this->deepestTerm($terms);
                    echo esc_html($deepest ? $deepest->name : $terms[0]->name);
                } else {
                    echo '—';
                }
                break;

            case 'wpre_availability':
                $status = get_post_meta($postId, '_wpre_availability', true);
                if ($status) {
                    $labels = Formatting::availabilityOptions();
                    printf(
                        '<span class="wpre-badge wpre-badge--%s">%s</span>',
                        esc_attr($this->availabilityClass($status)),
                        esc_html($labels[$status] ?? $status)
                    );
                } else {
                    echo '—';
                }
                break;

            case 'wpre_agent':
                $agentId = get_post_meta($postId, '_wpre_agent_id', true);
                $agent = $agentId ? get_post($agentId) : null;
                if ($agent) {
                    printf('<a href="%s">%s</a>', esc_url(get_edit_post_link($agentId)), esc_html($agent->post_title));
                } else {
                    echo '—';
                }
                break;
        }
    }

    public function sortableColumns(array $columns): array
    {
        $columns['wpre_reference'] = '_wpre_reference';
        $columns['wpre_price'] = '_wpre_price';
        return $columns;
    }

    public function orderbyColumn(\WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== ListingType::SLUG) {
            return;
        }

        $orderby = $query->get('orderby');
        if ($orderby === '_wpre_price') {
            $query->set('meta_key', '_wpre_price');
            $query->set('orderby', 'meta_value_num');
        } elseif ($orderby === '_wpre_reference') {
            $query->set('meta_key', '_wpre_reference');
            $query->set('orderby', 'meta_value');
        }
    }

    private function printTermList(int $postId, string $taxonomy): void
    {
        $terms = get_the_terms($postId, $taxonomy);
        echo ($terms && !is_wp_error($terms))
            ? esc_html(implode(', ', wp_list_pluck($terms, 'name')))
            : '—';
    }

    private function deepestTerm(array $terms): ?\WP_Term
    {
        $deepest = null;
        $maxDepth = -1;
        foreach ($terms as $term) {
            $depth = 0;
            $parent = $term->parent;
            while ($parent > 0) {
                $depth++;
                $parentTerm = get_term($parent, 'listing_location');
                $parent = $parentTerm && !is_wp_error($parentTerm) ? $parentTerm->parent : 0;
            }
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
                $deepest = $term;
            }
        }
        return $deepest;
    }

    private function availabilityClass(string $status): string
    {
        return match ($status) {
            'disponible' => 'success',
            'reservada'  => 'warning',
            'vendida', 'alquilada' => 'info',
            'retirada'   => 'danger',
            default      => 'default',
        };
    }
}
