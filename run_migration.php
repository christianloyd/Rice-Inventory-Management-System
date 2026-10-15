<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = App\Support\Database::getInstance();
$sql = file_get_contents(__DIR__ . '/database/migrations/021_add_discount_to_delivery_items.sql');
$db->exec($sql);
echo "Migration 021 applied successfully.\n";
