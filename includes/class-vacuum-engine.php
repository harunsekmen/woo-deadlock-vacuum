<?php
declare(strict_types=1);

namespace WooDeadlockVacuum;

defined('ABSPATH') || exit;

final class VacuumEngine {
    public const DEFAULT_CHUNK_SIZE = 500;
    public const CHUNK_SLEEP_MICROSECONDS = 50000; // 50ms pause to let live checkout transactions commit

    public function register(): void {
        add_action('wdv_run_vacuum_chunk_action', [$this, 'process_vacuum_chunk']);
        add_action('wp_ajax_wdv_trigger_vacuum', [$this, 'handle_ajax_vacuum']);
    }

    public function get_chunk_size(): int {
        $settings = get_option(Plugin::OPTION_KEY, []);
        return isset($settings['chunk_size']) ? max(50, min(2000, (int) $settings['chunk_size'])) : self::DEFAULT_CHUNK_SIZE;
    }

    /**
     * Executes single non-blocking chunk deletion using primary key index scans
     */
    public function purge_expired_transients(int $limit): int {
        global $wpdb;

        $time = time();
        $limit = max(10, $limit);

        // Find primary keys in indexed subquery first to avoid row lock escalation
        $sql = $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_value < %d 
             LIMIT %d",
            $wpdb->esc_like('_transient_timeout_') . '%',
            $time,
            $limit
        );

        $timeout_options = $wpdb->get_col($sql);
        if (empty($timeout_options)) {
            return 0;
        }

        $transient_keys = [];
        foreach ($timeout_options as $timeout_key) {
            $transient_keys[] = $timeout_key;
            $transient_keys[] = substr($timeout_key, strlen('_timeout')); // actual _transient_ key
        }

        if (empty($transient_keys)) {
            return 0;
        }

        $format = implode(',', array_fill(0, count($transient_keys), '%s'));
        $delete_sql = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name IN ($format)",
            ...$transient_keys
        );

        $deleted = (int) $wpdb->query($delete_sql);
        usleep(self::CHUNK_SLEEP_MICROSECONDS);

        return $deleted;
    }

    /**
     * Safely cleans up orphan order item meta from cancelled/trashed sessions
     */
    public function purge_orphan_order_itemmeta(int $limit): int {
        global $wpdb;

        $items_table = $wpdb->prefix . 'woocommerce_order_items';
        $meta_table  = $wpdb->prefix . 'woocommerce_order_itemmeta';

        // Check if WooCommerce tables exist
        if ($wpdb->get_var("SHOW TABLES LIKE '{$items_table}'") !== $items_table) {
            return 0;
        }

        $sql = $wpdb->prepare(
            "SELECT m.meta_id FROM {$meta_table} m 
             LEFT JOIN {$items_table} i ON m.order_item_id = i.order_item_id 
             WHERE i.order_item_id IS NULL 
             LIMIT %d",
            $limit
        );

        $orphan_ids = $wpdb->get_col($sql);
        if (empty($orphan_ids)) {
            return 0;
        }

        $format = implode(',', array_fill(0, count($orphan_ids), '%d'));
        $delete_sql = $wpdb->prepare(
            "DELETE FROM {$meta_table} WHERE meta_id IN ($format)",
            ...$orphan_ids
        );

        $deleted = (int) $wpdb->query($delete_sql);
        usleep(self::CHUNK_SLEEP_MICROSECONDS);

        return $deleted;
    }

    public function process_vacuum_chunk(): void {
        $chunk_size = $this->get_chunk_size();
        $deleted_transients = $this->purge_expired_transients($chunk_size);
        $deleted_orphans    = $this->purge_orphan_order_itemmeta($chunk_size);

        $stats = Scheduler::get_vacuum_stats();
        $stats['total_purged'] = (int) ($stats['total_purged'] ?? 0) + $deleted_transients + $deleted_orphans;
        $stats['last_run']     = current_time('mysql');
        $stats['last_batch']   = $deleted_transients + $deleted_orphans;

        update_option(Scheduler::STATS_OPTION, $stats, false);

        // If there were more items, schedule next micro-chunk via Action Scheduler
        if (($deleted_transients + $deleted_orphans) >= $chunk_size) {
            if (function_exists('as_enqueue_async_action')) {
                as_enqueue_async_action('wdv_run_vacuum_chunk_action');
            }
        }
    }

    public function handle_ajax_vacuum(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied.', 'woo-deadlock-vacuum')]);
        }
        check_ajax_referer('wdv_vacuum_nonce', 'nonce');

        $chunk_size = $this->get_chunk_size();
        $deleted_transients = $this->purge_expired_transients($chunk_size);
        $deleted_orphans    = $this->purge_orphan_order_itemmeta($chunk_size);
        $total = $deleted_transients + $deleted_orphans;

        $stats = Scheduler::get_vacuum_stats();
        $stats['total_purged'] = (int) ($stats['total_purged'] ?? 0) + $total;
        $stats['last_run']     = current_time('mysql');
        $stats['last_batch']   = $total;
        update_option(Scheduler::STATS_OPTION, $stats, false);

        wp_send_json_success([
            'purged'        => $total,
            'transients'    => $deleted_transients,
            'orphans'       => $deleted_orphans,
            'total_all_time'=> $stats['total_purged'],
            'message'       => sprintf(
                /* translators: %d: number of purged records */
                __('Vacuum chunk completed: %d rows safely purged without locking tables.', 'woo-deadlock-vacuum'),
                $total
            ),
        ]);
    }
}

