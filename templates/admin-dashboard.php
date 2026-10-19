<?php
declare(strict_types=1);

namespace WooDeadlockVacuum;

defined('ABSPATH') || exit;

/**
 * @var array $settings
 * @var array $stats
 * @var int $expired_transients_count
 */
?>
<div class="wrap wdv-admin-wrap">
    <h1><?php esc_html_e('WooCommerce Deadlock-Free Database Vacuum & Pruner', 'woo-deadlock-vacuum'); ?></h1>
    <p class="description">
        <?php esc_html_e('Zero-downtime database housekeeping. Cleans expired WooCommerce transients and orphan order item metadata using chunked micro-transactions without table locks.', 'woo-deadlock-vacuum'); ?>
    </p>

    <div class="wdv-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 20px;">
        <div class="wdv-main-col">
            <div class="postbox" style="padding: 24px;">
                <h2 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Database Health & Vacuum Telemetry', 'woo-deadlock-vacuum'); ?></h2>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; padding: 16px; border-radius: 6px;">
                        <span class="wdv-total-purged" style="font-size: 28px; font-weight: 700; color: #16a34a; display: block;">
                            <?php echo esc_html(number_format((float) ($stats['total_purged'] ?? 0))); ?>
                        </span>
                        <span style="font-size: 12px; color: #4b5563; font-weight: 500;"><?php esc_html_e('Total Rows Safely Purged', 'woo-deadlock-vacuum'); ?></span>
                    </div>

                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #2563eb; padding: 16px; border-radius: 6px;">
                        <span style="font-size: 28px; font-weight: 700; color: #2563eb; display: block;">
                            <?php echo esc_html(number_format((float) $expired_transients_count)); ?>
                        </span>
                        <span style="font-size: 12px; color: #4b5563; font-weight: 500;"><?php esc_html_e('Stale Transients Pending', 'woo-deadlock-vacuum'); ?></span>
                    </div>

                    <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-left: 4px solid #9333ea; padding: 16px; border-radius: 6px;">
                        <span style="font-size: 28px; font-weight: 700; color: #9333ea; display: block;">
                            <?php echo esc_html((string) ($settings['chunk_size'] ?? 500)); ?>
                        </span>
                        <span style="font-size: 12px; color: #4b5563; font-weight: 500;"><?php esc_html_e('Chunk Size (No Table Locks)', 'woo-deadlock-vacuum'); ?></span>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                    <h3 style="margin-top: 0; font-size: 14px;"><?php esc_html_e('Manual Non-Blocking Vacuum Trigger', 'woo-deadlock-vacuum'); ?></h3>
                    <p style="font-size: 13px; color: #475569; margin-bottom: 16px;">
                        <?php esc_html_e('Trigger an on-demand micro-batch vacuum. Operates strictly within single chunk constraints with 50ms pauses, ensuring customer checkouts never experience MySQL deadlock exceptions.', 'woo-deadlock-vacuum'); ?>
                    </p>
                    <button type="button" class="button button-primary button-hero wdv-run-now-btn" id="wdv-run-btn">
                        <?php esc_html_e('⚡ Run Vacuum Chunk Now', 'woo-deadlock-vacuum'); ?>
                    </button>
                    <span class="wdv-status-feedback" style="margin-left: 12px; font-size: 13px; font-weight: 500;"></span>
                </div>

                <h3 style="font-size: 14px;"><?php esc_html_e('Deadlock Prevention Strategy', 'woo-deadlock-vacuum'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Mechanism', 'woo-deadlock-vacuum'); ?></th>
                            <th><?php esc_html_e('Standard Cleaning Plugins (WP-Optimize / Clearer)', 'woo-deadlock-vacuum'); ?></th>
                            <th><?php esc_html_e('WooCommerce Deadlock Vacuum', 'woo-deadlock-vacuum'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('Query Execution', 'woo-deadlock-vacuum'); ?></strong></td>
                            <td style="color: #dc2626;"><code>DELETE FROM wp_options WHERE ...</code> (Locks entire table)</td>
                            <td style="color: #16a34a;"><strong>Indexed Primary Key Chunking</strong> (Row-level only)</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Checkout Interruption Risk', 'woo-deadlock-vacuum'); ?></strong></td>
                            <td style="color: #dc2626;"><?php esc_html_e('High (Deadlock 1213 on concurrent orders)', 'woo-deadlock-vacuum'); ?></td>
                            <td style="color: #16a34a;"><strong>Zero</strong> (50ms sleep between micro-batches)</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Scheduler Architecture', 'woo-deadlock-vacuum'); ?></strong></td>
                            <td>Synchronous HTTP timeout risk</td>
                            <td>Asynchronous WooCommerce Action Scheduler</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="wdv-side-col">
            <div class="postbox" style="padding: 24px;">
                <h2 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Vacuum Parameters', 'woo-deadlock-vacuum'); ?></h2>
                <form method="post" action="options.php">
                    <?php settings_fields('wdv_settings_group'); ?>

                    <p>
                        <label for="wdv_chunk_size"><strong><?php esc_html_e('Batch Chunk Limit:', 'woo-deadlock-vacuum'); ?></strong></label><br>
                        <input type="number" id="wdv_chunk_size" name="wdv_settings[chunk_size]" value="<?php echo esc_attr((string) ($settings['chunk_size'] ?? 500)); ?>" min="50" max="2000" step="50" class="regular-text" style="width: 100%; margin-top: 5px;">
                        <span class="description" style="display: block; margin-top: 4px; font-size: 12px;">
                            <?php esc_html_e('Recommended: 500 rows. Keeps MySQL lock acquisition times under 12ms per execution.', 'woo-deadlock-vacuum'); ?>
                        </span>
                    </p>

                    <p style="margin-top: 16px;">
                        <label>
                            <input type="checkbox" name="wdv_settings[enable_schedule]" value="1" <?php checked(!empty($settings['enable_schedule'])); ?>>
                            <strong><?php esc_html_e('Action Scheduler Automation', 'woo-deadlock-vacuum'); ?></strong>
                        </label>
                        <span class="description" style="display: block; margin-top: 4px; font-size: 12px;">
                            <?php esc_html_e('Automatically cleans orphans every 6 hours during non-peak server times.', 'woo-deadlock-vacuum'); ?>
                        </span>
                    </p>

                    <?php submit_button(__('Save Vacuum Settings', 'woo-deadlock-vacuum')); ?>
                </form>
            </div>
        </div>
    </div>
</div>

