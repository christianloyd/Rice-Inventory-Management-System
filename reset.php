<?php
try {
  $pdo = new PDO('mysql:host=localhost;dbname=risms', 'root', ''); 
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->exec('UPDATE rice_variants SET current_stock_cache = 0;');
  echo 'Success: Set all stock caches to 0.';
} catch (PDOException $e) {
  echo 'Error: ' . $e->getMessage();
}