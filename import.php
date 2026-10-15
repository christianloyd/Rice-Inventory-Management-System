<?php
require __DIR__ . '/vendor/autoload.php';
$pdo = \App\Support\Database::getInstance();
session_start();
$_SESSION['user_id'] = 1; // mock admin id

// Map paper names dynamically parsing space conventions in DB ("25 kg" vs "10 kg")
$items = [
    ['base' => 'Young Master', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1380],
    ['base' => 'Young Master', 'unit' => '10kg', 'unit_db' => '10 kg', 'price' => 570],
    ['base' => 'Young Master', 'unit' => '5kg',  'unit_db' => '5 kg', 'price' => 290],
    ['base' => 'Mang Bigas Violet', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1380],
    ['base' => 'Mang Bigas Violet', 'unit' => '10kg', 'unit_db' => '10 kg', 'price' => 570],
    ['base' => 'Mang Bigas Violet', 'unit' => '5kg',  'unit_db' => '5 kg', 'price' => 290],
    ['base' => 'Supremo Green', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1100],
    ['base' => 'Supremo Green', 'unit' => '10kg', 'unit_db' => '10 kg', 'price' => 485],
    ['base' => 'Supremo Green', 'unit' => '5kg',  'unit_db' => '5 kg', 'price' => 245],
    ['base' => 'Supremo Yellow', 'unit' => '10kg', 'unit_db' => '10 kg', 'price' => 440],
    ['base' => 'Supremo Yellow', 'unit' => '5kg',  'unit_db' => '5 kg', 'price' => 220],
    ['base' => 'Golden Buddha', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1320], // in DB it's Budhha 25kg
    ['base' => 'Palma 160', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1320],
    ['base' => 'Palma H.R.', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1120], // mapped from HR
    ['base' => 'Mais #10/#12', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 920],
    ['base' => 'Daffodil', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1050],
    
    ['base' => 'Pilit Diamond', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1160],
    ['base' => 'Punjung', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1070],
    ['base' => 'Japonica', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1230],
    ['base' => 'Peacock', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1280],
    ['base' => 'Planters', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1160],
    ['base' => 'Great Wall', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1180],
    ['base' => 'Great Wall', 'unit' => '5kg',  'unit_db' => '5 kg', 'price' => 255],
    ['base' => 'Blue Eagle', 'unit' => '25kg', 'unit_db' => '25 kg', 'price' => 1050]
];

foreach ($items as $item) {
    try {
        $fullName = $item['base'] . ' ' . $item['unit'];
        
        // Let's specifically handle special cases from previous schema matches
        if ($item['base'] === 'Golden Buddha') $fullName = 'Budhha 25kg'; // Matches old table natively
        
        $stmt = $pdo->prepare("SELECT id FROM rice_variants WHERE name COLLATE utf8mb4_unicode_ci = ?");
        $stmt->execute([$fullName]);
        $variant = $stmt->fetch();

        if ($variant) {
            $variantId = $variant['id'];
            echo "MATCHED: {$fullName} -> ID: {$variantId}\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO rice_variants (name, unit, status) VALUES (?, ?, 'active')");
            $stmt->execute([$fullName, $item['unit_db']]);
            $variantId = $pdo->lastInsertId();
            echo "INSERTED: {$fullName} -> ID: {$variantId}\n";
        }
        
        \App\Models\RicePrice::updatePrice($variantId, $item['price'], 'Aligned with Physical Catalog Price List', 1);
        echo "   -> Updated Price: {$item['price']}\n";
        
    } catch (Exception $e) {
        echo "ERROR for {$fullName}: " . $e->getMessage() . "\n";
    }
}
echo "ALL SECURELY SYNCED!\n";
