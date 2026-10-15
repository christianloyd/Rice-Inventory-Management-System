<?php

namespace App\Models;

use App\Support\Database;

class Setting
{
    /**
     * Get all settings as an associative key => value array.
     */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    /**
     * Get a single setting value by key.
     */
    public static function get(string $key, string $default = ''): string
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    }

    /**
     * Persist a key => value pair.
     */
    public static function set(string $key, string $value): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, $value]);
    }
}
