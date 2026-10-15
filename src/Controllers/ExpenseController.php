<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Services\ExpenseService;

class ExpenseController
{
    private ExpenseService $expenseService;

    public function __construct()
    {
        $this->expenseService = new ExpenseService();
    }

    public function index(Request $request): Response
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        $expenses = $this->expenseService->getExpenses($startDate, $endDate);
        
        $totalExpenses = array_sum(array_column($expenses, 'amount'));

        // Load variants for Konsumo option
        $variants = (new \App\Models\RiceVariant())->all();

        return View::render('expenses/index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'variants' => $variants
        ]);
    }

    public function store(Request $request): Response
    {
        $description = trim($request->input('description', ''));
        $category = trim($request->input('category', 'Utilities'));
        $amount = (float) $request->input('amount', 0);
        $expenseDate = $request->input('expense_date', date('Y-m-d'));

        $riceVariantId = null;
        $riceQty = null;

        if ($category === 'Konsumo') {
            $amount = 0.0;
            $riceVariantId = $request->input('rice_variant_id') ? (int) $request->input('rice_variant_id') : null;
            $riceQty = $request->input('rice_qty') ? (float) $request->input('rice_qty') : null;

            if (!$riceVariantId || !$riceQty || $riceQty <= 0) {
                Response::setFlash('error', 'Konsumo requires a valid Rice Variant and Quantity.');
                return Response::redirect(url('/expenses'));
            }
        } elseif (empty($description) || $amount <= 0) {
            Response::setFlash('error', 'Description and valid amount are required.');
            return Response::redirect(url('/expenses'));
        }

        $userId = $_SESSION['user_id'];

        $this->expenseService->createExpense([
            'description' => $description,
            'category' => $category,
            'amount' => $amount,
            'expense_date' => $expenseDate,
            'rice_variant_id' => $riceVariantId,
            'rice_qty' => $riceQty
        ], $userId);

        Response::setFlash('success', 'Expense recorded successfully.');
        return Response::redirect(url('/expenses'));
    }

    public function delete(Request $request, int $id): Response
    {
        $this->expenseService->deleteExpense($id);
        Response::setFlash('success', 'Expense deleted successfully.');
        return Response::redirect(url('/expenses'));
    }
}
