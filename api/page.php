<?php
// Dynamic page handler — serves /pages/{slug}.json configs
$slug = $_GET['slug'] ?? '';
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($slug)));

$pageFile = __DIR__ . '/../pages/' . $slug . '.json';

if (!$slug || !file_exists($pageFile)) {
    // Fall back to main config if slug not found
    $configPath = __DIR__ . '/../config.json';
    $config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
} else {
    $config = json_decode(file_get_contents($pageFile), true);
}

require_once __DIR__ . '/render.php';
render_page($config);
