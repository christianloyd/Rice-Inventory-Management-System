<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$c = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4", $c['user'], $c['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$sql = "
CREATE TABLE IF NOT EXISTS stock_in_returns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_no VARCHAR(30) NOT NULL UNIQUE,
    stock_in_id BIGINT UNSIGNED NOT NULL,
    stock_in_item_id BIGINT UNSIGNED NOT NULL,
    rice_variant_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    cost_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    return_date DATE NOT NULL,
    reason VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    returned_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_stock_in (stock_in_id),
    KEY idx_variant (rice_variant_id),
    KEY idx_returned_by (returned_by),
    CONSTRAINT fk_sir_stock_in FOREIGN KEY (stock_in_id) REFERENCES stock_ins(id) ON DELETE CASCADE,
    CONSTRAINT fk_sir_variant FOREIGN KEY (rice_variant_id) REFERENCES rice_variants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);
echo "Table stock_in_returns created successfully.\n";
