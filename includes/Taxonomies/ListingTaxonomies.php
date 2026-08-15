<?php
/**
 * Taxonomias asociadas al CPT de inmuebles.
 *
 * @package WPRealEstate\Taxonomies
 */

namespace WPRealEstate\Taxonomies;

use WPRealEstate\PostTypes\ListingType;

defined('ABSPATH') || exit;

class ListingTaxonomies
{
    public function register(): void
    {
        $this->registerType();
        $this->registerOperation();
        $this->registerLocation();
        $this->registerAmenity();
        $this->registerCondition();
        $this->registerEnergyRating();
    }

    private function registerType(): void
    {
        register_taxonomy('listing_type', ListingType::SLUG, [
            'labels'            => [
                'name'              => __('Tipos de Inmueble', 'wp-real-estate'),
                'singular_name'     => __('Tipo de Inmueble', 'wp-real-estate'),
                'search_items'      => __('Buscar tipos', 'wp-real-estate'),
                'all_items'         => __('Todos los tipos', 'wp-real-estate'),
                'parent_item'       => __('Tipo padre', 'wp-real-estate'),
                'parent_item_colon' => __('Tipo padre:', 'wp-real-estate'),
                'edit_item'         => __('Editar tipo', 'wp-real-estate'),
                'update_item'       => __('Actualizar tipo', 'wp-real-estate'),
                'add_new_item'      => __('Añadir nuevo tipo', 'wp-real-estate'),
                'new_item_name'     => __('Nuevo nombre de tipo', 'wp-real-estate'),
                'menu_name'         => __('Tipo de Inmueble', 'wp-real-estate'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'tipo-inmueble'],
        ]);
    }

    private function registerOperation(): void
    {
        register_taxonomy('listing_operation', ListingType::SLUG, [
            'labels'            => [
                'name'          => __('Tipos de Operación', 'wp-real-estate'),
                'singular_name' => __('Tipo de Operación', 'wp-real-estate'),
                'search_items'  => __('Buscar operaciones', 'wp-real-estate'),
                'all_items'     => __('Todas las operaciones', 'wp-real-estate'),
                'edit_item'     => __('Editar operación', 'wp-real-estate'),
                'update_item'   => __('Actualizar operación', 'wp-real-estate'),
                'add_new_item'  => __('Añadir nueva operación', 'wp-real-estate'),
                'new_item_name' => __('Nuevo nombre de operación', 'wp-real-estate'),
                'menu_name'     => __('Tipo de Operación', 'wp-real-estate'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'operacion'],
        ]);
    }

    private function registerLocation(): void
    {
        register_taxonomy('listing_location', ListingType::SLUG, [
            'labels'            => [
                'name'              => __('Ubicaciones', 'wp-real-estate'),
                'singular_name'     => __('Ubicación', 'wp-real-estate'),
                'search_items'      => __('Buscar ubicaciones', 'wp-real-estate'),
                'all_items'         => __('Todas las ubicaciones', 'wp-real-estate'),
                'parent_item'       => __('Ubicación padre', 'wp-real-estate'),
                'parent_item_colon' => __('Ubicación padre:', 'wp-real-estate'),
                'edit_item'         => __('Editar ubicación', 'wp-real-estate'),
                'update_item'       => __('Actualizar ubicación', 'wp-real-estate'),
                'add_new_item'      => __('Añadir nueva ubicación', 'wp-real-estate'),
                'new_item_name'     => __('Nuevo nombre de ubicación', 'wp-real-estate'),
                'menu_name'         => __('Ubicación', 'wp-real-estate'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'ubicacion'],
        ]);
    }

    private function registerAmenity(): void
    {
        register_taxonomy('listing_amenity', ListingType::SLUG, [
            'labels'            => [
                'name'                       => __('Comodidades', 'wp-real-estate'),
                'singular_name'              => __('Comodidad', 'wp-real-estate'),
                'search_items'               => __('Buscar comodidades', 'wp-real-estate'),
                'popular_items'              => __('Comodidades populares', 'wp-real-estate'),
                'all_items'                  => __('Todas las comodidades', 'wp-real-estate'),
                'edit_item'                  => __('Editar comodidad', 'wp-real-estate'),
                'update_item'                => __('Actualizar comodidad', 'wp-real-estate'),
                'add_new_item'               => __('Añadir nueva comodidad', 'wp-real-estate'),
                'new_item_name'              => __('Nuevo nombre de comodidad', 'wp-real-estate'),
                'separate_items_with_commas' => __('Separar con comas', 'wp-real-estate'),
                'add_or_remove_items'        => __('Añadir o eliminar comodidades', 'wp-real-estate'),
                'choose_from_most_used'      => __('Elegir de las más usadas', 'wp-real-estate'),
                'not_found'                  => __('No se encontraron comodidades', 'wp-real-estate'),
                'menu_name'                  => __('Comodidades', 'wp-real-estate'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => false,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'comodidad'],
        ]);
    }

    private function registerCondition(): void
    {
        register_taxonomy('listing_condition', ListingType::SLUG, [
            'labels'            => [
                'name'          => __('Estado del Inmueble', 'wp-real-estate'),
                'singular_name' => __('Estado', 'wp-real-estate'),
                'search_items'  => __('Buscar estados', 'wp-real-estate'),
                'all_items'     => __('Todos los estados', 'wp-real-estate'),
                'edit_item'     => __('Editar estado', 'wp-real-estate'),
                'update_item'   => __('Actualizar estado', 'wp-real-estate'),
                'add_new_item'  => __('Añadir nuevo estado', 'wp-real-estate'),
                'new_item_name' => __('Nuevo nombre de estado', 'wp-real-estate'),
                'menu_name'     => __('Estado', 'wp-real-estate'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => false,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'estado-inmueble'],
        ]);
    }

    private function registerEnergyRating(): void
    {
        register_taxonomy('energy_rating', ListingType::SLUG, [
            'labels'            => [
                'name'          => __('Certificado Energético', 'wp-real-estate'),
                'singular_name' => __('Certificado', 'wp-real-estate'),
                'search_items'  => __('Buscar certificados', 'wp-real-estate'),
                'all_items'     => __('Todos los certificados', 'wp-real-estate'),
                'edit_item'     => __('Editar certificado', 'wp-real-estate'),
                'update_item'   => __('Actualizar certificado', 'wp-real-estate'),
                'add_new_item'  => __('Añadir nuevo certificado', 'wp-real-estate'),
                'new_item_name' => __('Nuevo nombre de certificado', 'wp-real-estate'),
                'menu_name'     => __('Cert. Energético', 'wp-real-estate'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => false,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'certificado-energetico'],
        ]);
    }
}
