<?php
/**
 * Custom Post Type: Inmueble (wpre_listing).
 *
 * @package WPRealEstate\PostTypes
 */

namespace WPRealEstate\PostTypes;

defined('ABSPATH') || exit;

class ListingType
{
    public const SLUG = 'wpre_listing';

    public function register(): void
    {
        register_post_type(self::SLUG, [
            'labels'             => $this->labels(),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'inmuebles', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-admin-home',
            'supports'           => ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'],
            'show_in_rest'       => true,
        ]);
    }

    private function labels(): array
    {
        return [
            'name'                  => __('Inmuebles', 'wp-real-estate'),
            'singular_name'         => __('Inmueble', 'wp-real-estate'),
            'menu_name'             => __('Inmuebles', 'wp-real-estate'),
            'name_admin_bar'        => __('Inmueble', 'wp-real-estate'),
            'add_new'               => __('Añadir nuevo', 'wp-real-estate'),
            'add_new_item'          => __('Añadir nuevo inmueble', 'wp-real-estate'),
            'new_item'              => __('Nuevo inmueble', 'wp-real-estate'),
            'edit_item'             => __('Editar inmueble', 'wp-real-estate'),
            'view_item'             => __('Ver inmueble', 'wp-real-estate'),
            'all_items'             => __('Todos los inmuebles', 'wp-real-estate'),
            'search_items'          => __('Buscar inmuebles', 'wp-real-estate'),
            'parent_item_colon'     => __('Inmueble padre:', 'wp-real-estate'),
            'not_found'             => __('No se encontraron inmuebles.', 'wp-real-estate'),
            'not_found_in_trash'    => __('No hay inmuebles en la papelera.', 'wp-real-estate'),
            'featured_image'        => __('Imagen principal', 'wp-real-estate'),
            'set_featured_image'    => __('Establecer imagen principal', 'wp-real-estate'),
            'remove_featured_image' => __('Eliminar imagen principal', 'wp-real-estate'),
            'use_featured_image'    => __('Usar como imagen principal', 'wp-real-estate'),
            'archives'              => __('Archivo de inmuebles', 'wp-real-estate'),
            'filter_items_list'     => __('Filtrar inmuebles', 'wp-real-estate'),
        ];
    }
}
