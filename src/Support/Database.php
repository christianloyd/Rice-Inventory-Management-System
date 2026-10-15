<?php

namespace App\Support;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset=utf8mb4";
            
            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                
                // Align session timezone with PHP
                $tz = require __DIR__ . '/../../config/app.php';
                $dt = new \DateTime("now", new \DateTimeZone($tz['timezone']));
                $offset = $dt->format('P');
                self::$instance->exec("SET time_zone = '{$offset}';");
                
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
