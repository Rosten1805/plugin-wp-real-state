<?php
/**
 * Genera inmuebles de demostración con datos realistas.
 *
 * @package WPRealEstate\DemoData
 */

namespace WPRealEstate\DemoData;

use WPRealEstate\PostTypes\ListingType;

defined('ABSPATH') || exit;

class ListingGenerator
{
    /** @var int[] */
    private array $agentIds;

    private int $counter;

    private UnsplashClient $photos;

    public function __construct(array $agentIds, int $counter = 0)
    {
        $this->agentIds = $agentIds;
        $this->counter = $counter;
        $this->photos = new UnsplashClient();
    }

    /**
     * Genera un lote de inmuebles de un tipo con las operaciones dadas.
     *
     * @param string   $type       Tipo de inmueble
     * @param string[] $operations Operaciones para cada inmueble
     * @return array{created: int, counter: int, titles: string[]}
     */
    public function generateBatch(string $type, array $operations): array
    {
        $created = 0;
        $titles = [];

        foreach ($operations as $operation) {
            $title = $this->createListing($type, $operation);
            if ($title !== null) {
                $created++;
                $titles[] = $title;
            }
        }

        return ['created' => $created, 'counter' => $this->counter, 'titles' => $titles];
    }

    private function createListing(string $type, string $operation): ?string
    {
        $this->counter++;
        $reference = sprintf('%s%03d', get_option('wpre_reference_prefix', 'WPRE-'), $this->counter);
        $location = $this->randomLocation();
        $data = $this->buildListingData($type, $operation, $location);

        $postId = wp_insert_post([
            'post_type'    => ListingType::SLUG,
            'post_title'   => $data['title'],
            'post_content' => $data['content'],
            'post_excerpt' => $data['excerpt'],
            'post_status'  => 'publish',
        ]);

        if (is_wp_error($postId)) {
            return null;
        }

        update_post_meta($postId, '_wpre_demo_content', '1');

        wp_set_object_terms($postId, $type, 'listing_type');
        wp_set_object_terms($postId, $operation, 'listing_operation');
        wp_set_object_terms($postId, $location['terms'], 'listing_location');
        wp_set_object_terms($postId, $data['amenities'], 'listing_amenity');
        wp_set_object_terms($postId, $data['condition'], 'listing_condition');
        wp_set_object_terms($postId, $data['energy_rating'], 'energy_rating');

        $imageIds = $this->photos->importListingPhotos($this->imageQuery($type), rand(3, 5), $postId);
        $galleryIds = [];
        if (!empty($imageIds)) {
            set_post_thumbnail($postId, $imageIds[0]);
            $galleryIds = array_slice($imageIds, 1);
        }

        $agentId = $this->agentIds[array_rand($this->agentIds)] ?? 0;

        $meta = [
            '_wpre_price'            => $data['price'],
            '_wpre_price_before'     => $data['price_before'],
            '_wpre_price_label'      => '',
            '_wpre_currency'         => 'EUR',
            '_wpre_built_area'       => $data['built_area'],
            '_wpre_usable_area'      => (int) ($data['built_area'] * 0.85),
            '_wpre_plot_area'        => $data['plot_area'],
            '_wpre_rooms'            => $data['rooms'],
            '_wpre_bathrooms'        => $data['bathrooms'],
            '_wpre_floors'           => $data['floors'],
            '_wpre_floor_number'     => $data['floor_number'],
            '_wpre_year_built'       => $data['year_built'],
            '_wpre_orientation'      => $data['orientation'],
            '_wpre_address'          => $data['address'],
            '_wpre_postal_code'      => $location['postal_code'],
            '_wpre_city'             => $location['city'],
            '_wpre_latitude'         => $location['lat'],
            '_wpre_longitude'        => $location['lng'],
            '_wpre_reference'        => $reference,
            '_wpre_cadastral_ref'    => strtoupper(substr(md5($reference), 0, 14)),
            '_wpre_availability'     => $this->randomAvailability(),
            '_wpre_gallery'          => $galleryIds,
            '_wpre_floor_plan'       => '',
            '_wpre_virtual_tour_url' => '',
            '_wpre_video_url'        => '',
            '_wpre_agent_id'         => $agentId,
        ];

        foreach ($meta as $key => $value) {
            update_post_meta($postId, $key, $value);
        }

        return $data['title'];
    }

    private function buildListingData(string $type, string $operation, array $location): array
    {
        $profile = $this->typeProfile($type);
        $rooms = rand($profile['rooms_min'], $profile['rooms_max']);
        $bathrooms = max(1, (int) ceil($rooms / 2));
        $builtArea = rand($profile['area_min'], $profile['area_max']);
        $price = $this->calculatePrice($type, $operation, $location['city'], $builtArea);
        $yearBuilt = rand($profile['year_min'], $profile['year_max']);

        $orientations = ['norte', 'sur', 'este', 'oeste', 'norte-sur', 'este-oeste'];
        $conditions = ['Obra Nueva', 'Segunda Mano', 'Reformado', 'Buen Estado'];
        $energyRatings = ['A', 'B', 'C', 'D', 'E', 'F'];

        if ($yearBuilt >= 2020) {
            $conditions = ['Obra Nueva', 'Buen Estado'];
            $energyRatings = ['A', 'B', 'C'];
        }

        $coreAmenities = ['Ascensor', 'Terraza', 'Balcón', 'Aire Acondicionado', 'Calefacción', 'Cocina Equipada', 'Armarios Empotrados', 'Trastero'];
        $extraAmenities = ['Piscina', 'Garaje', 'Jardín', 'Portero', 'Seguridad 24h', 'Gimnasio', 'Zona Comunitaria', 'Parking', 'Amueblado'];

        shuffle($coreAmenities);
        shuffle($extraAmenities);
        $amenities = array_merge(
            array_slice($coreAmenities, 0, rand(2, 4)),
            array_slice($extraAmenities, 0, rand(1, 3))
        );

        $operationLabel = match ($operation) {
            'Alquiler' => 'en alquiler',
            'Alquiler Vacacional' => 'vacacional',
            default => 'en venta',
        };

        return [
            'title'         => $this->buildTitle($type, $location, $operationLabel, $rooms, $builtArea),
            'content'       => $this->buildDescription($type, $rooms, $bathrooms, $builtArea, $location, $amenities),
            'excerpt'       => sprintf('%s de %d m² con %d habitaciones en %s.', $type, $builtArea, $rooms, $location['zone']),
            'price'         => $price,
            'price_before'  => rand(0, 3) === 0 ? (int) ($price * 1.1) : '',
            'built_area'    => $builtArea,
            'plot_area'     => in_array($type, ['Casa', 'Chalet', 'Terreno']) ? rand($builtArea, $builtArea * 3) : 0,
            'rooms'         => $rooms,
            'bathrooms'     => $bathrooms,
            'floors'        => in_array($type, ['Casa', 'Chalet', 'Dúplex']) ? rand(2, 3) : 1,
            'floor_number'  => in_array($type, ['Apartamento', 'Ático', 'Estudio', 'Oficina']) ? (string) rand(1, 8) : '',
            'year_built'    => $yearBuilt,
            'orientation'   => $orientations[array_rand($orientations)],
            'address'       => $this->buildAddress($location),
            'amenities'     => $amenities,
            'condition'     => $conditions[array_rand($conditions)],
            'energy_rating' => $energyRatings[array_rand($energyRatings)],
        ];
    }

    private function buildTitle(string $type, array $location, string $operationLabel, int $rooms, int $area): string
    {
        $adjectives = ['Amplio', 'Luminoso', 'Acogedor', 'Moderno', 'Elegante', 'Espectacular', 'Magnífico', 'Bonito', 'Céntrico', 'Exclusivo'];
        $adjective = $adjectives[array_rand($adjectives)];

        $templates = [
            "$adjective $type $operationLabel en {$location['zone']}, {$location['city']}",
            "$type de $rooms habitaciones en {$location['zone']}",
            "$adjective $type de {$area}m² en {$location['district']}, {$location['city']}",
            "$type $operationLabel en {$location['district']} — {$rooms} hab. {$area}m²",
        ];

        return $templates[array_rand($templates)];
    }

    private function buildDescription(string $type, int $rooms, int $bathrooms, int $area, array $location, array $amenities): string
    {
        $amenitiesText = implode(', ', array_slice($amenities, 0, -1)) . ' y ' . end($amenities);

        $paragraphs = [
            sprintf(
                'Presentamos este magnífico %s de %d metros cuadrados situado en una de las mejores zonas de %s, concretamente en %s. La propiedad cuenta con %d habitaciones y %d baños, distribuidos de manera funcional para aprovechar al máximo cada espacio.',
                strtolower($type),
                $area,
                $location['city'],
                $location['zone'],
                $rooms,
                $bathrooms
            ),
            sprintf(
                'Entre las características más destacadas encontramos: %s. La vivienda ha sido cuidada con esmero y se encuentra en excelente estado de conservación, lista para entrar a vivir.',
                $amenitiesText
            ),
            sprintf(
                'La ubicación es inmejorable, en el barrio de %s, con todos los servicios a su alrededor: transporte público, colegios, supermercados, parques y zonas de ocio. Una oportunidad única para quienes buscan calidad de vida en %s.',
                $location['zone'],
                $location['city']
            ),
            'No dude en contactarnos para concertar una visita. Nuestro equipo de profesionales estará encantado de atenderle y resolver todas sus dudas sobre esta propiedad.',
        ];

        return implode("\n\n", $paragraphs);
    }

    private function calculatePrice(string $type, string $operation, string $city, int $area): int
    {
        $cityMultiplier = match ($city) {
            'Madrid'    => 4500,
            'Barcelona' => 4200,
            'Valencia'  => 2200,
            'Sevilla'   => 2000,
            'Málaga'    => 2800,
            default     => 2000,
        };

        $typeMultiplier = match ($type) {
            'Ático', 'Chalet' => 1.4,
            'Dúplex' => 1.2,
            'Apartamento', 'Casa' => 1.0,
            'Estudio' => 0.9,
            'Local Comercial', 'Oficina' => 0.8,
            'Nave Industrial' => 0.4,
            'Terreno' => 0.3,
            default => 1.0,
        };

        $basePrice = (int) ($area * $cityMultiplier * $typeMultiplier);

        if ($operation === 'Alquiler') {
            return (int) ($basePrice * 0.004);
        }
        if ($operation === 'Alquiler Vacacional') {
            return (int) ($basePrice * 0.001);
        }

        $variation = rand(-15, 15) / 100;
        return (int) ($basePrice * (1 + $variation));
    }

    private function buildAddress(array $location): string
    {
        $streets = [
            'Calle Mayor', 'Calle del Prado', 'Avenida de la Constitución', 'Paseo de la Castellana',
            'Calle Gran Vía', 'Calle Serrano', 'Avenida Diagonal', 'Calle Valencia',
            'Paseo de Gracia', 'Rambla de Catalunya', 'Calle Alcalá', 'Calle Fuencarral',
            'Calle Princesa', 'Calle Atocha', 'Calle Velázquez', 'Calle Goya',
            'Calle Santa María', 'Avenida de Andalucía', 'Calle San Fernando', 'Calle Larios',
        ];

        return sprintf('%s, %d', $streets[array_rand($streets)], rand(1, 120));
    }

    private function randomLocation(): array
    {
        $locations = [
            ['city' => 'Madrid', 'district' => 'Centro', 'zone' => 'Sol', 'lat' => '40.4168', 'lng' => '-3.7038', 'postal_code' => '28013'],
            ['city' => 'Madrid', 'district' => 'Centro', 'zone' => 'Malasaña', 'lat' => '40.4264', 'lng' => '-3.7053', 'postal_code' => '28004'],
            ['city' => 'Madrid', 'district' => 'Centro', 'zone' => 'Chueca', 'lat' => '40.4224', 'lng' => '-3.6978', 'postal_code' => '28004'],
            ['city' => 'Madrid', 'district' => 'Centro', 'zone' => 'La Latina', 'lat' => '40.4117', 'lng' => '-3.7122', 'postal_code' => '28005'],
            ['city' => 'Madrid', 'district' => 'Chamberí', 'zone' => 'Trafalgar', 'lat' => '40.4330', 'lng' => '-3.7020', 'postal_code' => '28010'],
            ['city' => 'Madrid', 'district' => 'Chamberí', 'zone' => 'Ríos Rosas', 'lat' => '40.4410', 'lng' => '-3.6970', 'postal_code' => '28003'],
            ['city' => 'Madrid', 'district' => 'Salamanca', 'zone' => 'Recoletos', 'lat' => '40.4230', 'lng' => '-3.6880', 'postal_code' => '28001'],
            ['city' => 'Madrid', 'district' => 'Salamanca', 'zone' => 'Goya', 'lat' => '40.4250', 'lng' => '-3.6780', 'postal_code' => '28001'],
            ['city' => 'Barcelona', 'district' => 'Eixample', 'zone' => "Dreta de l'Eixample", 'lat' => '41.3917', 'lng' => '2.1700', 'postal_code' => '08009'],
            ['city' => 'Barcelona', 'district' => 'Eixample', 'zone' => "Esquerra de l'Eixample", 'lat' => '41.3870', 'lng' => '2.1540', 'postal_code' => '08011'],
            ['city' => 'Barcelona', 'district' => 'Gràcia', 'zone' => 'Vila de Gràcia', 'lat' => '41.4016', 'lng' => '2.1568', 'postal_code' => '08012'],
            ['city' => 'Barcelona', 'district' => 'Ciutat Vella', 'zone' => 'El Born', 'lat' => '41.3851', 'lng' => '2.1826', 'postal_code' => '08003'],
            ['city' => 'Barcelona', 'district' => 'Ciutat Vella', 'zone' => 'Barrio Gótico', 'lat' => '41.3827', 'lng' => '2.1767', 'postal_code' => '08002'],
            ['city' => 'Valencia', 'district' => 'Ciutat Vella', 'zone' => 'El Carmen', 'lat' => '39.4785', 'lng' => '-0.3810', 'postal_code' => '46003'],
            ['city' => 'Valencia', 'district' => 'Ruzafa', 'zone' => 'Ruzafa', 'lat' => '39.4610', 'lng' => '-0.3730', 'postal_code' => '46006'],
            ['city' => 'Sevilla', 'district' => 'Centro', 'zone' => 'Santa Cruz', 'lat' => '37.3861', 'lng' => '-5.9885', 'postal_code' => '41004'],
            ['city' => 'Málaga', 'district' => 'Centro Histórico', 'zone' => 'Centro Histórico', 'lat' => '36.7213', 'lng' => '-4.4214', 'postal_code' => '29015'],
        ];

        $location = $locations[array_rand($locations)];
        $location['terms'] = [$location['city'], $location['district'], $location['zone']];

        return $location;
    }

    private function randomAvailability(): string
    {
        $roll = rand(1, 100);
        if ($roll <= 80) {
            return 'disponible';
        }
        if ($roll <= 90) {
            return 'reservada';
        }
        return rand(0, 1) ? 'vendida' : 'alquilada';
    }

    private function typeProfile(string $type): array
    {
        return match ($type) {
            'Apartamento'     => ['rooms_min' => 1, 'rooms_max' => 4, 'area_min' => 45, 'area_max' => 150, 'year_min' => 1970, 'year_max' => 2025],
            'Casa'            => ['rooms_min' => 3, 'rooms_max' => 6, 'area_min' => 120, 'area_max' => 350, 'year_min' => 1960, 'year_max' => 2024],
            'Chalet'          => ['rooms_min' => 4, 'rooms_max' => 7, 'area_min' => 200, 'area_max' => 600, 'year_min' => 1980, 'year_max' => 2025],
            'Dúplex'          => ['rooms_min' => 3, 'rooms_max' => 5, 'area_min' => 100, 'area_max' => 250, 'year_min' => 1990, 'year_max' => 2025],
            'Ático'           => ['rooms_min' => 2, 'rooms_max' => 4, 'area_min' => 80, 'area_max' => 200, 'year_min' => 1985, 'year_max' => 2025],
            'Estudio'         => ['rooms_min' => 1, 'rooms_max' => 1, 'area_min' => 25, 'area_max' => 50, 'year_min' => 1980, 'year_max' => 2025],
            'Local Comercial' => ['rooms_min' => 0, 'rooms_max' => 2, 'area_min' => 50, 'area_max' => 300, 'year_min' => 1970, 'year_max' => 2020],
            'Oficina'         => ['rooms_min' => 0, 'rooms_max' => 3, 'area_min' => 40, 'area_max' => 200, 'year_min' => 1980, 'year_max' => 2025],
            'Terreno'         => ['rooms_min' => 0, 'rooms_max' => 0, 'area_min' => 200, 'area_max' => 5000, 'year_min' => 2020, 'year_max' => 2025],
            'Nave Industrial' => ['rooms_min' => 0, 'rooms_max' => 1, 'area_min' => 300, 'area_max' => 2000, 'year_min' => 1990, 'year_max' => 2020],
            default           => ['rooms_min' => 1, 'rooms_max' => 3, 'area_min' => 50, 'area_max' => 150, 'year_min' => 1980, 'year_max' => 2025],
        };
    }

    private function imageQuery(string $type): string
    {
        return match ($type) {
            'Apartamento'     => 'modern apartment interior living room',
            'Casa'            => 'house exterior residential architecture',
            'Chalet'          => 'luxury villa exterior pool garden',
            'Dúplex'          => 'duplex apartment modern interior',
            'Ático'           => 'penthouse terrace city views',
            'Estudio'         => 'studio apartment modern small',
            'Local Comercial' => 'commercial retail space storefront',
            'Oficina'         => 'modern office space interior',
            'Terreno'         => 'land plot empty terrain',
            'Nave Industrial' => 'warehouse industrial building',
            default           => 'real estate property interior',
        };
    }
}
