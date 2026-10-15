<?php
namespace App\Controllers;

use App\Services\UtangService;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use Exception;

class UtangController
{
    private UtangService $utangService;

    public function __construct()
    {
        $this->utangService = new UtangService();
    }

    public function index(Request $request): Response
    {
        $filters = [
            'status' => $_GET['status'] ?? 'outstanding',
            'search' => $_GET['search'] ?? null
        ];

        $allRecords = $this->utangService->listRecords($filters);
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $total = count($allRecords);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $records = array_slice($allRecords, ($page - 1) * $perPage, $perPage);

        return View::render('utang/index', [
            'filters' => $filters,
            'records' => $records,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'search' => $filters['search'],
        ]);
    }

    public function print(Request $request): Response
    {
        $filters = [
            'status' => $_GET['status'] ?? 'outstanding',
            'search' => $_GET['search'] ?? null
        ];

        $records = $this->utangService->listRecords($filters);
        
        return View::render('utang/print', [
            'filters' => $filters,
            'records' => $records,
            'search'  => $filters['search'],
        ]);
    }

    public function pay(Request $request, string $id): Response
    {
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::json(['success' => false, 'error' => 'Invalid request method.']);
        }

        $amount = (float) $request->input('amount', 0);
        
        $result = $this->utangService->addPayment((int)$id, $amount, $_SESSION['user_id']);
        
        if ($result['success']) {
            return Response::json(['success' => true, 'new_balance' => $result['new_balance']]);
        } else {
            return Response::json(['success' => false, 'error' => $result['error']], 400);
        }
    }
}
