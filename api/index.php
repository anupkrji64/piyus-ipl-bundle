<?php
$configPath = __DIR__ . '/../config.json';
$config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
require_once __DIR__ . '/render.php';
render_page($config);
