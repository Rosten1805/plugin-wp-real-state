<?php
/**
 * Meta box de datos del inmueble (wpre_listing).
 *
 * @package WPRealEstate\Fields
 */

namespace WPRealEstate\Fields;

use WPRealEstate\PostTypes\ListingType;
use WPRealEstate\Support\Formatting;

defined('ABSPATH') || exit;

class ListingFields
{
    private const NONCE_ACTION = 'wpre_listing_fields';
    private const NONCE_FIELD  = '_wpre_listing_fields_nonce';

    public function register(): void
    {
        add_meta_box(
            'wpre_listing_details',
            __('Datos del Inmueble', 'wp-real-estate'),
            [$this, 'render'],
            ListingType::SLUG,
            'normal',
            'high'
        );
    }

    public function render(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);
        include WPRE_PATH . 'views/fields-listing.php';
    }

    public function save(int $postId, \WP_Post $post): void
    {
        if (!$this->userCanSave($postId)) {
            return;
        }

        foreach ($this->schema() as $key => $config) {
            $raw = $_POST[$key] ?? '';
            update_post_meta($postId, $key, $this->sanitize($raw, $config['type']));
        }
    }

    private function userCanSave(int $postId): bool
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        if (!isset($_POST[self::NONCE_FIELD]) || !wp_verify_nonce($_POST[self::NONCE_FIELD], self::NONCE_ACTION)) {
            return false;
        }
        return current_user_can('edit_post', $postId);
    }

    private function sanitize(mixed $value, string $type): mixed
    {
        return match ($type) {
            'number'  => is_numeric($value) ? (float) $value : '',
            'url'     => esc_url_raw($value),
            'email'   => sanitize_email($value),
            'select'  => sanitize_text_field($value),
            'gallery' => is_array($value) ? array_map('absint', $value) : [],
            'file'    => absint($value),
            default   => sanitize_text_field($value),
        };
    }

    /**
     * Definicion de todos los campos meta del inmueble, agrupados para la vista.
     */
    public function schema(): array
    {
        return [
            '_wpre_price' => [
                'label' => __('Precio', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'price',
                'step'  => '0.01',
            ],
            '_wpre_price_before' => [
                'label' => __('Precio anterior', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'price',
                'step'  => '0.01',
            ],
            '_wpre_price_label' => [
                'label'       => __('Etiqueta de precio', 'wp-real-estate'),
                'type'        => 'text',
                'group'       => 'price',
                'placeholder' => __('Ej: Desde, Consultar', 'wp-real-estate'),
            ],
            '_wpre_currency' => [
                'label'   => __('Moneda', 'wp-real-estate'),
                'type'    => 'select',
                'group'   => 'price',
                'options' => Formatting::currencyOptions(),
            ],
            '_wpre_built_area' => [
                'label' => __('Superficie construida (m²)', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'dimensions',
            ],
            '_wpre_usable_area' => [
                'label' => __('Superficie útil (m²)', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'dimensions',
            ],
            '_wpre_plot_area' => [
                'label' => __('Superficie parcela (m²)', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'dimensions',
            ],
            '_wpre_rooms' => [
                'label' => __('Habitaciones', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'dimensions',
            ],
            '_wpre_bathrooms' => [
                'label' => __('Baños', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'dimensions',
            ],
            '_wpre_floors' => [
                'label' => __('Plantas', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'dimensions',
            ],
            '_wpre_floor_number' => [
                'label' => __('Número de piso', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'dimensions',
            ],
            '_wpre_year_built' => [
                'label' => __('Año de construcción', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'dimensions',
            ],
            '_wpre_orientation' => [
                'label'   => __('Orientación', 'wp-real-estate'),
                'type'    => 'select',
                'group'   => 'dimensions',
                'options' => Formatting::orientationOptions(),
            ],
            '_wpre_address' => [
                'label' => __('Dirección', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'location',
            ],
            '_wpre_postal_code' => [
                'label' => __('Código postal', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'location',
            ],
            '_wpre_city' => [
                'label' => __('Ciudad', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'location',
            ],
            '_wpre_latitude' => [
                'label' => __('Latitud', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'location',
            ],
            '_wpre_longitude' => [
                'label' => __('Longitud', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'location',
            ],
            '_wpre_reference' => [
                'label' => __('Referencia', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'reference',
            ],
            '_wpre_cadastral_ref' => [
                'label' => __('Referencia catastral', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'reference',
            ],
            '_wpre_availability' => [
                'label'   => __('Disponibilidad', 'wp-real-estate'),
                'type'    => 'select',
                'group'   => 'status',
                'options' => Formatting::availabilityOptions(),
            ],
            '_wpre_gallery' => [
                'label' => __('Galería de imágenes', 'wp-real-estate'),
                'type'  => 'gallery',
                'group' => 'media',
            ],
            '_wpre_floor_plan' => [
                'label' => __('Plano', 'wp-real-estate'),
                'type'  => 'file',
                'group' => 'media',
            ],
            '_wpre_virtual_tour_url' => [
                'label' => __('URL Tour Virtual', 'wp-real-estate'),
                'type'  => 'url',
                'group' => 'media',
            ],
            '_wpre_video_url' => [
                'label' => __('URL Video', 'wp-real-estate'),
                'type'  => 'url',
                'group' => 'media',
            ],
            '_wpre_agent_id' => [
                'label'   => __('Agente asignado', 'wp-real-estate'),
                'type'    => 'select',
                'group'   => 'agent',
                'options' => '__agents__',
            ],
        ];
    }
}
