<?php
namespace App\Controllers;

use App\Services\TbtService;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use Exception;

class TbtController
{
    private TbtService $tbtService;

    public function __construct()
    {
        $this->tbtService = new TbtService();
    }

    public function index(Request $request): Response
    {
        $filters = [
            'status' => $_GET['status'] ?? 'pending_pickup',
            'search' => $_GET['search'] ?? null
        ];

        $records = $this->tbtService->listRecords($filters);
        
        return View::render('tbt/index', [
            'filters' => $filters,
            'records' => $records
        ]);
    }

    public function release(Request $request, string $id): Response
    {
        // Safely flush ALL output buffer levels without emitting notices when none exist.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::json(['success' => false, 'error' => 'Invalid request method.']);
        }

        try {
            // CSRF already validated by CsrfMiddleware in the router before this controller runs.
            // Optional quantity for partial releases; 0 = release all remaining
            $quantityToRelease = (float)($request->input('quantity', 0));
            $result = $this->tbtService->release((int)$id, $_SESSION['user_id'], $quantityToRelease);
            return Response::json([
                'success'            => true,
                'released_at'        => $result['released_at'],
                'quantity_released'  => $result['quantity_released'],
                'quantity_remaining' => $result['quantity_remaining'],
                'status'             => $result['status'],
            ]);
        } catch (Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
