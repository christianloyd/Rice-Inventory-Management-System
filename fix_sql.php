<?php
$file = 'database/risms.sql';
$content = file_get_contents($file);

// Replace ") ;" or ");" for CREATE TABLE statements that lack the Engine suffix
$regex = '/\n\) \;/m';
$replacement = "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$fixed_content = preg_replace($regex, $replacement, $content);
file_put_contents('database/risms_fixed.sql', $fixed_content);

echo "Fixed " . preg_match_all($regex, $content) . " missing Engine tables.\n";
