<?php
require __DIR__ . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
$pdo = \App\Support\Database::getInstance();

echo "--- CUSTOMER DELIVERY ITEMS COLUMNS ---\n";
foreach ($pdo->query("DESCRIBE customer_delivery_items")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ")\n";
}
