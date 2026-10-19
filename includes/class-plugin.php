<?php
declare(strict_types=1);

namespace WooDeadlockVacuum;

defined('ABSPATH') || exit;

final class Plugin {
    public const VERSION = '1.0.0';
    public const OPTION_KEY = 'wdv_settings';

    private static ?self $instance = null;
    private VacuumEngine $engine;
    private Scheduler $scheduler;
    private Admin $admin;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        $this->engine    = new VacuumEngine();
        $this->scheduler = new Scheduler();
        $this->admin     = new Admin();
    }

    public function init(): void {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'render_woocommerce_missing_notice']);
            return;
        }

        $this->engine->register();
        $this->scheduler->register();
        $this->admin->register();
    }

    public function render_woocommerce_missing_notice(): void {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('WooCommerce Deadlock-Free Database Vacuum requires WooCommerce to be installed and active.', 'woo-deadlock-vacuum'); ?></p>
        </div>
        <?php
    }

    public static function get_plugin_dir(): string {
        return plugin_dir_path(dirname(__FILE__));
    }

    public static function get_plugin_url(): string {
        return plugin_dir_url(dirname(__FILE__));
    }
}

