<?php
/**
 * Plugin Name: WP Real Estate
 * Plugin URI:  https://example.com/wp-real-estate
 * Description: Gestión de inmuebles y agentes para WordPress: fichas de inmuebles, directorio de agentes, taxonomías configurables y generador de contenido de demostración.
 * Version:     1.0.0
 * Author:      Your Name Here
 * Author URI:  https://example.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-real-estate
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package WPRealEstate
 */

defined('ABSPATH') || exit;

// Constantes del plugin.
define('WPRE_VERSION', '1.0.0');
define('WPRE_PATH', plugin_dir_path(__FILE__));
define('WPRE_URL', plugin_dir_url(__FILE__));
define('WPRE_BASENAME', plugin_basename(__FILE__));
define('WPRE_SLUG', 'wp-real-estate');

// Comprobación de versión mínima de PHP.
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function () {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('WP Real Estate requiere PHP 8.0 o superior.', 'wp-real-estate')
        );
    });
    return;
}

// Autoload vía Composer si existe, con fallback PSR-4 manual.
if (file_exists(WPRE_PATH . 'vendor/autoload.php')) {
    require_once WPRE_PATH . 'vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'WPRealEstate\\';
        $base_dir = WPRE_PATH . 'includes/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
}

register_activation_hook(__FILE__, ['WPRealEstate\\Core\\Activation', 'run']);
register_deactivation_hook(__FILE__, ['WPRealEstate\\Core\\Deactivation', 'run']);

add_action('plugins_loaded', function (): void {
    \WPRealEstate\Core\Bootstrap::boot();
});
