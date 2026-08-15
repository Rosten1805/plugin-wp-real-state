<?php
/**
 * Rutinas ejecutadas al desactivar el plugin.
 *
 * @package WPRealEstate\Core
 */

namespace WPRealEstate\Core;

defined('ABSPATH') || exit;

class Deactivation
{
    public static function run(): void
    {
        flush_rewrite_rules();
    }
}
