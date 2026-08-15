<?php
/**
 * Taxonomias asociadas al CPT de agentes.
 *
 * @package WPRealEstate\Taxonomies
 */

namespace WPRealEstate\Taxonomies;

use WPRealEstate\PostTypes\AgentType;

defined('ABSPATH') || exit;

class AgentTaxonomies
{
    public function register(): void
    {
        register_taxonomy('agent_specialty', AgentType::SLUG, [
            'labels'            => [
                'name'                       => __('Especialidades', 'wp-real-estate'),
                'singular_name'              => __('Especialidad', 'wp-real-estate'),
                'search_items'               => __('Buscar especialidades', 'wp-real-estate'),
                'popular_items'              => __('Especialidades populares', 'wp-real-estate'),
                'all_items'                  => __('Todas las especialidades', 'wp-real-estate'),
                'edit_item'                  => __('Editar especialidad', 'wp-real-estate'),
                'update_item'                => __('Actualizar especialidad', 'wp-real-estate'),
                'add_new_item'               => __('Añadir nueva especialidad', 'wp-real-estate'),
                'new_item_name'              => __('Nuevo nombre de especialidad', 'wp-real-estate'),
                'separate_items_with_commas' => __('Separar con comas', 'wp-real-estate'),
                'add_or_remove_items'        => __('Añadir o eliminar especialidades', 'wp-real-estate'),
                'choose_from_most_used'      => __('Elegir de las más usadas', 'wp-real-estate'),
                'not_found'                  => __('No se encontraron especialidades', 'wp-real-estate'),
                'menu_name'                  => __('Especialidad', 'wp-real-estate'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'especialidad'],
        ]);
    }
}
