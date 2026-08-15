<?php
/**
 * Meta box de datos del agente (wpre_agent).
 *
 * @package WPRealEstate\Fields
 */

namespace WPRealEstate\Fields;

use WPRealEstate\PostTypes\AgentType;

defined('ABSPATH') || exit;

class AgentFields
{
    private const NONCE_ACTION = 'wpre_agent_fields';
    private const NONCE_FIELD  = '_wpre_agent_fields_nonce';

    public function register(): void
    {
        add_meta_box(
            'wpre_agent_details',
            __('Datos del Agente', 'wp-real-estate'),
            [$this, 'render'],
            AgentType::SLUG,
            'normal',
            'high'
        );
    }

    public function render(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);
        include WPRE_PATH . 'views/fields-agent.php';
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
            'number' => is_numeric($value) ? (int) $value : '',
            'url'    => esc_url_raw($value),
            'email'  => sanitize_email($value),
            'tel'    => sanitize_text_field($value),
            default  => sanitize_text_field($value),
        };
    }

    /**
     * Definicion de todos los campos meta del agente, agrupados para la vista.
     */
    public function schema(): array
    {
        return [
            '_wpre_agent_phone' => [
                'label' => __('Teléfono', 'wp-real-estate'),
                'type'  => 'tel',
                'group' => 'contact',
            ],
            '_wpre_agent_phone_secondary' => [
                'label' => __('Teléfono secundario', 'wp-real-estate'),
                'type'  => 'tel',
                'group' => 'contact',
            ],
            '_wpre_agent_email' => [
                'label' => __('Email', 'wp-real-estate'),
                'type'  => 'email',
                'group' => 'contact',
            ],
            '_wpre_agent_license' => [
                'label' => __('Número de licencia', 'wp-real-estate'),
                'type'  => 'text',
                'group' => 'professional',
            ],
            '_wpre_agent_experience_years' => [
                'label' => __('Años de experiencia', 'wp-real-estate'),
                'type'  => 'number',
                'group' => 'professional',
            ],
            '_wpre_agent_languages' => [
                'label'       => __('Idiomas', 'wp-real-estate'),
                'type'        => 'text',
                'group'       => 'professional',
                'placeholder' => __('Ej: Español, Inglés, Francés', 'wp-real-estate'),
            ],
            '_wpre_agent_position' => [
                'label'       => __('Cargo', 'wp-real-estate'),
                'type'        => 'text',
                'group'       => 'professional',
                'placeholder' => __('Ej: Director Comercial', 'wp-real-estate'),
            ],
            '_wpre_agent_facebook' => [
                'label' => __('Facebook', 'wp-real-estate'),
                'type'  => 'url',
                'group' => 'social',
            ],
            '_wpre_agent_instagram' => [
                'label' => __('Instagram', 'wp-real-estate'),
                'type'  => 'url',
                'group' => 'social',
            ],
            '_wpre_agent_linkedin' => [
                'label' => __('LinkedIn', 'wp-real-estate'),
                'type'  => 'url',
                'group' => 'social',
            ],
            '_wpre_agent_twitter' => [
                'label' => __('Twitter / X', 'wp-real-estate'),
                'type'  => 'url',
                'group' => 'social',
            ],
            '_wpre_agent_whatsapp' => [
                'label'       => __('WhatsApp', 'wp-real-estate'),
                'type'        => 'tel',
                'group'       => 'social',
                'placeholder' => __('Ej: +34600000000', 'wp-real-estate'),
            ],
        ];
    }
}
