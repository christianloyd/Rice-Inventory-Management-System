<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable('.')->safeLoad();
$pdo = App\Support\Database::getInstance();
$r = $pdo->query("SHOW TRIGGERS")->fetchAll(\PDO::FETCH_ASSOC);
print_r($r);
