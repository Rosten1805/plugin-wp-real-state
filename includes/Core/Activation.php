<?php
/**
 * Rutinas ejecutadas al activar el plugin.
 *
 * @package WPRealEstate\Core
 */

namespace WPRealEstate\Core;

use WPRealEstate\PostTypes\AgentType;
use WPRealEstate\PostTypes\ListingType;
use WPRealEstate\Taxonomies\AgentTaxonomies;
use WPRealEstate\Taxonomies\ListingTaxonomies;

defined('ABSPATH') || exit;

class Activation
{
    public static function run(): void
    {
        // Registrar CPTs y taxonomias temporalmente para poder hacer flush.
        (new ListingType())->register();
        (new AgentType())->register();
        (new ListingTaxonomies())->register();
        (new AgentTaxonomies())->register();

        self::seedTaxonomyTerms();
        flush_rewrite_rules();

        update_option('wpre_version', WPRE_VERSION);
        self::seedDefaultOptions();
    }

    private static function seedTaxonomyTerms(): void
    {
        $simpleTerms = [
            'listing_type'      => [
                'Apartamento', 'Casa', 'Chalet', 'Duplex', 'Atico',
                'Estudio', 'Local Comercial', 'Oficina', 'Terreno',
                'Nave Industrial', 'Garaje', 'Trastero',
            ],
            'listing_operation' => ['Venta', 'Alquiler', 'Alquiler Vacacional', 'Traspaso'],
            'listing_amenity'   => [
                'Piscina', 'Garaje', 'Ascensor', 'Terraza', 'Balcon', 'Jardin',
                'Trastero', 'Aire Acondicionado', 'Calefaccion', 'Cocina Equipada',
                'Armarios Empotrados', 'Portero', 'Seguridad 24h', 'Gimnasio',
                'Zona Comunitaria', 'Parking', 'Amueblado', 'Mascotas Permitidas',
            ],
            'listing_condition' => [
                'Obra Nueva', 'Segunda Mano', 'En Construccion',
                'Para Reformar', 'Reformado', 'Buen Estado',
            ],
            'energy_rating'     => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'En tramite', 'Exento'],
            'agent_specialty'   => ['Residencial', 'Comercial', 'Industrial', 'Lujo', 'Inversiones', 'Alquiler'],
        ];

        foreach ($simpleTerms as $taxonomy => $terms) {
            foreach ($terms as $term) {
                if (!term_exists($term, $taxonomy)) {
                    wp_insert_term($term, $taxonomy);
                }
            }
        }

        self::seedLocationTerms();
    }

    private static function seedLocationTerms(): void
    {
        $tree = [
            'Madrid' => [
                'Centro'    => ['Sol', 'Malasana', 'Chueca', 'La Latina'],
                'Chamberi'  => ['Trafalgar', 'Rios Rosas'],
                'Salamanca' => ['Recoletos', 'Goya'],
            ],
            'Barcelona' => [
                'Eixample'    => ["Dreta de l'Eixample", "Esquerra de l'Eixample"],
                'Gracia'      => ['Vila de Gracia'],
                'Ciutat Vella' => ['El Born', 'Barrio Gotico'],
            ],
            'Valencia' => [
                'Ciutat Vella' => ['El Carmen'],
                'Ruzafa'       => [],
            ],
            'Sevilla' => [
                'Centro' => ['Santa Cruz'],
            ],
            'Malaga' => [
                'Centro Historico' => [],
            ],
        ];

        foreach ($tree as $city => $districts) {
            $cityId = self::ensureTerm($city, 'listing_location');
            if ($cityId === null) {
                continue;
            }

            foreach ($districts as $district => $zones) {
                $districtId = self::ensureTerm($district, 'listing_location', $cityId);
                if ($districtId === null) {
                    continue;
                }

                foreach ($zones as $zone) {
                    self::ensureTerm($zone, 'listing_location', $districtId);
                }
            }
        }
    }

    private static function ensureTerm(string $name, string $taxonomy, int $parent = 0): ?int
    {
        $existing = term_exists($name, $taxonomy, $parent ?: null);
        if ($existing) {
            return is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
        }

        $inserted = wp_insert_term($name, $taxonomy, $parent ? ['parent' => $parent] : []);
        if (is_wp_error($inserted)) {
            return null;
        }

        return (int) $inserted['term_id'];
    }

    private static function seedDefaultOptions(): void
    {
        $defaults = [
            'wpre_default_currency'  => 'EUR',
            'wpre_measurement_unit'  => 'm2',
            'wpre_listings_per_page' => 12,
            'wpre_reference_prefix'  => 'WPRE-',
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }
}
