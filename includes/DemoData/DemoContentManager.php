<?php
/**
 * Orquesta la generación y eliminación de contenido de demostración,
 * ejecutada paso a paso desde la pantalla de Herramientas via AJAX.
 *
 * @package WPRealEstate\DemoData
 */

namespace WPRealEstate\DemoData;

use WPRealEstate\PostTypes\AgentType;
use WPRealEstate\PostTypes\ListingType;

defined('ABSPATH') || exit;

class DemoContentManager
{
    private const FLAG_OPTION    = 'wpre_demo_active';
    private const AGENTS_OPTION  = 'wpre_demo_agent_ids';
    private const COUNTER_OPTION = 'wpre_demo_counter';

    public static function getSteps(): array
    {
        return [
            ['action' => 'agents', 'label' => 'Creando agentes (8)', 'type' => 'agents'],
            ['action' => 'listings', 'label' => 'Apartamentos (15)', 'type' => 'Apartamento', 'count' => 15],
            ['action' => 'listings', 'label' => 'Casas (10)', 'type' => 'Casa', 'count' => 10],
            ['action' => 'listings', 'label' => 'Chalets (5)', 'type' => 'Chalet', 'count' => 5],
            ['action' => 'listings', 'label' => 'Áticos (4)', 'type' => 'Ático', 'count' => 4],
            ['action' => 'listings', 'label' => 'Locales Comerciales (3)', 'type' => 'Local Comercial', 'count' => 3],
            ['action' => 'listings', 'label' => 'Oficinas (3)', 'type' => 'Oficina', 'count' => 3],
            ['action' => 'listings', 'label' => 'Terrenos (3)', 'type' => 'Terreno', 'count' => 3],
            ['action' => 'listings', 'label' => 'Estudios (3)', 'type' => 'Estudio', 'count' => 3],
            ['action' => 'listings', 'label' => 'Dúplex (2)', 'type' => 'Dúplex', 'count' => 2],
            ['action' => 'listings', 'label' => 'Naves Industriales (2)', 'type' => 'Nave Industrial', 'count' => 2],
        ];
    }

    public function isDemoActive(): bool
    {
        return (bool) get_option(self::FLAG_OPTION, false);
    }

    public function runStep(int $step): array
    {
        $steps = self::getSteps();
        $totalSteps = count($steps);

        if ($step < 0 || $step >= $totalSteps) {
            return ['success' => false, 'message' => __('Paso inválido.', 'wp-real-estate')];
        }

        if ($step === 0 && $this->isDemoActive()) {
            return ['success' => false, 'message' => __('El contenido de demostración ya existe.', 'wp-real-estate')];
        }

        $current = $steps[$step];

        return $current['action'] === 'agents'
            ? $this->runAgentsStep($step, $totalSteps)
            : $this->runListingsStep($step, $totalSteps, $current);
    }

    private function runAgentsStep(int $step, int $totalSteps): array
    {
        update_option(self::COUNTER_OPTION, 0);

        $agentIds = (new AgentGenerator())->generate();
        update_option(self::AGENTS_OPTION, $agentIds);

        return [
            'success'     => true,
            'step'        => $step,
            'total_steps' => $totalSteps,
            'message'     => sprintf(__('Creados %d agentes con perfiles completos.', 'wp-real-estate'), count($agentIds)),
            'details'     => array_map('get_the_title', $agentIds),
            'done'        => false,
        ];
    }

    private function runListingsStep(int $step, int $totalSteps, array $config): array
    {
        $agentIds = get_option(self::AGENTS_OPTION, []);
        $counter = (int) get_option(self::COUNTER_OPTION, 0);

        $generator = new ListingGenerator($agentIds, $counter);
        $operations = $this->operationsForType($config['type'], $config['count']);
        $result = $generator->generateBatch($config['type'], $operations);

        update_option(self::COUNTER_OPTION, $result['counter']);

        $isLastStep = ($step === $totalSteps - 1);
        if ($isLastStep) {
            update_option(self::FLAG_OPTION, true);
            delete_option(self::AGENTS_OPTION);
            delete_option(self::COUNTER_OPTION);
        }

        return [
            'success'     => true,
            'step'        => $step,
            'total_steps' => $totalSteps,
            'message'     => sprintf(
                __('Creados %d %s.', 'wp-real-estate'),
                $result['created'],
                strtolower($config['type']) . ($result['created'] !== 1 ? 's' : '')
            ),
            'details'     => $result['titles'],
            'done'        => $isLastStep,
        ];
    }

    private function operationsForType(string $type, int $count): array
    {
        $map = [
            'Apartamento'     => ['Venta', 'Venta', 'Venta', 'Venta', 'Venta', 'Venta', 'Venta', 'Venta', 'Alquiler', 'Alquiler', 'Alquiler', 'Alquiler', 'Alquiler', 'Alquiler Vacacional', 'Alquiler Vacacional'],
            'Casa'            => ['Venta', 'Venta', 'Venta', 'Venta', 'Venta', 'Venta', 'Alquiler', 'Alquiler', 'Alquiler', 'Alquiler'],
            'Chalet'          => ['Venta', 'Venta', 'Venta', 'Alquiler', 'Alquiler'],
            'Ático'           => ['Venta', 'Venta', 'Venta', 'Alquiler'],
            'Local Comercial' => ['Venta', 'Venta', 'Alquiler'],
            'Oficina'         => ['Venta', 'Alquiler', 'Alquiler'],
            'Terreno'         => ['Venta', 'Venta', 'Venta'],
            'Estudio'         => ['Venta', 'Alquiler', 'Alquiler'],
            'Dúplex'          => ['Venta', 'Alquiler'],
            'Nave Industrial' => ['Venta', 'Alquiler'],
        ];

        return array_slice($map[$type] ?? array_fill(0, $count, 'Venta'), 0, $count);
    }

    public function removeAll(): array
    {
        $attachments = get_posts([
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_key'       => '_wpre_demo_content',
            'meta_value'     => '1',
            'fields'         => 'ids',
        ]);
        foreach ($attachments as $id) {
            wp_delete_attachment($id, true);
        }

        $listings = get_posts([
            'post_type'      => ListingType::SLUG,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_key'       => '_wpre_demo_content',
            'meta_value'     => '1',
            'fields'         => 'ids',
        ]);
        foreach ($listings as $id) {
            wp_delete_post($id, true);
        }

        $agents = get_posts([
            'post_type'      => AgentType::SLUG,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_key'       => '_wpre_demo_content',
            'meta_value'     => '1',
            'fields'         => 'ids',
        ]);
        foreach ($agents as $id) {
            wp_delete_post($id, true);
        }

        delete_option(self::FLAG_OPTION);
        delete_option(self::AGENTS_OPTION);
        delete_option(self::COUNTER_OPTION);

        return [
            'success' => true,
            'message' => sprintf(
                __('Se han eliminado %d inmuebles, %d agentes y %d imágenes de demostración.', 'wp-real-estate'),
                count($listings),
                count($agents),
                count($attachments)
            ),
        ];
    }

    public function handleGenerateRequest(): void
    {
        check_ajax_referer('wpre_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No tienes permisos.', 'wp-real-estate')]);
        }

        @set_time_limit(300);

        $step = isset($_POST['step']) ? absint($_POST['step']) : 0;
        $result = $this->runStep($step);

        $result['success'] ? wp_send_json_success($result) : wp_send_json_error($result);
    }

    public function handleRemoveRequest(): void
    {
        check_ajax_referer('wpre_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No tienes permisos.', 'wp-real-estate')]);
        }

        $result = $this->removeAll();
        $result['success'] ? wp_send_json_success($result) : wp_send_json_error($result);
    }
}
