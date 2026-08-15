<?php
/**
 * Genera agentes de demostración con perfiles completos.
 *
 * @package WPRealEstate\DemoData
 */

namespace WPRealEstate\DemoData;

use WPRealEstate\PostTypes\AgentType;

defined('ABSPATH') || exit;

class AgentGenerator
{
    private UnsplashClient $photos;

    public function __construct()
    {
        $this->photos = new UnsplashClient();
    }

    /**
     * @return int[] IDs de los agentes creados.
     */
    public function generate(): array
    {
        $ids = [];

        foreach ($this->profiles() as $profile) {
            $postId = wp_insert_post([
                'post_type'    => AgentType::SLUG,
                'post_title'   => $profile['name'],
                'post_content' => $profile['bio'],
                'post_status'  => 'publish',
            ]);

            if (is_wp_error($postId)) {
                continue;
            }

            update_post_meta($postId, '_wpre_demo_content', '1');

            foreach ($profile['meta'] as $key => $value) {
                update_post_meta($postId, $key, $value);
            }

            if (!empty($profile['specialties'])) {
                wp_set_object_terms($postId, $profile['specialties'], 'agent_specialty');
            }

            $portraitId = $this->photos->importAgentPortrait($profile['photo_query'] ?? 'professional portrait business', $postId);
            if ($portraitId) {
                set_post_thumbnail($postId, $portraitId);
            }

            $ids[] = $postId;
        }

        return $ids;
    }

    private function profiles(): array
    {
        return [
            [
                'name' => 'María García López',
                'bio'  => 'Agente inmobiliaria con más de 12 años de experiencia en el sector residencial de lujo. Especializada en propiedades exclusivas en las mejores zonas de Madrid. Su profundo conocimiento del mercado y atención personalizada garantizan una experiencia excepcional para cada cliente.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 912 345 678',
                    '_wpre_agent_phone_secondary'  => '+34 612 345 678',
                    '_wpre_agent_email'            => 'maria.garcia@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2015-0892',
                    '_wpre_agent_experience_years' => 12,
                    '_wpre_agent_languages'        => 'Español, Inglés, Francés',
                    '_wpre_agent_position'         => 'Directora Comercial',
                    '_wpre_agent_facebook'         => 'https://facebook.com/mariagarcia.re',
                    '_wpre_agent_instagram'        => 'https://instagram.com/mariagarcia_re',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/mariagarcia-re',
                    '_wpre_agent_twitter'          => '',
                    '_wpre_agent_whatsapp'         => '+34612345678',
                ],
                'specialties' => ['Residencial', 'Lujo'],
                'photo_query' => 'professional woman business portrait headshot',
            ],
            [
                'name' => 'Carlos Rodríguez Martín',
                'bio'  => 'Especialista en inversiones inmobiliarias y propiedades comerciales. Carlos cuenta con una sólida formación en finanzas y más de 8 años asesorando a inversores nacionales e internacionales en la adquisición de activos inmobiliarios rentables.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 913 456 789',
                    '_wpre_agent_phone_secondary'  => '',
                    '_wpre_agent_email'            => 'carlos.rodriguez@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2017-1245',
                    '_wpre_agent_experience_years' => 8,
                    '_wpre_agent_languages'        => 'Español, Inglés',
                    '_wpre_agent_position'         => 'Director de Inversiones',
                    '_wpre_agent_facebook'         => '',
                    '_wpre_agent_instagram'        => '',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/carlosrodriguez-re',
                    '_wpre_agent_twitter'          => 'https://x.com/carlos_re',
                    '_wpre_agent_whatsapp'         => '+34623456789',
                ],
                'specialties' => ['Comercial', 'Inversiones'],
                'photo_query' => 'professional man business suit portrait',
            ],
            [
                'name' => 'Ana Fernández Torres',
                'bio'  => 'Apasionada por el sector inmobiliario, Ana se especializa en alquileres residenciales y vacacionales. Su dedicación y cercanía con los clientes la convierten en una referencia para quienes buscan el hogar perfecto en Barcelona y alrededores.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 934 567 890',
                    '_wpre_agent_phone_secondary'  => '+34 634 567 890',
                    '_wpre_agent_email'            => 'ana.fernandez@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2018-0567',
                    '_wpre_agent_experience_years' => 6,
                    '_wpre_agent_languages'        => 'Español, Catalán, Inglés',
                    '_wpre_agent_position'         => 'Responsable de Alquileres',
                    '_wpre_agent_facebook'         => 'https://facebook.com/anafernandez.re',
                    '_wpre_agent_instagram'        => 'https://instagram.com/anafernandez_re',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/anafernandez-re',
                    '_wpre_agent_twitter'          => '',
                    '_wpre_agent_whatsapp'         => '+34634567890',
                ],
                'specialties' => ['Residencial', 'Alquiler'],
                'photo_query' => 'young woman professional smiling portrait',
            ],
            [
                'name' => 'Javier Moreno Ruiz',
                'bio'  => 'Agente senior con amplia experiencia en el mercado industrial y comercial de Valencia. Javier ha gestionado operaciones de gran envergadura y mantiene una extensa red de contactos en el sector empresarial.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 965 678 901',
                    '_wpre_agent_phone_secondary'  => '',
                    '_wpre_agent_email'            => 'javier.moreno@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2014-0334',
                    '_wpre_agent_experience_years' => 15,
                    '_wpre_agent_languages'        => 'Español, Inglés, Alemán',
                    '_wpre_agent_position'         => 'Director de Industrial',
                    '_wpre_agent_facebook'         => '',
                    '_wpre_agent_instagram'        => '',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/javiermoreno-re',
                    '_wpre_agent_twitter'          => '',
                    '_wpre_agent_whatsapp'         => '+34645678901',
                ],
                'specialties' => ['Industrial', 'Comercial'],
                'photo_query' => 'mature man professional confident portrait',
            ],
            [
                'name' => 'Laura Sánchez Navarro',
                'bio'  => 'Con 10 años de trayectoria, Laura se ha consolidado como una de las mejores agentes de propiedades residenciales en Madrid. Su enfoque consultivo y su profundo conocimiento de los barrios madrileños la hacen indispensable para encontrar el hogar ideal.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 914 789 012',
                    '_wpre_agent_phone_secondary'  => '+34 654 789 012',
                    '_wpre_agent_email'            => 'laura.sanchez@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2016-0721',
                    '_wpre_agent_experience_years' => 10,
                    '_wpre_agent_languages'        => 'Español, Inglés, Italiano',
                    '_wpre_agent_position'         => 'Agente Senior',
                    '_wpre_agent_facebook'         => 'https://facebook.com/laurasanchez.re',
                    '_wpre_agent_instagram'        => 'https://instagram.com/laurasanchez_re',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/laurasanchez-re',
                    '_wpre_agent_twitter'          => '',
                    '_wpre_agent_whatsapp'         => '+34654789012',
                ],
                'specialties' => ['Residencial', 'Lujo'],
                'photo_query' => 'woman real estate agent professional portrait',
            ],
            [
                'name' => 'Pablo Jiménez Herrera',
                'bio'  => 'Pablo es especialista en obra nueva y promociones inmobiliarias. Trabaja estrechamente con promotoras y constructoras para ofrecer a sus clientes las mejores oportunidades en viviendas de nueva construcción en toda España.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 955 890 123',
                    '_wpre_agent_phone_secondary'  => '',
                    '_wpre_agent_email'            => 'pablo.jimenez@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2019-0198',
                    '_wpre_agent_experience_years' => 5,
                    '_wpre_agent_languages'        => 'Español, Portugués',
                    '_wpre_agent_position'         => 'Responsable de Obra Nueva',
                    '_wpre_agent_facebook'         => '',
                    '_wpre_agent_instagram'        => 'https://instagram.com/pablojimenez_re',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/pablojimenez-re',
                    '_wpre_agent_twitter'          => '',
                    '_wpre_agent_whatsapp'         => '+34665890123',
                ],
                'specialties' => ['Residencial', 'Inversiones'],
                'photo_query' => 'young man professional business casual portrait',
            ],
            [
                'name' => 'Elena Ruiz Castillo',
                'bio'  => 'Agente bilingüe especializada en clientes internacionales que buscan propiedades en la Costa del Sol y Málaga. Elena ofrece un servicio integral que incluye asesoramiento legal y fiscal para compradores extranjeros.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 952 901 234',
                    '_wpre_agent_phone_secondary'  => '+34 676 901 234',
                    '_wpre_agent_email'            => 'elena.ruiz@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2017-0456',
                    '_wpre_agent_experience_years' => 7,
                    '_wpre_agent_languages'        => 'Español, Inglés, Ruso',
                    '_wpre_agent_position'         => 'Agente Internacional',
                    '_wpre_agent_facebook'         => 'https://facebook.com/elenaruiz.re',
                    '_wpre_agent_instagram'        => 'https://instagram.com/elenaruiz_re',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/elenaruiz-re',
                    '_wpre_agent_twitter'          => '',
                    '_wpre_agent_whatsapp'         => '+34676901234',
                ],
                'specialties' => ['Residencial', 'Lujo', 'Alquiler'],
                'photo_query' => 'woman professional elegant portrait headshot',
            ],
            [
                'name' => 'Diego Martínez Vega',
                'bio'  => 'Con formación en arquitectura y experiencia en el mercado inmobiliario, Diego aporta una perspectiva única a la hora de valorar y comercializar propiedades. Su ojo técnico es especialmente valorado en la venta de propiedades singulares y de diseño.',
                'meta' => [
                    '_wpre_agent_phone'            => '+34 963 012 345',
                    '_wpre_agent_phone_secondary'  => '',
                    '_wpre_agent_email'            => 'diego.martinez@tuinmobiliaria.es',
                    '_wpre_agent_license'          => 'AICAT-2020-0089',
                    '_wpre_agent_experience_years' => 4,
                    '_wpre_agent_languages'        => 'Español, Inglés',
                    '_wpre_agent_position'         => 'Agente Técnico',
                    '_wpre_agent_facebook'         => '',
                    '_wpre_agent_instagram'        => 'https://instagram.com/diegomartinez_re',
                    '_wpre_agent_linkedin'         => 'https://linkedin.com/in/diegomartinez-re',
                    '_wpre_agent_twitter'          => 'https://x.com/diego_re',
                    '_wpre_agent_whatsapp'         => '+34687012345',
                ],
                'specialties' => ['Residencial', 'Comercial'],
                'photo_query' => 'man architect professional creative portrait',
            ],
        ];
    }
}
