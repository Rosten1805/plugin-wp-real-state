<?php
/**
 * Pestaña de ajustes: redes sociales.
 *
 * @package WPRealEstate\Admin\Settings
 */

namespace WPRealEstate\Admin\Settings;

defined('ABSPATH') || exit;

class SocialSettings
{
    private const OPTION_GROUP = 'wpre_social_group';
    private const PAGE_SLUG    = 'wpre_social';
    private const SECTION      = 'wpre_social_section';

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
            __('Redes Sociales', 'wp-real-estate'),
            function () {
                echo '<p>' . esc_html__('URLs de las redes sociales de la inmobiliaria.', 'wp-real-estate') . '</p>';
            },
            self::PAGE_SLUG
        );

        foreach ($this->fields() as $key => $config) {
            register_setting(self::OPTION_GROUP, $key, [
                'type'              => 'string',
                'sanitize_callback' => $config['sanitize'] ?? 'esc_url_raw',
                'default'           => '',
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
        $value = get_option($key, '');

        printf(
            '<input type="%s" id="%s" name="%s" value="%s" class="regular-text" placeholder="%s">',
            esc_attr($config['type'] ?? 'url'),
            esc_attr($key),
            esc_attr($key),
            esc_attr($value),
            esc_attr($config['placeholder'] ?? '')
        );
    }

    private function fields(): array
    {
        return [
            'wpre_social_facebook' => [
                'label'       => __('Facebook', 'wp-real-estate'),
                'type'        => 'url',
                'placeholder' => 'https://facebook.com/tu-inmobiliaria',
            ],
            'wpre_social_instagram' => [
                'label'       => __('Instagram', 'wp-real-estate'),
                'type'        => 'url',
                'placeholder' => 'https://instagram.com/tu-inmobiliaria',
            ],
            'wpre_social_linkedin' => [
                'label'       => __('LinkedIn', 'wp-real-estate'),
                'type'        => 'url',
                'placeholder' => 'https://linkedin.com/company/tu-inmobiliaria',
            ],
            'wpre_social_twitter' => [
                'label'       => __('Twitter / X', 'wp-real-estate'),
                'type'        => 'url',
                'placeholder' => 'https://x.com/tu-inmobiliaria',
            ],
            'wpre_social_youtube' => [
                'label'       => __('YouTube', 'wp-real-estate'),
                'type'        => 'url',
                'placeholder' => 'https://youtube.com/@tu-inmobiliaria',
            ],
            'wpre_social_whatsapp' => [
                'label'       => __('WhatsApp', 'wp-real-estate'),
                'type'        => 'tel',
                'placeholder' => '+34600000000',
                'sanitize'    => 'sanitize_text_field',
            ],
        ];
    }
}
