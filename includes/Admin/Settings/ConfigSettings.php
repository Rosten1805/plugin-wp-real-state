<?php
/**
 * Pestaña de ajustes: configuración general (moneda, unidades, servicios externos).
 *
 * @package WPRealEstate\Admin\Settings
 */

namespace WPRealEstate\Admin\Settings;

defined('ABSPATH') || exit;

class ConfigSettings
{
    private const OPTION_GROUP = 'wpre_config_group';
    private const PAGE_SLUG    = 'wpre_config';
    private const SECTION      = 'wpre_config_section';

    public function getOptionGroup(): string
    {
        return self::OPTION_GROUP;
    }

    public function getPageSlug(): string
    {
        return self::PAGE_SLUG;
    }

    public function register(): void
    {
        add_settings_section(
            self::SECTION,
            __('Configuración General', 'wp-real-estate'),
            function () {
                echo '<p>' . esc_html__('Ajustes de moneda, unidades y servicios externos.', 'wp-real-estate') . '</p>';
            },
            self::PAGE_SLUG
        );

        foreach ($this->fields() as $key => $config) {
            register_setting(self::OPTION_GROUP, $key, [
                'type'              => $config['setting_type'] ?? 'string',
                'sanitize_callback' => $config['sanitize'] ?? 'sanitize_text_field',
                'default'           => $config['default'] ?? '',
            ]);

            add_settings_field($key, $config['label'], [$this, 'renderField'], self::PAGE_SLUG, self::SECTION, [
                'key'    => $key,
                'config' => $config,
            ]);
        }
    }

    public function renderField(array $args): void
    {
        $key = $args['key'];
        $config = $args['config'];
        $value = get_option($key, $config['default'] ?? '');
        $type = $config['type'] ?? 'text';

        if ($type === 'select') {
            printf('<select id="%s" name="%s">', esc_attr($key), esc_attr($key));
            foreach ($config['options'] as $optValue => $optLabel) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($optValue),
                    selected($value, $optValue, false),
                    esc_html($optLabel)
                );
            }
            echo '</select>';
        } else {
            printf(
                '<input type="%s" id="%s" name="%s" value="%s" class="regular-text" placeholder="%s">',
                esc_attr($type === 'number' ? 'number' : 'text'),
                esc_attr($key),
                esc_attr($key),
                esc_attr($value),
                esc_attr($config['placeholder'] ?? '')
            );
        }

        if (!empty($config['description'])) {
            printf('<p class="description">%s</p>', esc_html($config['description']));
        }
    }

    private function fields(): array
    {
        return [
            'wpre_default_currency' => [
                'label'   => __('Moneda por defecto', 'wp-real-estate'),
                'type'    => 'select',
                'default' => 'EUR',
                'options' => [
                    'EUR' => __('Euro (€)', 'wp-real-estate'),
                    'USD' => __('Dólar ($)', 'wp-real-estate'),
                    'GBP' => __('Libra (£)', 'wp-real-estate'),
                ],
            ],
            'wpre_measurement_unit' => [
                'label'   => __('Unidad de medida', 'wp-real-estate'),
                'type'    => 'select',
                'default' => 'm2',
                'options' => [
                    'm2'  => __('Metros cuadrados (m²)', 'wp-real-estate'),
                    'ft2' => __('Pies cuadrados (ft²)', 'wp-real-estate'),
                    'ha'  => __('Hectáreas (ha)', 'wp-real-estate'),
                ],
            ],
            'wpre_listings_per_page' => [
                'label'        => __('Inmuebles por página', 'wp-real-estate'),
                'type'         => 'number',
                'default'      => 12,
                'setting_type' => 'integer',
                'sanitize'     => 'absint',
            ],
            'wpre_google_maps_key' => [
                'label'       => __('Google Maps API Key', 'wp-real-estate'),
                'type'        => 'text',
                'default'     => '',
                'description' => __('Necesaria para mostrar mapas en las fichas de inmuebles.', 'wp-real-estate'),
            ],
            'wpre_reference_prefix' => [
                'label'       => __('Prefijo de referencia', 'wp-real-estate'),
                'type'        => 'text',
                'default'     => 'WPRE-',
                'description' => __('Prefijo para las referencias de inmuebles (ej: WPRE-001).', 'wp-real-estate'),
            ],
            'wpre_unsplash_key' => [
                'label'       => __('Unsplash Access Key', 'wp-real-estate'),
                'type'        => 'text',
                'default'     => '',
                'placeholder' => 'Tu Access Key de Unsplash',
                'description' => __('Necesaria para descargar imágenes al generar contenido de demostración. Crea una app en unsplash.com/developers y copia el Access Key (no el Secret Key).', 'wp-real-estate'),
            ],
        ];
    }
}
