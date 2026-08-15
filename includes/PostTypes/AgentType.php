<?php
/**
 * Custom Post Type: Agente (wpre_agent).
 *
 * @package WPRealEstate\PostTypes
 */

namespace WPRealEstate\PostTypes;

defined('ABSPATH') || exit;

class AgentType
{
    public const SLUG = 'wpre_agent';

    public function register(): void
    {
        register_post_type(self::SLUG, [
            'labels'             => $this->labels(),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'agentes', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => ['title', 'editor', 'thumbnail'],
            'show_in_rest'       => true,
        ]);
    }

    private function labels(): array
    {
        return [
            'name'                  => __('Agentes', 'wp-real-estate'),
            'singular_name'         => __('Agente', 'wp-real-estate'),
            'menu_name'             => __('Agentes', 'wp-real-estate'),
            'name_admin_bar'        => __('Agente', 'wp-real-estate'),
            'add_new'               => __('Añadir nuevo', 'wp-real-estate'),
            'add_new_item'          => __('Añadir nuevo agente', 'wp-real-estate'),
            'new_item'              => __('Nuevo agente', 'wp-real-estate'),
            'edit_item'             => __('Editar agente', 'wp-real-estate'),
            'view_item'             => __('Ver agente', 'wp-real-estate'),
            'all_items'             => __('Todos los agentes', 'wp-real-estate'),
            'search_items'          => __('Buscar agentes', 'wp-real-estate'),
            'not_found'             => __('No se encontraron agentes.', 'wp-real-estate'),
            'not_found_in_trash'    => __('No hay agentes en la papelera.', 'wp-real-estate'),
            'featured_image'        => __('Foto del agente', 'wp-real-estate'),
            'set_featured_image'    => __('Establecer foto del agente', 'wp-real-estate'),
            'remove_featured_image' => __('Eliminar foto del agente', 'wp-real-estate'),
            'use_featured_image'    => __('Usar como foto del agente', 'wp-real-estate'),
        ];
    }
}
