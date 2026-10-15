<?php 
$title = "Dashboard";
ob_start(); 
?>

<!-- Metrics Grid -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    
    <!-- Today -->
    <div class="bg-surface rounded-2xl border border-border p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-semibold text-text-muted uppercase tracking-widest">Today</h3>
            <i data-lucide="banknote" class="h-4 w-4 text-text-muted"></i>
        </div>
        <div class="text-3xl font-semibold text-text-primary">₱<?= number_format($todaySales, 2) ?></div>
        <p class="mt-1 text-sm text-text-muted"><?= number_format($todayQuantity) ?> units sold</p>
    </div>

    <!-- Transactions -->
    <div class="bg-surface rounded-2xl border border-border p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-semibold text-text-muted uppercase tracking-widest">Transactions</h3>
            <i data-lucide="receipt" class="h-4 w-4 text-text-muted"></i>
        </div>
        <div class="text-3xl font-semibold text-text-primary"><?= number_format($todayTransactions) ?></div>
        <p class="mt-1 text-sm text-text-muted">completed today</p>
    </div>

    <!-- This Month -->
    <div class="bg-surface rounded-2xl border border-border p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-semibold text-text-muted uppercase tracking-widest">This Month</h3>
            <i data-lucide="calendar" class="h-4 w-4 text-text-muted"></i>
        </div>
        <div class="text-3xl font-semibold text-text-primary">₱<?= number_format($monthSales, 2) ?></div>
        <p class="mt-1 text-sm text-text-muted"><?= number_format($monthQuantity) ?> sales items</p>
    </div>

    <!-- Stock Alerts -->
    <div class="bg-yellow-50/50 rounded-2xl border border-yellow-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-semibold text-yellow-800 uppercase tracking-widest">Stock Alerts</h3>
            <i data-lucide="alert-triangle" class="h-4 w-4 text-yellow-700"></i>
        </div>
        <div class="text-3xl font-semibold text-yellow-900"><?= number_format($lowStock) ?></div>
        <p class="mt-1 text-sm text-yellow-700">low or out of stock</p>
    </div>
</div>

<!-- Detailed Panels -->
<div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
    
    <!-- Recent Sales -->
    <div class="bg-surface rounded-2xl border border-border flex flex-col">
        <div class="px-5 py-4 border-b border-border flex justify-between items-center bg-surface rounded-t-2xl">
            <h2 class="text-lg font-medium text-text-primary">Recent sales</h2>
            <a href="<?= url('/sales') ?>" class="inline-flex items-center text-sm font-medium text-primary hover:text-primary-hover transition-colors">
                View all <i data-lucide="arrow-right" class="h-4 w-4 ml-1"></i>
            </a>
        </div>
        <div class="p-5 flex-1 flex flex-col">
            <?php if(empty($recentSales)): ?>
                <div class="flex-1 flex items-center justify-center py-6 text-sm text-text-muted">
                    No sales recorded yet.
                </div>
            <?php else: ?>
                <ul class="-my-5 divide-y divide-border">
                    <?php foreach($recentSales as $sale): ?>
                    <li class="py-4 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-text-primary">Order #<?= e(str_pad($sale['id'], 5, '0', STR_PAD_LEFT)) ?></p>
                            <p class="text-xs text-text-muted"><?= date('M j, Y g:i A', strtotime($sale['sale_date'])) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-text-primary">₱<?= number_format($sale['total_amount'], 2) ?></p>
                            <p class="text-xs text-text-muted"><?= number_format($sale['total_quantity']) ?> items</p>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stock Levels -->
    <div class="bg-surface rounded-2xl border border-border flex flex-col">
        <div class="px-5 py-4 border-b border-border flex justify-between items-center bg-surface rounded-t-2xl">
            <h2 class="text-lg font-medium text-text-primary">Stock levels</h2>
            <a href="<?= url('/inventory') ?>" class="inline-flex items-center text-sm font-medium text-primary hover:text-primary-hover transition-colors">
                View all <i data-lucide="arrow-right" class="h-4 w-4 ml-1"></i>
            </a>
        </div>
        <div class="p-5 flex-1">
            <?php if(empty($lowStockItems)): ?>
                <div class="flex items-center justify-center h-full py-6 text-sm text-success font-medium">
                    All items are well stocked!
                </div>
            <?php else: ?>
                <ul class="-my-5 divide-y divide-border">
                    <?php foreach($lowStockItems as $item): ?>
                    <li class="py-4 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-text-primary"><?= e($item['name']) ?></p>
                            <p class="text-xs text-text-muted mt-0.5"><?= number_format($item['current_stock_cache'] ?? 0) ?> <?= e($item['unit'] ?? 'unit') ?> &bull; min <?= number_format($item['minimum_stock'] ?? 0) ?></p>
                        </div>
                        <div>
                            <?php if($item['current_stock_cache'] <= 0): ?>
                                <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">Out of stock</span>
                            <?php else: ?>
                                <span class="inline-flex items-center rounded-full border border-yellow-200 bg-yellow-50 px-2.5 py-0.5 text-xs font-semibold text-yellow-800">Low stock</span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/app.php'; 
?>
