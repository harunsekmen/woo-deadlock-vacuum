<?php
require_once __DIR__ . '/bootstrap.php';
if (!function_exists('get_option')) { function get_option($k, $d = []) { return $d; } }
require_once dirname(__DIR__) . '/includes/class-plugin.php';
require_once dirname(__DIR__) . '/includes/class-vacuum-engine.php';

echo "Testing WooDeadlockVacuum (VacuumEngine Chunking)...
";

$engine = new \WooDeadlockVacuum\VacuumEngine();
$chunk_size = $engine->get_chunk_size();
assert($chunk_size === 500, "Safe deadlock-free chunk size must be 500");

echo "PASS: woo-deadlock-vacuum verified.
";
