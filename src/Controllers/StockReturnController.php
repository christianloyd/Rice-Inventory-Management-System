<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Models\StockInReturn;
use App\Services\StockInReturnService;
use App\Support\Database;
use PDO;

class StockReturnController
{
    private StockInReturnService $returnService;

    public function __construct()
    {
        $this->returnService = new StockInReturnService();
    }

    public function index(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        $allReturns = StockInReturn::all();
        $stats = StockInReturn::getStats();

        // Apply filters in PHP
        $filteredReturns = array_filter($allReturns, function ($r) use ($search, $dateFrom, $dateTo) {
            if ($search !== '') {
                $q = strtolower($search);
                $matchNo = str_contains(strtolower($r['return_no'] ?? ''), $q);
                $matchSupplier = str_contains(strtolower($r['supplier_name'] ?? ''), $q);
                $matchVariant = str_contains(strtolower($r['variant_name'] ?? ''), $q);
                $matchRef = str_contains(strtolower($r['stock_in_reference'] ?? ''), $q);
                if (!$matchNo && !$matchSupplier && !$matchVariant && !$matchRef) {
                    return false;
                }
            }

            $retDate = substr($r['return_date'] ?? $r['created_at'], 0, 10);
            if ($dateFrom !== '' && $retDate < $dateFrom) {
                return false;
            }
            if ($dateTo !== '' && $retDate > $dateTo) {
                return false;
            }

            return true;
        });

        return View::render('stock-returns/index', [
            'returns'   => array_values($filteredReturns),
            'stats'     => $stats,
            'search'    => $search,
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
        ]);
    }

    public function create(Request $request): Response
    {
        $returnableItems = StockInReturn::getReturnableItems();
        $selectedItemId = (int)$request->input('item_id', 0);
        $selectedShipmentId = (int)$request->input('shipment_id', 0);

        return View::render('stock-returns/create', [
            'items'              => $returnableItems,
            'selectedItemId'     => $selectedItemId,
            'selectedShipmentId' => $selectedShipmentId,
        ]);
    }

    public function store(Request $request): Response
    {
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
            Response::setFlash('error', 'Please select a valid item and specify a return quantity greater than 0.');
            return Response::redirect(url('/stock-returns/create'));
        }

        if (empty($reason)) {
            Response::setFlash('error', 'Please provide a reason for returning the sacks.');
            return Response::redirect(url('/stock-returns/create'));
        }

        // Look up stock_in_id for this item
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT stock_in_id FROM stock_in_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $stockInId = (int)$stmt->fetchColumn();

        if ($stockInId <= 0) {
            Response::setFlash('error', 'The selected shipment item was not found.');
            return Response::redirect(url('/stock-returns/create'));
        }

        $userId = $_SESSION['user_id'] ?? 1;
        $result = $this->returnService->executeReturn(
            $stockInId,
            $itemId,
            $quantity,
            $reason,
            $returnDate,
            $userId,
            $notes
        );

        if ($result['success']) {
            Response::setFlash('success', "Return transaction {$result['return_no']} created successfully! {$quantity} sacks stocked OUT.");
            return Response::redirect(url("/stock-returns/{$result['id']}"));
        } else {
            Response::setFlash('error', $result['error'] ?? 'Failed to process return.');
            return Response::redirect(url('/stock-returns/create'));
        }
    }

    public function show(Request $request, string $id): Response
    {
        $return = StockInReturn::find((int)$id);
        if (!$return) {
            Response::setFlash('error', 'Return transaction record not found.');
            return Response::redirect(url('/stock-returns'));
        }

        return View::render('stock-returns/show', [
            'return' => $return,
        ]);
    }

    public function printSlip(Request $request, string $id): Response
    {
        $return = StockInReturn::find((int)$id);
        if (!$return) {
            Response::setFlash('error', 'Return transaction record not found.');
            return Response::redirect(url('/stock-returns'));
        }

        return View::render('stock-returns/print', [
            'return' => $return,
        ]);
    }
}
