<?php
declare(strict_types=1);

namespace WooDeadlockVacuum;

defined('ABSPATH') || exit;

final class Scheduler {
    public const HOOK_NAME = 'wdv_recurring_vacuum_hook';
    public const STATS_OPTION = 'wdv_vacuum_stats';

    public function register(): void {
        add_action('init', [$this, 'schedule_recurring_vacuum']);
        add_action(self::HOOK_NAME, [$this, 'dispatch_vacuum']);
    }

    public function schedule_recurring_vacuum(): void {
        if (!function_exists('as_has_scheduled_action') || !function_exists('as_schedule_recurring_action')) {
            return;
        }

        if (!as_has_scheduled_action(self::HOOK_NAME)) {
            // Run vacuum in off-peak background every 6 hours
            as_schedule_recurring_action(time() + 600, 6 * HOUR_IN_SECONDS, self::HOOK_NAME);
        }
    }

    public function dispatch_vacuum(): void {
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('wdv_run_vacuum_chunk_action');
        }
    }

    public static function get_vacuum_stats(): array {
        $stats = get_option(self::STATS_OPTION, [
            'total_purged' => 0,
            'last_run'     => null,
            'last_batch'   => 0,
        ]);
        return is_array($stats) ? $stats : [];
    }
}

