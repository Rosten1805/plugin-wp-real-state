<?php
/**
 * Registro centralizado de actions y filters.
 *
 * En lugar de llamar add_action()/add_filter() de forma dispersa, los
 * distintos componentes del plugin declaran sus hooks aqui y el Bootstrap
 * los registra todos de una vez en run().
 *
 * @package WPRealEstate\Core
 */

namespace WPRealEstate\Core;

defined('ABSPATH') || exit;

class Loader
{
    private array $actions = [];

    private array $filters = [];

    public function addAction(string $hook, object $component, string $method, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->actions[] = [
            'hook'     => $hook,
            'callback' => [$component, $method],
            'priority' => $priority,
            'args'     => $acceptedArgs,
        ];
    }

    public function addFilter(string $hook, object $component, string $method, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->filters[] = [
            'hook'     => $hook,
            'callback' => [$component, $method],
            'priority' => $priority,
            'args'     => $acceptedArgs,
        ];
    }

    public function run(): void
    {
        foreach ($this->actions as $item) {
            add_action($item['hook'], $item['callback'], $item['priority'], $item['args']);
        }

        foreach ($this->filters as $item) {
            add_filter($item['hook'], $item['callback'], $item['priority'], $item['args']);
        }
    }
}
