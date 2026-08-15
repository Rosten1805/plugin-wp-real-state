<?php
/**
 * Vista del meta box de datos del inmueble.
 *
 * @package WPRealEstate
 * @var WP_Post $post
 */

use WPRealEstate\Fields\ListingFields;
use WPRealEstate\Support\Formatting;

defined('ABSPATH') || exit;

$fieldSet = (new ListingFields())->schema();

$groups = [
    'price'      => __('Precio', 'wp-real-estate'),
    'dimensions' => __('Dimensiones', 'wp-real-estate'),
    'location'   => __('Ubicación', 'wp-real-estate'),
    'reference'  => __('Referencias', 'wp-real-estate'),
    'status'     => __('Estado', 'wp-real-estate'),
    'media'      => __('Multimedia', 'wp-real-estate'),
    'agent'      => __('Agente', 'wp-real-estate'),
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
                    $inputType = $config['type'];
                    $placeholder = $config['placeholder'] ?? '';

                    $options = [];
                    if ($inputType === 'select') {
                        $options = ($config['options'] ?? '') === '__agents__'
                            ? Formatting::agentSelectOptions()
                            : ($config['options'] ?? []);
                    }
                    ?>
                    <div class="wpre-field wpre-field--<?php echo esc_attr($inputType); ?>">
                        <label for="<?php echo esc_attr($key); ?>">
                            <?php echo esc_html($config['label']); ?>
                        </label>

                        <?php if ($inputType === 'select') : ?>
                            <select id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>">
                                <?php foreach ($options as $optValue => $optLabel) : ?>
                                    <option value="<?php echo esc_attr($optValue); ?>" <?php selected($value, $optValue); ?>>
                                        <?php echo esc_html($optLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($inputType === 'gallery') : ?>
                            <div class="wpre-gallery" id="<?php echo esc_attr($key); ?>-wrapper">
                                <div class="wpre-gallery__preview">
                                    <?php
                                    $galleryIds = is_array($value) ? $value : [];
                                    foreach ($galleryIds as $imageId) :
                                        $imageUrl = wp_get_attachment_image_url($imageId, 'thumbnail');
                                        if ($imageUrl) :
                                            ?>
                                            <div class="wpre-gallery__item" data-id="<?php echo absint($imageId); ?>">
                                                <img src="<?php echo esc_url($imageUrl); ?>" alt="">
                                                <button type="button" class="wpre-gallery__remove">&times;</button>
                                                <input type="hidden" name="<?php echo esc_attr($key); ?>[]" value="<?php echo absint($imageId); ?>">
                                            </div>
                                        <?php endif;
                                    endforeach; ?>
                                </div>
                                <button type="button" class="button wpre-gallery__add" data-field="<?php echo esc_attr($key); ?>">
                                    <?php esc_html_e('Añadir imágenes', 'wp-real-estate'); ?>
                                </button>
                            </div>

                        <?php elseif ($inputType === 'file') : ?>
                            <div class="wpre-file">
                                <?php $fileUrl = $value ? wp_get_attachment_url($value) : ''; ?>
                                <input type="hidden" id="<?php echo esc_attr($key); ?>"
                                       name="<?php echo esc_attr($key); ?>"
                                       value="<?php echo absint($value); ?>">
                                <span class="wpre-file__name">
                                    <?php echo $fileUrl ? esc_html(basename($fileUrl)) : ''; ?>
                                </span>
                                <button type="button" class="button wpre-file__select" data-field="<?php echo esc_attr($key); ?>">
                                    <?php esc_html_e('Seleccionar archivo', 'wp-real-estate'); ?>
                                </button>
                                <button type="button" class="button wpre-file__remove" data-field="<?php echo esc_attr($key); ?>"
                                    <?php echo !$value ? 'style="display:none"' : ''; ?>>
                                    <?php esc_html_e('Eliminar', 'wp-real-estate'); ?>
                                </button>
                            </div>

                        <?php else : ?>
                            <input type="<?php echo esc_attr($inputType === 'number' ? 'number' : ($inputType === 'url' ? 'url' : 'text')); ?>"
                                   id="<?php echo esc_attr($key); ?>"
                                   name="<?php echo esc_attr($key); ?>"
                                   value="<?php echo esc_attr($value); ?>"
                                   placeholder="<?php echo esc_attr($placeholder); ?>"
                                   <?php echo isset($config['step']) ? 'step="' . esc_attr($config['step']) . '"' : ''; ?>
                                   class="widefat">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
