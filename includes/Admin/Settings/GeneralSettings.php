<?php
/**
 * Pestaña de ajustes: datos generales de la empresa.
 *
 * @package WPRealEstate\Admin\Settings
 */

namespace WPRealEstate\Admin\Settings;

defined('ABSPATH') || exit;

class GeneralSettings
{
    private const OPTION_GROUP = 'wpre_general_group';
    private const PAGE_SLUG    = 'wpre_general';
    private const SECTION      = 'wpre_general_section';

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
            __('Datos de la Empresa', 'wp-real-estate'),
            function () {
                echo '<p>' . esc_html__('Información general de la inmobiliaria.', 'wp-real-estate') . '</p>';
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

        if ($type === 'textarea') {
            printf(
                '<textarea id="%s" name="%s" class="large-text" rows="4">%s</textarea>',
                esc_attr($key),
                esc_attr($key),
                esc_textarea($value)
            );
        } elseif ($type === 'media') {
            $imageUrl = $value ? wp_get_attachment_image_url($value, 'thumbnail') : '';
            ?>
            <div class="wpre-media-field">
                <input type="hidden" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo absint($value); ?>">
                <?php if ($imageUrl) : ?>
                    <img src="<?php echo esc_url($imageUrl); ?>" class="wpre-media-preview" style="max-width:150px;display:block;margin-bottom:8px;">
                <?php endif; ?>
                <button type="button" class="button wpre-media-select" data-field="<?php echo esc_attr($key); ?>">
                    <?php esc_html_e('Seleccionar imagen', 'wp-real-estate'); ?>
                </button>
                <button type="button" class="button wpre-media-remove" data-field="<?php echo esc_attr($key); ?>"
                    <?php echo !$value ? 'style="display:none"' : ''; ?>>
                    <?php esc_html_e('Eliminar', 'wp-real-estate'); ?>
                </button>
            </div>
            <?php
        } else {
            printf(
                '<input type="%s" id="%s" name="%s" value="%s" class="regular-text" placeholder="%s">',
                esc_attr($type),
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
            'wpre_company_name' => [
                'label'   => __('Nombre de la empresa', 'wp-real-estate'),
                'type'    => 'text',
                'default' => '',
            ],
            'wpre_company_description' => [
                'label'    => __('Descripción', 'wp-real-estate'),
                'type'     => 'textarea',
                'default'  => '',
                'sanitize' => 'sanitize_textarea_field',
            ],
            'wpre_company_logo' => [
                'label'        => __('Logo', 'wp-real-estate'),
                'type'         => 'media',
                'default'      => '',
                'setting_type' => 'integer',
                'sanitize'     => 'absint',
            ],
            'wpre_company_phone' => [
                'label'       => __('Teléfono', 'wp-real-estate'),
                'type'        => 'tel',
                'default'     => '',
                'placeholder' => '+34 900 000 000',
            ],
            'wpre_company_email' => [
                'label'    => __('Email de contacto', 'wp-real-estate'),
                'type'     => 'email',
                'default'  => '',
                'sanitize' => 'sanitize_email',
            ],
            'wpre_company_address' => [
                'label'   => __('Dirección', 'wp-real-estate'),
                'type'    => 'text',
                'default' => '',
            ],
            'wpre_company_city' => [
                'label'   => __('Ciudad', 'wp-real-estate'),
                'type'    => 'text',
                'default' => '',
            ],
            'wpre_company_postal_code' => [
                'label'   => __('Código postal', 'wp-real-estate'),
                'type'    => 'text',
                'default' => '',
            ],
            'wpre_company_country' => [
                'label'   => __('País', 'wp-real-estate'),
                'type'    => 'text',
                'default' => 'España',
            ],
            'wpre_company_tax_id' => [
                'label'   => __('CIF/NIF', 'wp-real-estate'),
                'type'    => 'text',
                'default' => '',
            ],
            'wpre_company_license' => [
                'label'       => __('Licencia inmobiliaria', 'wp-real-estate'),
                'type'        => 'text',
                'default'     => '',
                'description' => __('Número de registro oficial de la inmobiliaria.', 'wp-real-estate'),
            ],
        ];
    }
}
