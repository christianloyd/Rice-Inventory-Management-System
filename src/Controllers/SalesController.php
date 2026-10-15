<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\RicePrice;
use App\Services\AuditService;
use App\Services\ExchangeService;

use App\Models\User;

class SalesController
{
    private AuditService $auditService;
    private \App\Services\SaleService $saleService;
    private ExchangeService $exchangeService;

    public function __construct()
    {
        $this->auditService    = new AuditService();
        $this->saleService     = new \App\Services\SaleService();
        $this->exchangeService = new ExchangeService();
    }

    public function index(Request $request): Response
    {
        $search     = trim($_GET['search'] ?? '');
        $status     = trim($_GET['status'] ?? 'all');
        $payFilter  = trim($_GET['pay'] ?? 'all');
        $dateFrom   = trim($_GET['date_from'] ?? '');
        $dateTo     = trim($_GET['date_to'] ?? '');
        $datePreset = trim($_GET['preset'] ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = 10;

        // Apply date preset shortcuts
        if ($datePreset === 'today') {
            $dateFrom = date('Y-m-d');
            $dateTo   = date('Y-m-d');
        }

        $pdo = \App\Support\Database::getInstance();

        // ── Exclude delivery-linked rows (DR- prefix or delivery notes) ──────
        $query = "
            SELECT s.*, u.full_name as cashier_name 
            FROM sales s 
            LEFT JOIN users u ON s.recorded_by = u.id 
            WHERE 1=1
            AND s.transaction_no NOT LIKE 'DR-%'
            AND (s.notes IS NULL OR (
                s.notes NOT LIKE 'Delivery - %'
                AND s.notes NOT LIKE 'Utang payment settled - %'
            ))
        ";
        $params = [];

        if ($search !== '') {
            $query .= " AND (s.transaction_no LIKE ? OR s.customer_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status !== 'all' && in_array($status, ['completed', 'cancelled', 'exchanged'])) {
            $query .= " AND s.status = ?";
            $params[] = $status;
        }

        // ── Payment method / type filter ──────────────────────────────────────
        if ($payFilter === 'cash') {
            $query .= " AND s.payment_method = 'cash'
                        AND NOT EXISTS (
                            SELECT 1 FROM sale_items si
                            WHERE si.sale_id = s.id AND si.remark IN ('utang','tbt')
                        )";
        } elseif ($payFilter === 'cheque') {
            $query .= " AND s.payment_method = 'cheque'";
        } elseif ($payFilter === 'utang') {
            $query .= " AND EXISTS (
                            SELECT 1 FROM sale_items si
                            WHERE si.sale_id = s.id AND si.remark = 'utang'
                        )";
        } elseif ($payFilter === 'tbt') {
            $query .= " AND EXISTS (
                            SELECT 1 FROM sale_items si
                            WHERE si.sale_id = s.id AND si.remark = 'tbt'
                        )";
        }

        // ── Date range filter ─────────────────────────────────────────────────
        if ($dateFrom !== '') {
            $query .= " AND DATE(s.sale_date) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $query .= " AND DATE(s.sale_date) <= ?";
            $params[] = $dateTo;
        }

        $query .= " ORDER BY s.created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $all = $stmt->fetchAll();

        $total = count($all);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $sales = array_slice($all, ($page - 1) * $perPage, $perPage);

        return View::render('sales/index', [
            'sales'      => $sales,
            'search'     => $search,
            'status'     => $status,
            'payFilter'  => $payFilter,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'datePreset' => $datePreset,
            'page'       => $page,
            'pages'      => $pages,
            'total'      => $total,
        ]);
    }

    public function create(Request $request): Response
    {
        // For POS, we need active variants with their current price and current stock
        $pdo = \App\Support\Database::getInstance();
        $variants = $pdo->query("
            SELECT v.id, v.name, v.unit, v.allow_wholesale_discount, v.current_stock_cache as stock, p.current_price as price
            FROM rice_variants v
            LEFT JOIN rice_prices p ON p.rice_variant_id = v.id
            WHERE v.status = 'active'
        ")->fetchAll();

        return View::render('sales/create', ['variants' => $variants]);
    }

    public function store(Request $request): Response
    {
        $customerName = $request->input('customer_name', 'Walk-in Customer');
        $amountTendered = (float)$request->input('amount_tendered', '0');
        $globalDiscount = (float)$request->input('discount', '0');
        $notes = $request->input('notes', '');
        $saleDate = $request->input('sale_date', date('Y-m-d'));
        
        $paymentMethod = $request->input('payment_method', 'cash');
        $chequeNumber = $request->input('cheque_number', null);
        $chequeDate = $request->input('cheque_date', null);

        $itemsRaw = $request->input('variants', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        $remarks = $request->input('remarks', []);
        $quantitiesTaken = $request->input('quantities_taken', []);

        $items = [];
        foreach ($itemsRaw as $idx => $vId) {
            if (!empty($vId) && !empty($quantities[$idx])) {
                $items[] = [
                    'rice_variant_id' => (int) $vId,
                    'quantity' => (float) $quantities[$idx],
                    'unit_price' => (float) ($prices[$idx] ?: 0)
                ];
            }
        }

        if (empty($items)) {
            Response::setFlash('error', 'Cannot process an empty sale.');
            return Response::redirect(url('/sales/create'));
        }

        try {
            $notesArr = $notes ? [$notes] : [];
            $result = $this->saleService->createSale(
                $itemsRaw, 
                $quantities, 
                $prices, 
                $remarks, 
                $customerName, 
                $paymentMethod, 
                $amountTendered, 
                $_SESSION['user_id'], 
                $notesArr,
                $globalDiscount,
                $saleDate,
                $quantitiesTaken,
                $chequeNumber,
                $chequeDate
            );
            
            if ($result['success']) {
                Response::setFlash('success', 'Sale completed successfully.');
                // Redirect directly to the Control Sheet receipt and trigger auto-print
                return Response::redirect(url("/sales/{$result['id']}/receipt?autoprint=1"));
            } else {
                Response::setFlash('error', $result['error']);
                return Response::redirect(url('/sales/create'));
            }
        } catch (\Exception $e) {
            Response::setFlash('error', $e->getMessage());
            return Response::redirect(url('/sales/create'));
        }
    }

    public function show(Request $request, string $id): Response
    {
        $sale = Sale::find((int)$id);
        if (!$sale) {
            Response::setFlash('error', 'Sale record not found.');
            return Response::redirect(url('/sales'));
        }

        $replacementSale = null;
        if ($sale['status'] === 'exchanged' && !empty($sale['exchanged_to_sale_id'])) {
            $replacementSale = Sale::find((int)$sale['exchanged_to_sale_id']);
        }

        $items = SaleItem::getBySale((int)$id);
        return View::render('sales/show', ['sale' => $sale, 'items' => $items, 'replacementSale' => $replacementSale]);
    }

    public function receipt(Request $request, string $id): Response
    {
        $sale = Sale::find((int)$id);
        if (!$sale) {
            Response::setFlash('error', 'Sale record not found.');
            return Response::redirect(url('/sales'));
        }

        // Assign on first print; return the same number on subsequent prints.
        $controlNumber = Sale::assignControlNumber((int)$id);

        // Always use the LIVE session user for accountability, not the recorded_by user.
        $printedBy = '';
        if (!empty($_SESSION['user_id'])) {
            $sessionUser = User::findById((int)$_SESSION['user_id']);
            $printedBy   = $sessionUser['full_name'] ?? ($sessionUser['username'] ?? '—');
        }

        $items = SaleItem::getBySale((int)$id);

        return View::render('sales/receipt', [
            'sale'          => $sale,
            'items'         => $items,
            'controlNumber' => $controlNumber,
            'printedBy'     => $printedBy,
        ]);
    }

    public function cancel(Request $request, string $id): Response
    {
        $adminPassword = $request->input('admin_password', '');
        if (empty($adminPassword)) {
            Response::setFlash('error', 'Admin authorization password is required to void sales.');
            return Response::redirect(url("/sales/{$id}"));
        }

        $pdo = \App\Support\Database::getInstance();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE role = 'admin' AND is_active = 1");
        $stmt->execute();
        $admins = $stmt->fetchAll();

        $authorized = false;
        foreach ($admins as $admin) {
            if (password_verify($adminPassword, $admin['password_hash'])) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            Response::setFlash('error', 'Unauthorized: The provided Admin password does not match or is invalid.');
            return Response::redirect(url("/sales/{$id}"));
        }

        try {
            Sale::cancel((int)$id, (int)$_SESSION['user_id']);
            $this->auditService->log($_SESSION['user_id'], 'CANCEL', 'sales', 'sales', $id);
            Response::setFlash('success', 'Sale cancelled and inventory restored.');
        } catch (\Exception $e) {
            Response::setFlash('error', 'Failed to cancel sale.');
        }
        return Response::redirect(url("/sales/{$id}"));
    }

    public function exchangeForm(Request $request): Response
    {
        $query  = trim($request->input('q', ''));
        $date   = trim($request->input('date', ''));
        $saleId = (int) $request->input('sale_id', 0);

        $searchResults = [];
        $originalSale  = null;
        $originalItems = [];

        // If a specific original sale was chosen, load its details
        if ($saleId > 0) {
            $originalSale  = Sale::find($saleId);
            if (!$originalSale || $originalSale['status'] !== 'completed') {
                $originalSale = null;
                Response::setFlash('error', 'Sale not found or is not eligible for exchange.');
            } else {
                $originalItems = \App\Models\SaleItem::getBySale($saleId);
            }
        } elseif ($query !== '') {
            $searchResults = $this->exchangeService->searchOriginalSale(
                $query,
                $date !== '' ? $date : null
            );
        }

        // Active variants for the new-variant dropdown
        $pdo      = \App\Support\Database::getInstance();
        $variants = $pdo->query("
            SELECT v.id, v.name, v.unit, v.current_stock_cache as stock, p.current_price as price
            FROM rice_variants v
            LEFT JOIN rice_prices p ON p.rice_variant_id = v.id
            WHERE v.status = 'active'
            ORDER BY v.name ASC
        ")->fetchAll();

        return View::render('sales/exchange', [
            'query'         => $query,
            'date'          => $date,
            'searchResults' => $searchResults,
            'originalSale'  => $originalSale,
            'originalItems' => $originalItems,
            'variants'      => $variants,
        ]);
    }

    public function exchangeStore(Request $request): Response
    {
        $originalSaleId = (int) $request->input('original_sale_id', 0);
        $newVariantId   = (int) $request->input('new_variant_id', 0);
        $requestedQty   = max(1, (float) $request->input('quantity', 1));
        $customerName   = trim($request->input('customer_name', 'Walk-in Customer'));
        $amountTendered = $request->input('amount_tendered') !== null ? (float) $request->input('amount_tendered') : null;

        if ($originalSaleId <= 0 || $newVariantId <= 0) {
            Response::setFlash('error', 'Invalid exchange request. Please select a sale and a new variant.');
            return Response::redirect(url('/sales/exchange'));
        }

        $result = $this->exchangeService->execute(
            $originalSaleId,
            $newVariantId,
            $requestedQty,
            $customerName ?: 'Walk-in Customer',
            (int) $_SESSION['user_id'],
            $amountTendered
        );

        if ($result['success']) {
            $this->auditService->log(
                $_SESSION['user_id'],
                'EXCHANGE',
                'sales',
                'sales',
                $originalSaleId
            );
            Response::setFlash('success', 'Exchange completed. New receipt is ready.');
            return Response::redirect(url("/sales/{$result['new_sale_id']}/receipt?autoprint=1"));
        }

        Response::setFlash('error', $result['error']);
        return Response::redirect(url("/sales/exchange?sale_id={$originalSaleId}"));
    }
}
