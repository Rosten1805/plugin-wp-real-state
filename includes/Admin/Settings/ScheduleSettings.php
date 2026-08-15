<?php
/**
 * Pestaña de ajustes: horario de atención.
 *
 * @package WPRealEstate\Admin\Settings
 */

namespace WPRealEstate\Admin\Settings;

defined('ABSPATH') || exit;

class ScheduleSettings
{
    private const OPTION_GROUP = 'wpre_schedule_group';
    private const PAGE_SLUG    = 'wpre_schedule';
    private const SECTION      = 'wpre_schedule_section';

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
            __('Horario de Atención', 'wp-real-estate'),
            function () {
                echo '<p>' . esc_html__('Configura el horario de atención al público.', 'wp-real-estate') . '</p>';
            },
            self::PAGE_SLUG
        );

        foreach ($this->fields() as $key => $config) {
            register_setting(self::OPTION_GROUP, $key, [
                'type'              => 'string',
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

        if ($type === 'time') {
            printf('<input type="time" id="%s" name="%s" value="%s">', esc_attr($key), esc_attr($key), esc_attr($value));
        } elseif ($type === 'textarea') {
            printf(
                '<textarea id="%s" name="%s" class="large-text" rows="3">%s</textarea>',
                esc_attr($key),
                esc_attr($key),
                esc_textarea($value)
            );
        } else {
            printf('<input type="text" id="%s" name="%s" value="%s" class="regular-text">', esc_attr($key), esc_attr($key), esc_attr($value));
        }

        if (!empty($config['description'])) {
            printf('<p class="description">%s</p>', esc_html($config['description']));
        }
    }

    private function fields(): array
    {
        return [
            'wpre_schedule_weekdays_start' => [
                'label'   => __('Lunes a Viernes — Apertura', 'wp-real-estate'),
                'type'    => 'time',
                'default' => '09:00',
            ],
            'wpre_schedule_weekdays_end' => [
                'label'   => __('Lunes a Viernes — Cierre', 'wp-real-estate'),
                'type'    => 'time',
                'default' => '19:00',
            ],
            'wpre_schedule_saturday_start' => [
                'label'   => __('Sábado — Apertura', 'wp-real-estate'),
                'type'    => 'time',
                'default' => '10:00',
            ],
            'wpre_schedule_saturday_end' => [
                'label'   => __('Sábado — Cierre', 'wp-real-estate'),
                'type'    => 'time',
                'default' => '14:00',
            ],
            'wpre_schedule_sunday_start' => [
                'label'       => __('Domingo — Apertura', 'wp-real-estate'),
                'type'        => 'time',
                'default'     => '',
                'description' => __('Dejar vacío si no se abre los domingos.', 'wp-real-estate'),
            ],
            'wpre_schedule_sunday_end' => [
                'label'   => __('Domingo — Cierre', 'wp-real-estate'),
                'type'    => 'time',
                'default' => '',
            ],
            'wpre_schedule_notes' => [
                'label'       => __('Texto personalizado', 'wp-real-estate'),
                'type'        => 'textarea',
                'default'     => '',
                'sanitize'    => 'sanitize_textarea_field',
                'description' => __('Texto adicional sobre el horario (festivos, verano, etc.).', 'wp-real-estate'),
            ],
        ];
    }
}
