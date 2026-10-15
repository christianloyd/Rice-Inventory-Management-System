<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=risms;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    
    $stmt = $pdo->query("
        SELECT s.id as sale_id, s.customer_name, si.id as item_id, v.name as variant, si.quantity, si.remark
        FROM sales s
        JOIN sale_items si ON s.id = si.sale_id
        JOIN rice_variants v ON si.rice_variant_id = v.id
        WHERE DATE(s.sale_date) = CURDATE() AND s.customer_name LIKE '%Greg%'
    ");
    echo "Greg's Transactions Today:\n";
    print_r($stmt->fetchAll());

} catch (Exception $e) {
    echo $e->getMessage();
}
