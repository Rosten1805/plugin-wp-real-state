<?php
/**
 * Menu de administracion del plugin.
 *
 * @package WPRealEstate\Admin
 */

namespace WPRealEstate\Admin;

use WPRealEstate\Admin\Settings\ConfigSettings;
use WPRealEstate\Admin\Settings\GeneralSettings;
use WPRealEstate\Admin\Settings\ScheduleSettings;
use WPRealEstate\Admin\Settings\SocialSettings;
use WPRealEstate\DemoData\DemoContentManager;
use WPRealEstate\PostTypes\AgentType;
use WPRealEstate\PostTypes\ListingType;

defined('ABSPATH') || exit;

class Menu
{
    private array $tabs;

    public function __construct()
    {
        $this->tabs = [
            'general'  => new GeneralSettings(),
            'social'   => new SocialSettings(),
            'config'   => new ConfigSettings(),
            'schedule' => new ScheduleSettings(),
        ];
    }

    public function register(): void
    {
        add_menu_page(
            __('WP Real Estate', 'wp-real-estate'),
            __('WP Real Estate', 'wp-real-estate'),
            'manage_options',
            'wp-real-estate',
            [$this, 'renderDashboard'],
            'dashicons-admin-multisite',
            30
        );

        add_submenu_page(
            'wp-real-estate',
            __('Panel', 'wp-real-estate'),
            __('Panel', 'wp-real-estate'),
            'manage_options',
            'wp-real-estate',
            [$this, 'renderDashboard']
        );

        add_submenu_page(
            'wp-real-estate',
            __('Ajustes', 'wp-real-estate'),
            __('Ajustes', 'wp-real-estate'),
            'manage_options',
            'wp-real-estate-settings',
            [$this, 'renderSettings']
        );

        add_submenu_page(
            'wp-real-estate',
            __('Herramientas', 'wp-real-estate'),
            __('Herramientas', 'wp-real-estate'),
            'manage_options',
            'wp-real-estate-tools',
            [$this, 'renderTools']
        );
    }

    public function registerSettings(): void
    {
        foreach ($this->tabs as $tab) {
            $tab->register();
        }
    }

    public function renderDashboard(): void
    {
        $listingsCount = wp_count_posts(ListingType::SLUG);
        $agentsCount = wp_count_posts(AgentType::SLUG);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WP Real Estate', 'wp-real-estate'); ?></h1>
            <p class="description"><?php esc_html_e('Panel de control de tu inmobiliaria.', 'wp-real-estate'); ?></p>

            <div class="wpre-dashboard">
                <div class="wpre-dashboard__cards">
                    <div class="wpre-card">
                        <h3><?php esc_html_e('Inmuebles', 'wp-real-estate'); ?></h3>
                        <span class="wpre-card__number"><?php echo absint($listingsCount->publish ?? 0); ?></span>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=' . ListingType::SLUG)); ?>">
                            <?php esc_html_e('Ver todos', 'wp-real-estate'); ?> &rarr;
                        </a>
                    </div>
                    <div class="wpre-card">
                        <h3><?php esc_html_e('Agentes', 'wp-real-estate'); ?></h3>
                        <span class="wpre-card__number"><?php echo absint($agentsCount->publish ?? 0); ?></span>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=' . AgentType::SLUG)); ?>">
                            <?php esc_html_e('Ver todos', 'wp-real-estate'); ?> &rarr;
                        </a>
                    </div>
                </div>

                <div class="wpre-dashboard__links">
                    <h3><?php esc_html_e('Accesos rápidos', 'wp-real-estate'); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . ListingType::SLUG)); ?>"><?php esc_html_e('Añadir inmueble', 'wp-real-estate'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . AgentType::SLUG)); ?>"><?php esc_html_e('Añadir agente', 'wp-real-estate'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=wp-real-estate-settings')); ?>"><?php esc_html_e('Ajustes', 'wp-real-estate'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=wp-real-estate-tools')); ?>"><?php esc_html_e('Herramientas', 'wp-real-estate'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    public function renderSettings(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $currentTab = sanitize_text_field($_GET['tab'] ?? 'general');
        $tabLabels = [
            'general'  => __('General', 'wp-real-estate'),
            'social'   => __('Redes Sociales', 'wp-real-estate'),
            'config'   => __('Configuración', 'wp-real-estate'),
            'schedule' => __('Horarios', 'wp-real-estate'),
        ];

        if (!array_key_exists($currentTab, $tabLabels)) {
            $currentTab = 'general';
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Ajustes de WP Real Estate', 'wp-real-estate'); ?></h1>

            <nav class="nav-tab-wrapper">
                <?php foreach ($tabLabels as $tabKey => $tabLabel) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-real-estate-settings&tab=' . $tabKey)); ?>"
                       class="nav-tab <?php echo $currentTab === $tabKey ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($tabLabel); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php
                $activeTab = $this->tabs[$currentTab] ?? null;
                if ($activeTab) {
                    settings_fields($activeTab->getOptionGroup());
                    do_settings_sections($activeTab->getPageSlug());
                }
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function renderTools(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $demoManager = new DemoContentManager();
        $hasFlaggedOption = $demoManager->isDemoActive();
        $hasFlaggedPosts = (bool) get_posts([
            'post_type'      => [ListingType::SLUG, AgentType::SLUG],
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'meta_key'       => '_wpre_demo_content',
            'meta_value'     => '1',
            'fields'         => 'ids',
        ]);
        $hasDemoContent = $hasFlaggedOption || $hasFlaggedPosts;
        $hasUnsplashKey = !empty(get_option('wpre_unsplash_key', ''));
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Herramientas', 'wp-real-estate'); ?></h1>

            <div class="wpre-tools">
                <div class="wpre-tools__section">
                    <h2><?php esc_html_e('Contenido de demostración', 'wp-real-estate'); ?></h2>
                    <p class="description">
                        <?php esc_html_e('Genera o elimina contenido de demostración (8 agentes y 50 inmuebles con datos realistas e imágenes de Unsplash).', 'wp-real-estate'); ?>
                    </p>

                    <?php if (!$hasUnsplashKey) : ?>
                        <div class="notice notice-warning inline" style="margin: 12px 0;">
                            <p>
                                <?php
                                printf(
                                    esc_html__('No hay Unsplash Access Key configurada. Los inmuebles se crearán sin imágenes. %sConfigurar ahora%s', 'wp-real-estate'),
                                    '<a href="' . esc_url(admin_url('admin.php?page=wp-real-estate-settings&tab=config')) . '">',
                                    '</a>'
                                );
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div id="wpre-demo-actions">
                        <?php if ($hasDemoContent) : ?>
                            <p class="wpre-tools__status wpre-tools__status--active">
                                <?php esc_html_e('El contenido de demostración está activo.', 'wp-real-estate'); ?>
                            </p>
                            <button type="button" id="wpre-remove-demo" class="button button-secondary">
                                <?php esc_html_e('Eliminar contenido de demostración', 'wp-real-estate'); ?>
                            </button>
                        <?php else : ?>
                            <p class="wpre-tools__status">
                                <?php esc_html_e('No hay contenido de demostración generado.', 'wp-real-estate'); ?>
                            </p>
                            <button type="button" id="wpre-generate-demo" class="button button-primary">
                                <?php esc_html_e('Generar contenido de demostración', 'wp-real-estate'); ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div id="wpre-demo-panel" style="display:none;">
                        <div class="wpre-progress">
                            <div class="wpre-progress__header">
                                <span id="wpre-progress-status"><?php esc_html_e('Iniciando...', 'wp-real-estate'); ?></span>
                                <span id="wpre-progress-percent">0%</span>
                            </div>
                            <div class="wpre-progress__bar">
                                <div id="wpre-progress-bar-fill"></div>
                            </div>
                        </div>

                        <div class="wpre-demo-panel__layout">
                            <div class="wpre-demo-panel__steps">
                                <ul id="wpre-step-list"></ul>
                            </div>
                            <div class="wpre-demo-panel__log">
                                <div id="wpre-demo-log"></div>
                            </div>
                        </div>

                        <div id="wpre-demo-retry" style="display:none;">
                            <button type="button" class="button button-secondary">
                                <?php esc_html_e('Reintentar desde este paso', 'wp-real-estate'); ?>
                            </button>
                        </div>

                        <div id="wpre-demo-complete" style="display:none;">
                            <span class="wpre-demo-complete__icon">&#9989;</span>
                            <span class="wpre-demo-complete__text"></span>
                            <button type="button" class="button button-primary">
                                <?php esc_html_e('Recargar página', 'wp-real-estate'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
