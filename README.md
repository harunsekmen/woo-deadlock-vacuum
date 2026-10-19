# 🗄️ WooCommerce Deadlock-Free Database Vacuum

[![WooCommerce HPOS Ready](https://img.shields.io/badge/WooCommerce-HPOS%20Ready-7f54b3.svg)](https://github.com/harunsekmen/woo-deadlock-vacuum)
[![License: GPL-3.0](https://img.shields.io/badge/License-GPL%203.0-blue.svg)](https://github.com/harunsekmen/woo-deadlock-vacuum/blob/main/LICENSE)
[![PHP: 8.1+](https://img.shields.io/badge/PHP-8.1%20to%208.3-8892be.svg)](https://github.com/harunsekmen/woo-deadlock-vacuum)

> A safe, non-blocking background cleanup tool that purges expired transients and orphaned store data in small batches without locking database tables or interrupting active shoppers.

Developed and maintained by **[Harun Sekmen](https://github.com/harunsekmen)** — E-Commerce Systems Engineer & WooCommerce Developer.

[✉️ Discuss Database Optimization](mailto:sekmenwp@gmail.com) · [💼 Upwork Profile](https://www.upwork.com/freelancers/~01a7a8b58043e6e6d9) · [🔗 Main GitHub Profile](https://github.com/harunsekmen)

---

## 📸 Visual Previews & Operational Interface

### WordPress Admin Vacuum & Maintenance Dashboard
Store administrators can view the count of safely cleaned rows, monitor pending expired transients, adjust batch chunk sizes, or trigger on-demand maintenance:

![WooCommerce DB Vacuum Dashboard](docs/assets/admin-vacuum-dashboard.png)

---

## ⚡ The Problem It Solves

As a WooCommerce store grows and handles thousands of orders or frequent inventory updates, it accumulates massive amounts of temporary data:
- **Expired Transients:** Stale session fragments, cached calculations, and expired API tokens cluttering the `wp_options` table.
- **Orphaned Item Records:** Lingering data rows left behind in order item and metadata tables after past order modifications.
- **The Table Lock Trap:** When store owners run standard database cleanup plugins on a busy store, those tools often execute a single massive deletion query (e.g., `DELETE FROM wp_options WHERE...`). This can lock the entire database table for several seconds. If a customer attempts to place an order at that exact moment, their checkout crashes with a database lock error.

**WooCommerce Deadlock-Free Database Vacuum** solves this by cleaning records in **small, indexed batches** (default: 500 rows at a time) managed quietly in the background via **WooCommerce Action Scheduler**. It introduces a brief pause between batches so active customer checkouts always maintain top database priority.

---

## 🛠️ Key Architectural Features

- **Chunked Batch Deletions:** Deletes records in manageable micro-batches using indexed primary key lookups instead of locking entire tables.
- **Paced Micro-Pauses:** Includes a brief pause between chunks to ensure live customer checkouts and cart updates never compete for table locks.
- **Asynchronous Background Runs:** Automatically schedules periodic maintenance through Action Scheduler during off-peak hours.
- **High-Performance Order Storage (HPOS) Ready:** Built with native support for WooCommerce custom order tables and modern schemas.
- **Clear Admin Visibility:** See exactly how many expired records were safely cleared without needing manual phpMyAdmin intervention.

---

## 💼 Database Optimization & Store Maintenance

Managing an established WooCommerce store with a large database or slow backend queries?

- **Database Health Audits:** Identify bloated tables, missing indexes, and un-optimized query patterns.
- **HPOS Migration & Cleanups:** Safe migration from legacy `wp_posts` tables to High-Performance Order Storage tables.
- **Scheduled Automated Maintenance:** Non-blocking background routines to keep transient and session bloat under control.
- **Performance Tuning:** MySQL/MariaDB query optimization and object cache configuration (Redis/Memcached).

📩 **Get in touch:** Contact me at **[sekmenwp@gmail.com](mailto:sekmenwp@gmail.com)** or connect on **[Upwork](https://www.upwork.com/freelancers/~01a7a8b58043e6e6d9)**.

---

## 🚀 Developer Setup & Local Testing

1. **Clone into plugins directory:**
   ```bash
   git clone https://github.com/harunsekmen/woo-deadlock-vacuum.git wp-content/plugins/woo-deadlock-vacuum
   ```

2. **Activate via WP-CLI:**
   ```bash
   wp plugin activate woo-deadlock-vacuum
   ```

3. **Review maintenance status:** Open **WooCommerce > DB Vacuum** in WordPress Admin to review pending transients and trigger a test cleanup.

---

## 📄 License

This open-source project is licensed under the **[GNU General Public License v3.0](LICENSE)**.

