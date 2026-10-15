<?php
try {
  $pdo = new PDO('mysql:host=localhost;dbname=risms', 'root', ''); 
  $sql = file_get_contents('database/risms (1).sql');
  $pdo->exec('DROP DATABASE IF EXISTS test_risms2;');
  $pdo->exec('CREATE DATABASE IF NOT EXISTS test_risms2;');
  $pdo->exec('USE test_risms2;');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->exec($sql);
  echo "Success";
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
