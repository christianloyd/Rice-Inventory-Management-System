<?php
namespace App\Controllers;

use App\Services\StockDeliveryService;
use App\Support\Database;
use App\Support\Response;
use App\Support\View;

class StockDeliveryController
{
    private StockDeliveryService $deliveryService;

    public function __construct()
    {
        $this->deliveryService = new StockDeliveryService();
    }

    public function index(): Response
    {
        $pdo = Database::getInstance();
        $deliveries = $pdo->query(
            "SELECT sd.*, rv.name as variant_name, u.full_name as recorder_name 
             FROM stock_deliveries sd 
             JOIN rice_variants rv ON sd.rice_variant_id = rv.id 
             JOIN users u ON sd.created_by = u.id 
             ORDER BY sd.stock_in_date DESC, sd.id DESC"
        )->fetchAll();

        return View::render('stock-deliveries/index', [
            'deliveries' => $deliveries
        ]);
    }

    public function create(): Response
    {
        $pdo = Database::getInstance();
        $variants = $pdo->query("SELECT * FROM rice_variants WHERE status = 'active' ORDER BY name")->fetchAll();

        return View::render('stock-deliveries/create', [
            'variants' => $variants
        ]);
    }

    public function store(): Response
    {
        $data = [
            'stock_in_date' => $_POST['stock_in_date'] ?? date('Y-m-d'),
            'rice_variant_id' => $_POST['rice_variant_id'] ?? null,
            'quantity' => $_POST['quantity'] ?? null,
            'reference_note' => $_POST['reference_note'] ?? null,
            'created_by' => $_SESSION['user_id']
        ];

        if (!$data['rice_variant_id'] || !$data['quantity']) {
            $_SESSION['error'] = 'Variant and quantity are required.';
            return Response::redirect(url('/deliveries/new'));
        }

        $result = $this->deliveryService->createStockDelivery($data);

        if ($result['success']) {
            $_SESSION['success'] = 'Delivery recorded successfully.';
            return Response::redirect(url('/deliveries'));
        } else {
            $_SESSION['error'] = 'Failed to record delivery: ' . $result['error'];
            return Response::redirect(url('/deliveries/new'));
        }
    }
}
