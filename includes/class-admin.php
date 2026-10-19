<?php
declare(strict_types=1);

namespace WooDeadlockVacuum;

defined('ABSPATH') || exit;

final class Admin {
    public function register(): void {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu_page(): void {
        add_submenu_page(
            'woocommerce',
            __('Database Vacuum & Pruner', 'woo-deadlock-vacuum'),
            __('DB Vacuum', 'woo-deadlock-vacuum'),
            'manage_woocommerce',
            'wdv-settings',
            [$this, 'render_dashboard']
        );
    }

    public function register_settings(): void {
        register_setting('wdv_settings_group', Plugin::OPTION_KEY, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default'           => [
                'chunk_size'      => VacuumEngine::DEFAULT_CHUNK_SIZE,
                'enable_schedule' => 1,
            ],
        ]);
    }

    public function sanitize_settings(mixed $input): array {
        $output = [];
        $output['chunk_size']      = isset($input['chunk_size']) ? max(50, min(2000, (int) $input['chunk_size'])) : VacuumEngine::DEFAULT_CHUNK_SIZE;
        $output['enable_schedule'] = !empty($input['enable_schedule']) ? 1 : 0;
        return $output;
    }

    public function enqueue_assets(string $hook): void {
        if (strpos($hook, 'wdv-settings') === false) {
            return;
        }

        $plugin_url = Plugin::get_plugin_url();

        wp_enqueue_style('wdv-admin-css', $plugin_url . 'assets/css/vacuum-admin.css', [], Plugin::VERSION);
        wp_enqueue_script('wdv-admin-js', $plugin_url . 'assets/js/vacuum-admin.js', ['jquery'], Plugin::VERSION, true);

        wp_localize_script('wdv-admin-js', 'wdvData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wdv_vacuum_nonce'),
            'i18n'    => [
                'purging' => __('Purging chunk in background...', 'woo-deadlock-vacuum'),
                'success' => __('Vacuum batch completed successfully!', 'woo-deadlock-vacuum'),
            ],
        ]);
    }

    public function render_dashboard(): void {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        global $wpdb;

        $settings = get_option(Plugin::OPTION_KEY, ['chunk_size' => VacuumEngine::DEFAULT_CHUNK_SIZE]);
        $stats    = Scheduler::get_vacuum_stats();

        // Calculate expired transients estimate safely
        $expired_transients_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
                $wpdb->esc_like('_transient_timeout_') . '%',
                time()
            )
        );

        require_once Plugin::get_plugin_dir() . 'templates/admin-dashboard.php';
    }
}

