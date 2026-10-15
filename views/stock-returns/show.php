<?php 
$title = "Return Transaction " . e($return['return_no']);
ob_start(); 
$totalVal = (float)$return['quantity'] * (float)($return['cost_per_unit'] ?? 0);
?>

<div class="sm:flex sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-text-primary"><?= e($return['return_no']) ?></h1>
            <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 border border-rose-200">
                <i data-lucide="corner-up-left" class="w-3.5 h-3.5 text-error"></i>
                Stock OUT Reversal
            </span>
        </div>
        <p class="text-sm text-text-muted mt-1">Supplier return transaction details and inventory audit trail.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center space-x-3">
        <a href="<?= url('/stock-returns/' . $return['id'] . '/print') ?>" target="_blank" class="inline-flex items-center justify-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-surface focus:outline-none">
            <i data-lucide="printer" class="-ml-1 mr-2 h-4 w-4 text-text-muted"></i>
            Print Return Slip
        </a>
        <a href="<?= url('/stock-ins/' . $return['stock_in_id']) ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none">
            <i data-lucide="external-link" class="-ml-1 mr-2 h-4 w-4 text-text-muted"></i>
            View Origin Shipment
        </a>
        <a href="<?= url('/stock-returns') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none">
            <i data-lucide="arrow-left" class="-ml-1 mr-2 h-4 w-4 text-text-muted"></i>
            Back
        </a>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Left Column: Transaction Card -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Main Return Details -->
        <div class="bg-surface shadow overflow-hidden sm:rounded-lg border border-border">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center bg-background/50 border-b border-border">
                <div>
                    <h3 class="text-lg leading-6 font-semibold text-text-primary">
                        Returned Item: <?= e($return['variant_name']) ?>
                    </h3>
                    <p class="mt-1 text-sm text-text-muted">
                        Returned to <span class="font-medium text-text-primary"><?= e($return['supplier_name']) ?></span> on <?= date('M j, Y', strtotime($return['return_date'] ?? $return['created_at'])) ?>
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-extrabold bg-rose-50 text-error border border-rose-200">
                        <i data-lucide="corner-up-left" class="w-4 h-4"></i>
                        -<?= number_format($return['quantity']) ?> sacks
                    </span>
                </div>
            </div>
            <div class="px-4 py-5 sm:px-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Unit Packaging</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                <?= e($return['variant_unit'] ?? 'sack') ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Unit Cost Credited</dt>
                        <dd class="mt-1 text-sm font-medium text-text-primary">₱<?= number_format($return['cost_per_unit'], 2) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Total Return Value</dt>
                        <dd class="mt-1 text-base font-bold text-text-primary">₱<?= number_format($totalVal, 2) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Current Warehouse Stock</dt>
                        <dd class="mt-1 text-sm font-medium text-text-primary"><?= number_format($return['current_stock_cache'] ?? 0) ?> sacks</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Return Reason</dt>
                        <dd class="mt-1 text-sm font-medium text-text-primary bg-background p-3 rounded border border-border">
                            <?= e($return['reason']) ?>
                        </dd>
                    </div>
                    <?php if(!empty($return['notes'])): ?>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Additional Remarks</dt>
                            <dd class="mt-1 text-sm text-text-primary bg-background p-3 rounded border border-border">
                                <?= nl2br(e($return['notes'])) ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Inventory Audit Card -->
        <div class="bg-surface shadow overflow-hidden sm:rounded-lg border border-border p-6">
            <h4 class="text-sm font-semibold text-text-primary flex items-center gap-2 mb-3">
                <i data-lucide="history" class="w-4 h-4 text-primary"></i>
                Inventory Audit Movement
            </h4>
            <div class="rounded-md bg-background p-4 border border-border text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-text-muted">Movement Type:</span>
                    <span class="font-bold text-error">STOCK_IN_REVERSAL (Stock OUT)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Quantity Deducted:</span>
                    <span class="font-bold text-error">-<?= number_format($return['quantity']) ?> sacks</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Reference Type:</span>
                    <span class="font-medium text-text-primary">STOCK_IN_RETURN #<?= $return['id'] ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Processed By:</span>
                    <span class="font-medium text-text-primary"><?= e($return['returned_by_name'] ?? 'System') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">System Timestamp:</span>
                    <span class="font-medium text-text-primary"><?= date('M j, Y h:i A', strtotime($return['created_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Origin Shipment Details -->
    <div class="space-y-6">
        <div class="bg-surface shadow overflow-hidden sm:rounded-lg border border-border p-6">
            <h3 class="text-sm font-semibold text-text-primary uppercase tracking-wider mb-4 flex items-center gap-2">
                <i data-lucide="truck" class="w-4 h-4 text-primary"></i>
                Origin Shipment Record
            </h3>
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs text-text-muted">Stock-In ID</dt>
                    <dd class="mt-0.5">
                        <a href="<?= url('/stock-ins/' . $return['stock_in_id']) ?>" class="font-semibold text-primary hover:underline">
                            STI-<?= str_pad($return['stock_in_id'], 5, '0', STR_PAD_LEFT) ?>
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-text-muted">Supplier Name</dt>
                    <dd class="mt-0.5 font-medium text-text-primary"><?= e($return['supplier_name']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-text-muted">Shipment Reference</dt>
                    <dd class="mt-0.5 font-medium text-text-primary">
                        <?= !empty($return['stock_in_reference']) ? e($return['stock_in_reference']) : '<span class="italic text-text-muted">None provided</span>' ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-text-muted">Shipment Date</dt>
                    <dd class="mt-0.5 font-medium text-text-primary">
                        <?= !empty($return['stock_in_date']) ? date('M j, Y', strtotime($return['stock_in_date'])) : '—' ?>
                    </dd>
                </div>
            </dl>
            <div class="mt-6 pt-4 border-t border-border">
                <a href="<?= url('/stock-ins/' . $return['stock_in_id']) ?>" class="block w-full text-center rounded-md border border-border bg-background py-2 text-xs font-semibold text-text-primary hover:bg-surface">
                    Open Inbound Shipment Details
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
