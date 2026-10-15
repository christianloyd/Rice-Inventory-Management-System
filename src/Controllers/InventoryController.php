<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Models\RiceVariant;
use App\Models\StockMovement;

class InventoryController
{
    public function index(Request $request): Response
    {
        $search  = trim($_GET['search'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $pdo = \App\Support\Database::getInstance();

        if ($search !== '') {
            $like  = '%' . $search . '%';
            $stmt  = $pdo->prepare(
                "SELECT * FROM rice_variants
                 WHERE (name LIKE ? OR description LIKE ? OR unit LIKE ?)
                 ORDER BY name ASC"
            );
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $pdo->query(
                "SELECT * FROM rice_variants ORDER BY name ASC"
            );
        }

        $all      = $stmt->fetchAll();
        $total    = count($all);
        $pages    = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $pages);
        $variants = array_slice($all, ($page - 1) * $perPage, $perPage);

        return View::render('inventory/index', [
            'variants' => $variants,
            'search'   => $search,
            'page'     => $page,
            'pages'    => $pages,
            'total'    => $total,
        ]);
    }

    public function movements(Request $request, string $variantId = null): Response
    {
        $vId = $variantId ? (int)$variantId : null;
        $movements = StockMovement::getMovements($vId);
        
        $variant = null;
        if ($vId) {
            $pdo = \App\Support\Database::getInstance();
            $stmt = $pdo->prepare("SELECT name FROM rice_variants WHERE id = ?");
            $stmt->execute([$vId]);
            $variant = $stmt->fetchColumn();
        }

        return View::render('inventory/movements', [
            'movements' => $movements, 
            'variant' => $variant
        ]);
    }
}
