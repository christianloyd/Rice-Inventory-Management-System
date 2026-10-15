<?php
namespace App\Controllers;

use App\Services\TransactionService;
use App\Support\Database;
use App\Support\Response;
use App\Support\View;

class TransactionController
{
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function create(): Response
    {
        $pdo = Database::getInstance();
        $variants = $pdo->query("SELECT * FROM rice_variants WHERE status = 'active' ORDER BY name")->fetchAll();

        return View::render('transactions/create', [
            'variants' => $variants
        ]);
    }

    public function store(): Response
    {
        // Add CSRF validation and idempotency checking here if they are integrated in the router 
        // We'll extract POST payload
        $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
        $items = $_POST['items'] ?? [];

        if (empty($items)) {
            $_SESSION['error'] = 'At least one item is required.';
            return Response::redirect(url('/transactions/new'));
        }

        $header = [
            'transaction_date' => $transactionDate,
            'created_by' => $_SESSION['user_id']
        ];

        $result = $this->transactionService->createTransaction($header, $items);

        if ($result['success']) {
            $_SESSION['success'] = 'Transaction recorded successfully.';
            return Response::redirect(url('/transactions/new'));
        } else {
            $_SESSION['error'] = 'Failed to record transaction: ' . $result['error'];
            return Response::redirect(url('/transactions/new'));
        }
    }
}
