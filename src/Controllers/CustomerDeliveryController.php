<?php

namespace App\Controllers;

use App\Services\CustomerDeliveryService;
use App\Support\Database;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;

class CustomerDeliveryController
{
    private CustomerDeliveryService $service;

    public function __construct()
    {
        $this->service = new CustomerDeliveryService();
    }

    // GET /deliveries
    public function index(Request $request): Response
    {
        $filter = $_GET['type']   ?? 'all';
        $search = trim($_GET['search'] ?? '');

        $filters = ['search' => $search ?: null];
        if (in_array($filter, ['cash', 'utang'])) {
            $filters['payment_type'] = $filter;
        }

        $deliveries = $this->service->list($filters);

        // Attach items summary per delivery
        $pdo = Database::getInstance();
        foreach ($deliveries as &$d) {
            $stmt = $pdo->prepare("
                SELECT GROUP_CONCAT(rv.name ORDER BY rv.name SEPARATOR ', ') as items_summary
                FROM customer_delivery_items cdi
                JOIN rice_variants rv ON cdi.rice_variant_id = rv.id
                WHERE cdi.delivery_id = ?
            ");
            $stmt->execute([$d['id']]);
            $d['items_summary'] = $stmt->fetchColumn() ?? '–';
        }
        unset($d);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $total = count($deliveries);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        
        $pagedDeliveries = array_slice($deliveries, ($page - 1) * $perPage, $perPage);

        return View::render('customer-deliveries/index', [
            'deliveries' => $pagedDeliveries,
            'filter'     => $filter,
            'search'     => $search,
            'page'       => $page,
            'pages'      => $pages,
            'total'      => $total,
            'title'      => 'Deliveries',
        ]);
    }

    // GET /deliveries/{id}
    public function show(Request $request, string $id): Response
    {
        $delivery = $this->service->find((int)$id);
        if (!$delivery) {
            return new Response('Delivery not found.', 404);
        }

        return View::render('customer-deliveries/show', [
            'delivery' => $delivery,
            'title'    => 'Delivery Details – ' . $delivery['receipt_no'],
        ]);
    }

    // GET /deliveries/create
    public function create(Request $request): Response
    {
        $pdo      = Database::getInstance();
        $variants = $pdo->query("
            SELECT rv.id, rv.name, rv.unit, rp.current_price as unit_price,
                   rv.current_stock_cache as stock
            FROM rice_variants rv
            LEFT JOIN rice_prices rp ON rp.rice_variant_id = rv.id
                AND rp.id = (SELECT MAX(id) FROM rice_prices rp2 WHERE rp2.rice_variant_id = rv.id)
            WHERE rv.status = 'active'
            ORDER BY rv.name
        ")->fetchAll();

        return View::render('customer-deliveries/create', [
            'variants' => $variants,
            'title'    => 'New Delivery',
        ]);
    }

    // POST /deliveries
    public function store(Request $request): Response
    {
        $customerName  = trim($_POST['customer_name'] ?? '');
        $deliveryDate  = $_POST['delivery_date'] ?? date('Y-m-d');
        $paymentType   = $_POST['payment_type'] ?? 'cash';
        $notes         = trim($_POST['notes'] ?? '');

        $variantIds  = $_POST['rice_variant_id'] ?? [];
        $quantities  = $_POST['quantity']         ?? [];
        $unitPrices  = $_POST['unit_price']       ?? [];
        $discounts   = $_POST['discount_per_sack'] ?? [];

        if (!$customerName) {
            $_SESSION['error'] = 'Customer name is required.';
            return Response::redirect(url('/deliveries/create'));
        }
        if (!in_array($paymentType, ['cash', 'utang', 'owner'])) {
            $paymentType = 'cash';
        }

        $items = [];
        foreach ($variantIds as $i => $vid) {
            $qty   = (float)($quantities[$i]  ?? 0);
            $price = (float)($unitPrices[$i]  ?? 0);
            $disc  = (float)($discounts[$i]   ?? 0);
            
            if ($vid && $qty > 0) {
                // Ensure discount doesn't exceed price (only if price > 0)
                $disc = $price > 0 ? min($disc, $price) : 0;
                $items[] = [
                    'rice_variant_id'   => (int)$vid,
                    'quantity'          => $qty,
                    'unit_price'        => $price,
                    'discount_per_sack' => $disc,
                ];
            }
        }

        if (empty($items)) {
            $_SESSION['error'] = 'At least one valid item is required.';
            return Response::redirect(url('/deliveries/create'));
        }

        $result = $this->service->create([
            'customer_name'   => $customerName,
            'delivery_date'   => $deliveryDate,
            'payment_type'    => $paymentType,
            'amount_tendered' => isset($_POST['amount_tendered']) ? (float)$_POST['amount_tendered'] : 0.0,
            'notes'           => $notes,
            'items'           => $items,
            'recorded_by'     => $_SESSION['user_id'],
        ]);

        if ($result['success']) {
            // Redirect to receipt page which auto-prints
            return Response::redirect(url('/deliveries/' . $result['delivery_id'] . '/receipt'));
        }

        $_SESSION['error'] = 'Failed to save delivery: ' . $result['error'];
        return Response::redirect(url('/deliveries/create'));
    }

    // GET /deliveries/{id}/receipt
    public function receipt(Request $request, string $id): Response
    {
        $delivery = $this->service->find((int)$id);
        if (!$delivery) {
            return new Response('Delivery not found.', 404);
        }

        // Bridge to the underlying Sale to trigger Control Number assignment
        $db = \App\Support\Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM sales WHERE transaction_no = ? LIMIT 1");
        $stmt->execute([$delivery['receipt_no']]);
        $saleId = $stmt->fetchColumn();

        $controlNumber = '';
        if ($saleId) {
            $controlNumber = \App\Models\Sale::assignControlNumber((int)$saleId);
        }

        return View::render('customer-deliveries/receipt', [
            'delivery'      => $delivery,
            'controlNumber' => $controlNumber,
            'app_name'      => \App\Models\Setting::get('app_name', 'MAE CONSUMER'),
            'title'         => 'Delivery Receipt ' . $delivery['receipt_no'],
        ]);
    }

    // POST /deliveries/{id}/pay
    public function pay(Request $request, string $id): Response
    {
        header('Content-Type: application/json');
        while (ob_get_level() > 0) ob_end_clean();

        $amount = (float)($request->input('amount', 0));
        $result = $this->service->pay((int)$id, $amount, $_SESSION['user_id']);

        if ($result['success']) {
            return Response::json(['success' => true, 'new_balance' => $result['new_balance'], 'pay_status' => $result['pay_status']]);
        }
        return Response::json(['success' => false, 'error' => $result['error']], 400);
    }
}
