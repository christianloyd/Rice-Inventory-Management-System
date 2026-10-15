<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Support\Validator;
use App\Models\RiceVariant;
use App\Models\RicePrice;
use App\Models\RicePriceHistory;
use App\Services\AuditService;

class CatalogController
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function index(Request $request): Response
    {
        $variants = RiceVariant::allWithCurrentPrice();
        
        $limit = (int) $request->input('limit', '100');
        $history = RicePriceHistory::getHistory($limit);
        
        return View::render('catalog/index', [
            'variants' => $variants,
            'history' => $history
        ]);
    }

    public function storeVariant(Request $request): Response
    {
        $v = Validator::make($request->all());
        if (!$v->validate([
            'name' => 'required',
            'minimum_stock' => 'required|numeric|min:0',
            'initial_price' => 'numeric|min:0',
            'unit' => 'required'
        ])) {
            Response::setFlash('error', 'Validation failed. Please check inputs.');
            return Response::redirect(url('/catalog'));
        }

        try {
            $allowWholesale = $request->input('allow_wholesale_discount') ? 1 : 0;
            RiceVariant::create(
                $request->input('name'),
                $request->input('description', ''),
                (float) $request->input('minimum_stock'),
                (float) $request->input('initial_price', '0'),
                $request->input('unit'),
                $_SESSION['user_id'],
                $allowWholesale
            );
            
            $this->auditService->log($_SESSION['user_id'], 'CREATE', 'variants', 'rice_variants', null, null, ['name' => $request->input('name')]);
            Response::setFlash('success', 'Rice variant created successfully.');
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                Response::setFlash('error', 'A variant with this name and unit already exists.');
            } else {
                Response::setFlash('error', 'Database error occurred.');
            }
        }

        return Response::redirect(url('/catalog'));
    }

    public function updateVariant(Request $request, string $id): Response
    {
        $v = Validator::make($request->all());
        if (!$v->validate([
            'name' => 'required',
            'minimum_stock' => 'required|numeric|min:0',
            'unit' => 'required'
        ])) {
            Response::setFlash('error', 'Validation failed.');
            return Response::redirect(url('/catalog'));
        }

        try {
            $allowWholesale = $request->input('allow_wholesale_discount') ? 1 : 0;
            RiceVariant::update(
                (int)$id,
                $request->input('name'),
                $request->input('description', ''),
                (float) $request->input('minimum_stock'),
                $request->input('unit'),
                $allowWholesale
            );
            
            $this->auditService->log($_SESSION['user_id'], 'UPDATE', 'variants', 'rice_variants', $id);
            Response::setFlash('success', 'Rice variant updated successfully.');
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                Response::setFlash('error', 'A variant with this name and unit already exists.');
            } else {
                Response::setFlash('error', 'Database error occurred.');
            }
        }

        return Response::redirect(url('/catalog'));
    }

    public function toggleVariant(Request $request, string $id): Response
    {
        try {
            $newStatus = RiceVariant::toggleStatus((int)$id);
            $this->auditService->log($_SESSION['user_id'], 'TOGGLE_STATUS', 'variants', 'rice_variants', $id, null, ['status' => $newStatus]);
            Response::setFlash('success', "Variant marked as {$newStatus}.");
        } catch (\Exception $e) {
            Response::setFlash('error', 'Failed to change status.');
        }

        return Response::redirect(url('/catalog'));
    }
    
    public function updatePrice(Request $request, string $variantId): Response
    {
        $v = Validator::make($request->all());
        if (!$v->validate([
            'new_price' => 'required|numeric|min:0',
            'reason' => 'required'
        ])) {
            Response::setFlash('error', 'Validation failed: numeric price and reason required.');
            return Response::redirect(url('/catalog'));
        }

        try {
            RicePrice::updatePrice(
                (int)$variantId, 
                (float)$request->input('new_price'), 
                $request->input('reason'), 
                $_SESSION['user_id']
            );
            
            $this->auditService->log($_SESSION['user_id'], 'UPDATE_PRICE', 'pricing', 'rice_prices', $variantId, null, ['new_price' => $request->input('new_price')]);
            Response::setFlash('success', 'Price updated successfully.');
        } catch (\Exception $e) {
            Response::setFlash('error', 'Failed to update price.');
        }

        return Response::redirect(url('/catalog'));
    }
}
