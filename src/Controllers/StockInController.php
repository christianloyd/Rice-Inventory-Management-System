<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\StockInReturn;
use App\Models\RiceVariant;
use App\Services\AuditService;
use App\Services\StockInService;
use App\Services\StockInReturnService;

class StockInController
{
    private AuditService $auditService;
    private StockInService $stockInService;
    private StockInReturnService $stockInReturnService;

    public function __construct()
    {
        $this->auditService = new AuditService();
        $this->stockInService = new StockInService();
        $this->stockInReturnService = new StockInReturnService();
    }

    public function index(Request $request): Response
    {
        $stockIns = StockIn::all();
        $returns = StockInReturn::all();
        $returnStats = StockInReturn::getStats();
        $activeTab = $request->input('tab', 'shipments');

        return View::render('stock-in/index', [
            'stockIns'    => $stockIns,
            'returns'     => $returns,
            'returnStats' => $returnStats,
            'activeTab'   => $activeTab,
        ]);
    }

    public function create(Request $request): Response
    {
        $variants = RiceVariant::all();
        return View::render('stock-in/create', ['variants' => $variants]);
    }

    public function store(Request $request): Response
    {
        $supplier = $request->input('supplier_name');
        $reference = $request->input('reference_number', '');
        $notes = $request->input('notes', '');
        $itemsRaw = $request->input('variants', []);
        $quantities = $request->input('quantities', []);
        $costs = $request->input('costs', []);

        $items = [];
        foreach ($itemsRaw as $idx => $vId) {
            if (!empty($vId) && !empty($quantities[$idx])) {
                $items[] = [
                    'rice_variant_id' => (int) $vId,
                    'quantity' => (float) $quantities[$idx],
                    'cost_price' => (float) ($costs[$idx] ?: 0)
                ];
            }
        }

        if (empty($items)) {
            Response::setFlash('error', 'You must add at least one item.');
            return Response::redirect(url('/stock-ins/create'));
        }

        try {
            $stockInDate = $request->input('stock_in_date', date('Y-m-d'));
            $result = $this->stockInService->createStockIn(
                $reference, $supplier, $stockInDate, $itemsRaw, $quantities, $costs, $_SESSION['user_id'], $notes
            );
            
            if ($result['success']) {
                Response::setFlash('success', 'Stock-In recorded successfully.');
                return Response::redirect(url("/stock-ins/{$result['id']}"));
            } else {
                Response::setFlash('error', $result['error']);
                return Response::redirect(url('/stock-ins/create'));
            }
        } catch (\Exception $e) {
            Response::setFlash('error', $e->getMessage());
            return Response::redirect(url('/stock-ins/create'));
        }
    }

    public function overrideForm(Request $request): Response
    {
        $variants = RiceVariant::all();
        return View::render('stock-in/override', ['variants' => $variants]);
    }

    public function overrideStore(Request $request): Response
    {
        $variantId = (int) $request->input('variant_id');
        $absoluteQty = $request->input('absolute_quantity');
        $reason = $request->input('reason', 'Manual Absolute Override');

        if ($variantId <= 0 || $absoluteQty === '' || !is_numeric($absoluteQty) || $absoluteQty < 0) {
            Response::setFlash('error', 'Please provide a valid Rice Variant and a precise Absolute Quantity (0 or higher).');
            return Response::redirect(url('/stock-ins/override'));
        }

        try {
            $service = new \App\Services\StockAdjustmentService();
            $result = $service->execute($variantId, (float)$absoluteQty, $reason, $_SESSION['user_id']);

            if ($result['success']) {
                Response::setFlash('success', $result['message']);
                return Response::redirect(url('/stock-ins'));
            } else {
                Response::setFlash('error', $result['error']);
                return Response::redirect(url('/stock-ins/override'));
            }
        } catch (\Exception $e) {
            Response::setFlash('error', 'A system error occurred completing the override.');
            return Response::redirect(url('/stock-ins/override'));
        }
    }

    public function show(Request $request, string $id): Response
    {
        $stockIn = StockIn::find((int)$id);
        if (!$stockIn) {
            Response::setFlash('error', 'Record not found.');
            return Response::redirect(url('/stock-ins'));
        }

        $items = StockInItem::getByStockIn((int)$id);
        $returns = StockInReturn::getByStockIn((int)$id);
        return View::render('stock-in/show', [
            'stockIn' => $stockIn,
            'items'   => $items,
            'returns' => $returns,
        ]);
    }

    public function processReturn(Request $request, string $id): Response
    {
        $stockInId = (int)$id;
        $itemId = (int)$request->input('stock_in_item_id');
        $quantity = (float)$request->input('return_quantity');
        $reason = trim((string)$request->input('reason', ''));
        $customReason = trim((string)$request->input('custom_reason', ''));
        $returnDate = $request->input('return_date', date('Y-m-d'));
        $notes = trim((string)$request->input('notes', ''));

        if ($reason === 'other' && !empty($customReason)) {
            $reason = $customReason;
        }

        if ($itemId <= 0 || $quantity <= 0) {
            Response::setFlash('error', 'Please select a valid item and enter a return quantity greater than 0.');
            return Response::redirect(url("/stock-ins/{$id}"));
        }

        if (empty($reason)) {
            Response::setFlash('error', 'Please specify a reason for returning the sacks.');
            return Response::redirect(url("/stock-ins/{$id}"));
        }

        $result = $this->stockInReturnService->executeReturn(
            $stockInId,
            $itemId,
            $quantity,
            $reason,
            $returnDate,
            $_SESSION['user_id'] ?? 1,
            $notes
        );

        if ($result['success']) {
            Response::setFlash('success', "Return {$result['return_no']} processed successfully. {$quantity} sacks deducted from inventory (Stock OUT).");
        } else {
            Response::setFlash('error', $result['error'] ?? 'Failed to process return.');
        }

        return Response::redirect(url("/stock-ins/{$id}"));
    }

    public function cancel(Request $request, string $id): Response
    {
        try {
            StockIn::cancel((int)$id, $_SESSION['user_id']);
            $this->auditService->log($_SESSION['user_id'], 'CANCEL', 'stock', 'stock_ins', $id);
            Response::setFlash('success', 'Stock-In cancelled and inventory reverted.');
        } catch (\Exception $e) {
            Response::setFlash('error', $e->getMessage() ?: 'Failed to cancel record.');
        }
        return Response::redirect(url("/stock-ins/{$id}"));
    }
}
