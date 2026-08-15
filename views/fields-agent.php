<?php
/**
 * Vista del meta box de datos del agente.
 *
 * @package WPRealEstate
 * @var WP_Post $post
 */

use WPRealEstate\Fields\AgentFields;

defined('ABSPATH') || exit;

$fieldSet = (new AgentFields())->schema();

$groups = [
    'contact'      => __('Contacto', 'wp-real-estate'),
    'professional' => __('Información Profesional', 'wp-real-estate'),
    'social'       => __('Redes Sociales', 'wp-real-estate'),
];
?>
<div class="wpre-fields">
    <?php foreach ($groups as $groupKey => $groupLabel) : ?>
        <div class="wpre-fields__group">
            <h4 class="wpre-fields__group-title"><?php echo esc_html($groupLabel); ?></h4>
            <div class="wpre-fields__grid">
                <?php
                foreach ($fieldSet as $key => $config) :
                    if ($config['group'] !== $groupKey) {
                        continue;
                    }
                    $value = get_post_meta($post->ID, $key, true);
                    $htmlType = match ($config['type']) {
                        'tel'    => 'tel',
                        'email'  => 'email',
                        'url'    => 'url',
                        'number' => 'number',
                        default  => 'text',
                    };
                    ?>
                    <div class="wpre-field">
                        <label for="<?php echo esc_attr($key); ?>">
                            <?php echo esc_html($config['label']); ?>
                        </label>
                        <input type="<?php echo esc_attr($htmlType); ?>"
                               id="<?php echo esc_attr($key); ?>"
                               name="<?php echo esc_attr($key); ?>"
                               value="<?php echo esc_attr($value); ?>"
                               placeholder="<?php echo esc_attr($config['placeholder'] ?? ''); ?>"
                               class="widefat">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
