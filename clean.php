<?php
require __DIR__ . '/vendor/autoload.php';
$pdo = \App\Support\Database::getInstance();
$pdo->query('DELETE FROM rice_prices WHERE rice_variant_id >= 72');
$pdo->query('DELETE FROM rice_price_history WHERE rice_variant_id >= 72');
$pdo->query('DELETE FROM rice_variants WHERE id >= 72');
echo "Cleaned!";
