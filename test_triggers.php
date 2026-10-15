<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require 'config/app.php';
$pdo = App\Support\Database::getInstance();
$stmt = $pdo->query('SHOW TRIGGERS');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
