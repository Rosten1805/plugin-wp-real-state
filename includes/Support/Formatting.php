<?php
/**
 * Utilidades de formato y catalogos de opciones reutilizados por los
 * campos personalizados y las pantallas de administracion.
 *
 * @package WPRealEstate\Support
 */

namespace WPRealEstate\Support;

use WPRealEstate\PostTypes\AgentType;

defined('ABSPATH') || exit;

class Formatting
{
    public static function currencyOptions(): array
    {
        return [
            'EUR' => __('Euro (€)', 'wp-real-estate'),
            'USD' => __('Dólar ($)', 'wp-real-estate'),
            'GBP' => __('Libra (£)', 'wp-real-estate'),
        ];
    }

    public static function currencySymbol(string $currency): string
    {
        $symbols = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'];
        return $symbols[$currency] ?? '€';
    }

    public static function orientationOptions(): array
    {
        return [
            ''           => __('— Seleccionar —', 'wp-real-estate'),
            'norte'      => __('Norte', 'wp-real-estate'),
            'sur'        => __('Sur', 'wp-real-estate'),
            'este'       => __('Este', 'wp-real-estate'),
            'oeste'      => __('Oeste', 'wp-real-estate'),
            'norte-sur'  => __('Norte-Sur', 'wp-real-estate'),
            'este-oeste' => __('Este-Oeste', 'wp-real-estate'),
        ];
    }

    public static function availabilityOptions(): array
    {
        return [
            'disponible' => __('Disponible', 'wp-real-estate'),
            'reservada'  => __('Reservada', 'wp-real-estate'),
            'vendida'    => __('Vendida', 'wp-real-estate'),
            'alquilada'  => __('Alquilada', 'wp-real-estate'),
            'retirada'   => __('Retirada', 'wp-real-estate'),
        ];
    }

    public static function formatPrice(float $price, string $currency = 'EUR'): string
    {
        return number_format($price, 0, ',', '.') . ' ' . self::currencySymbol($currency);
    }

    /**
     * Opciones de agente para un <select>, incluyendo un valor vacio.
     */
    public static function agentSelectOptions(): array
    {
        $agents = get_posts([
            'post_type'      => AgentType::SLUG,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        $options = ['' => __('— Sin agente —', 'wp-real-estate')];
        foreach ($agents as $agent) {
            $options[$agent->ID] = $agent->post_title;
        }

        return $options;
    }
}
