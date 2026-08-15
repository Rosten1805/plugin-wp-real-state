<?php
/**
 * Punto de arranque del plugin: conecta todos los componentes con WordPress
 * a traves del Loader.
 *
 * @package WPRealEstate\Core
 */

namespace WPRealEstate\Core;

use WPRealEstate\Admin\AgentColumns;
use WPRealEstate\Admin\ListingColumns;
use WPRealEstate\Admin\Menu;
use WPRealEstate\DemoData\DemoContentManager;
use WPRealEstate\Fields\AgentFields;
use WPRealEstate\Fields\ListingFields;
use WPRealEstate\PostTypes\AgentType;
use WPRealEstate\PostTypes\ListingType;
use WPRealEstate\Taxonomies\AgentTaxonomies;
use WPRealEstate\Taxonomies\ListingTaxonomies;

defined('ABSPATH') || exit;

class Bootstrap
{
    private static ?Bootstrap $instance = null;

    private Loader $loader;

    public static function boot(): Bootstrap
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->loader = new Loader();

        $this->registerTranslations();
        $this->registerPublicHooks();

        if (is_admin()) {
            $this->registerAdminHooks();
        }

        $this->loader->run();
    }

    private function registerTranslations(): void
    {
        add_action('init', function () {
            load_plugin_textdomain('wp-real-estate', false, dirname(WPRE_BASENAME) . '/languages');
        });
    }

    private function registerPublicHooks(): void
    {
        $listingType = new ListingType();
        $agentType = new AgentType();
        $listingTaxonomies = new ListingTaxonomies();
        $agentTaxonomies = new AgentTaxonomies();

        $this->loader->addAction('init', $listingType, 'register');
        $this->loader->addAction('init', $agentType, 'register');
        $this->loader->addAction('init', $listingTaxonomies, 'register');
        $this->loader->addAction('init', $agentTaxonomies, 'register');
    }

    private function registerAdminHooks(): void
    {
        $listingFields = new ListingFields();
        $this->loader->addAction('add_meta_boxes', $listingFields, 'register');
        $this->loader->addAction('save_post_wpre_listing', $listingFields, 'save', 10, 2);

        $agentFields = new AgentFields();
        $this->loader->addAction('add_meta_boxes', $agentFields, 'register');
        $this->loader->addAction('save_post_wpre_agent', $agentFields, 'save', 10, 2);

        $menu = new Menu();
        $this->loader->addAction('admin_menu', $menu, 'register');
        $this->loader->addAction('admin_init', $menu, 'registerSettings');

        $listingColumns = new ListingColumns();
        $this->loader->addFilter('manage_wpre_listing_posts_columns', $listingColumns, 'addColumns');
        $this->loader->addAction('manage_wpre_listing_posts_custom_column', $listingColumns, 'renderColumn', 10, 2);
        $this->loader->addFilter('manage_edit-wpre_listing_sortable_columns', $listingColumns, 'sortableColumns');
        $this->loader->addAction('pre_get_posts', $listingColumns, 'orderbyColumn');

        $agentColumns = new AgentColumns();
        $this->loader->addFilter('manage_wpre_agent_posts_columns', $agentColumns, 'addColumns');
        $this->loader->addAction('manage_wpre_agent_posts_custom_column', $agentColumns, 'renderColumn', 10, 2);
        $this->loader->addFilter('manage_edit-wpre_agent_sortable_columns', $agentColumns, 'sortableColumns');

        $this->loader->addAction('admin_enqueue_scripts', $this, 'enqueueAssets');

        $demoManager = new DemoContentManager();
        $this->loader->addAction('wp_ajax_wpre_generate_demo', $demoManager, 'handleGenerateRequest');
        $this->loader->addAction('wp_ajax_wpre_remove_demo', $demoManager, 'handleRemoveRequest');
    }

    public function enqueueAssets(string $hook): void
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        $pluginScreens = [
            'wpre_listing',
            'wpre_agent',
            'toplevel_page_wp-real-estate',
            'wp-real-estate_page_wp-real-estate-settings',
            'wp-real-estate_page_wp-real-estate-tools',
        ];

        $onPluginScreen = in_array($screen->id, $pluginScreens, true)
            || in_array($screen->post_type, ['wpre_listing', 'wpre_agent'], true);

        if (!$onPluginScreen) {
            return;
        }

        wp_enqueue_style('wp-real-estate-admin', WPRE_URL . 'assets/css/admin.css', [], WPRE_VERSION);
        wp_enqueue_script('wp-real-estate-admin', WPRE_URL . 'assets/js/admin.js', ['jquery'], WPRE_VERSION, true);

        $data = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wpre_nonce'),
        ];

        if ($screen->id === 'wp-real-estate_page_wp-real-estate-tools') {
            $data['demoSteps'] = DemoContentManager::getSteps();
        }

        wp_localize_script('wp-real-estate-admin', 'wpRealEstate', $data);

        if (in_array($screen->post_type, ['wpre_listing', 'wpre_agent'], true) || str_contains($screen->id, 'wp-real-estate')) {
            wp_enqueue_media();
        }
    }
}
