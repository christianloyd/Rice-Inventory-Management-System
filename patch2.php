<?php
require 'vendor/autoload.php';
require 'config/database.php';
$pdo = App\Support\Database::getInstance();
$pdo->exec("UPDATE customer_deliveries SET total_amount = 0, amount_paid = 0, balance = 0, pay_status = 'paid' WHERE payment_type = 'owner'");
$pdo->exec("UPDATE customer_delivery_items cdi JOIN customer_deliveries cd ON cdi.delivery_id = cd.id SET cdi.unit_price = 0, cdi.subtotal = 0 WHERE cd.payment_type = 'owner'");
echo "Success\n";
