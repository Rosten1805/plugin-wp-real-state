<?php
/**
 * Limpieza al desinstalar WP Real Estate.
 *
 * @package WPRealEstate
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

// Eliminar inmuebles.
$listings = get_posts([
    'post_type'      => 'wpre_listing',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
]);
foreach ($listings as $id) {
    wp_delete_post($id, true);
}

// Eliminar agentes.
$agents = get_posts([
    'post_type'      => 'wpre_agent',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
]);
foreach ($agents as $id) {
    wp_delete_post($id, true);
}

// Eliminar términos de las taxonomías del plugin.
$taxonomies = [
    'listing_type', 'listing_operation', 'listing_location',
    'listing_amenity', 'listing_condition', 'energy_rating',
    'agent_specialty',
];
foreach ($taxonomies as $taxonomy) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'ids']);
    if (!is_wp_error($terms)) {
        foreach ($terms as $termId) {
            wp_delete_term($termId, $taxonomy);
        }
    }
}

// Eliminar opciones del plugin.
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpre_%'");

flush_rewrite_rules();
