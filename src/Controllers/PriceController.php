<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Support\Validator;
use App\Models\RicePrice;
use App\Models\RicePriceHistory;
use App\Services\AuditService;

class PriceController
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function index(Request $request): Response
    {
        $variants = RicePrice::allActiveWithPrices();
        return View::render('prices/index', ['variants' => $variants]);
    }

    public function update(Request $request, string $variantId): Response
    {
        $v = Validator::make($request->all());
        if (!$v->validate([
            'new_price' => 'required|numeric|min:0',
            'reason' => 'required'
        ])) {
            Response::setFlash('error', 'Validation failed: numeric price and reason required.');
            return Response::redirect(url('/prices'));
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

        return Response::redirect(url('/prices'));
    }

    public function history(Request $request): Response
    {
        $limit = (int) $request->input('limit', '100');
        $history = RicePriceHistory::getHistory($limit);
        return View::render('prices/history', ['history' => $history]);
    }
}
