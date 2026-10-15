<?php 
$title = "Stock-In & Supplier Returns";
ob_start(); 
$activeTab = $activeTab ?? 'shipments';
$returns = $returns ?? [];
$stockIns = $stockIns ?? [];
$returnStats = $returnStats ?? ['total_returns_count' => 0, 'total_sacks_returned' => 0, 'total_returned_value' => 0];
?>

<div class="sm:flex sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-text-primary">Stock-In & Returns</h1>
        <p class="text-sm text-text-muted mt-1">Manage inbound shipments from suppliers and stock-out returns for excess or rejected sacks.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3 sm:flex-none">
        <a href="<?= url('/stock-ins/override') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="shield-alert" class="-ml-1 mr-2 h-5 w-5 text-error"></i>
            Override Restock
        </a>
        <a href="<?= url('/stock-ins/create') ?>" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="plus" class="-ml-1 mr-2 h-5 w-5"></i>
            Record Stock-In
        </a>
    </div>
</div>

<!-- Quick Metric Highlights -->
<div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
    <div class="bg-surface overflow-hidden rounded-lg border border-border p-5 shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-emerald-50 p-3 text-success">
                <i data-lucide="package-plus" class="h-6 w-6"></i>
            </div>
            <div class="ml-4 w-0 flex-1">
                <dl>
                    <dt class="truncate text-xs font-medium text-text-muted uppercase tracking-wider">Total Shipments (IN)</dt>
                    <dd class="text-xl font-bold text-text-primary mt-1"><?= count($stockIns) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="bg-surface overflow-hidden rounded-lg border border-border p-5 shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-rose-50 p-3 text-error">
                <i data-lucide="corner-up-left" class="h-6 w-6"></i>
            </div>
            <div class="ml-4 w-0 flex-1">
                <dl>
                    <dt class="truncate text-xs font-medium text-text-muted uppercase tracking-wider">Returned Sacks (OUT)</dt>
                    <dd class="text-xl font-bold text-error mt-1">
                        -<?= number_format($returnStats['total_sacks_returned'] ?? 0) ?> <span class="text-xs font-normal text-text-muted">sacks returned</span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="bg-surface overflow-hidden rounded-lg border border-border p-5 shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-blue-50 p-3 text-blue-600">
                <i data-lucide="rotate-ccw" class="h-6 w-6"></i>
            </div>
            <div class="ml-4 w-0 flex-1">
                <dl>
                    <dt class="truncate text-xs font-medium text-text-muted uppercase tracking-wider">Return Transactions</dt>
                    <dd class="text-xl font-bold text-text-primary mt-1"><?= number_format($returnStats['total_returns_count'] ?? 0) ?> <span class="text-xs font-normal text-text-muted">records</span></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="mt-8 border-b border-border">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <a href="<?= url('/stock-ins?tab=shipments') ?>" 
           class="<?= $activeTab === 'shipments' ? 'border-primary text-primary font-semibold' : 'border-transparent text-text-muted hover:text-text-primary hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 text-sm flex items-center gap-2">
            <i data-lucide="truck" class="h-4 w-4"></i>
            <span>Shipments History</span>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium <?= $activeTab === 'shipments' ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-text-muted' ?>">
                <?= count($stockIns) ?>
            </span>
        </a>
        <a href="<?= url('/stock-ins?tab=returns') ?>" 
           class="<?= $activeTab === 'returns' ? 'border-error text-error font-semibold' : 'border-transparent text-text-muted hover:text-text-primary hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 text-sm flex items-center gap-2">
            <i data-lucide="corner-up-left" class="h-4 w-4"></i>
            <span>Returned Section (Supplier Returns)</span>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium <?= $activeTab === 'returns' ? 'bg-rose-100 text-error' : 'bg-gray-100 text-text-muted' ?>">
                <?= count($returns) ?>
            </span>
        </a>
    </nav>
</div>

<!-- Tab Content -->
<?php if ($activeTab === 'shipments'): ?>
    <!-- SHIPMENTS TABLE -->
    <div class="mt-6 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <?php if (empty($stockIns)): ?>
                        <?php 
                            $title = "No stock-in records";
                            $message = "You haven't recorded any inventory deliveries yet.";
                            $icon = 'truck';
                            require __DIR__ . '/../partials/empty-state.php'; 
                        ?>
                    <?php else: ?>
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-background">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">ID</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Date</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Supplier</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Total Cost</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-text-primary">Returns</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border bg-surface">
                            <?php foreach($stockIns as $row): ?>
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                        STI-<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                        <?= date('M j, Y', strtotime($row['stock_in_date'] ?? $row['created_at'])) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                        <div class="font-medium"><?= e($row['supplier_name']) ?></div>
                                        <?php if(!empty($row['reference_number'])): ?>
                                            <div class="text-xs text-text-muted mt-0.5">Ref: <?= e($row['reference_number']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-text-primary">
                                        ₱<?= number_format($row['total_cost'], 2) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <?php if($row['status'] === 'active'): ?>
                                            <span class="inline-flex rounded-full bg-status-successBg px-2 text-xs font-semibold leading-5 text-success">Completed</span>
                                        <?php else: ?>
                                            <span class="inline-flex rounded-full bg-status-cancelBg px-2 text-xs font-semibold leading-5 text-error">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                        <?php if(!empty($row['total_returned_qty']) && $row['total_returned_qty'] > 0): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                                <i data-lucide="corner-up-left" class="w-3 h-3 text-rose-600"></i>
                                                -<?= number_format($row['total_returned_qty']) ?> sacks
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-text-muted font-normal">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="<?= url('/stock-ins/' . $row['id']) ?>" class="text-primary hover:text-primary-hover inline-flex items-center gap-1 justify-end font-medium">
                                            <i data-lucide="eye" class="h-4 w-4"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- RETURNED SECTION TABLE -->
    <div class="mt-6 flex flex-col">
        <div class="mb-4 rounded-md bg-amber-50 p-4 border border-amber-200">
            <div class="flex">
                <div class="flex-shrink-0 text-amber-500">
                    <i data-lucide="info" class="h-5 w-5"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800">Supplier Returns (Stock OUT)</h3>
                    <div class="mt-1 text-xs text-amber-700">
                        When received sacks are excess, damaged, or rejected, they are returned to the supplier. Each return transaction automatically stocks OUT the sacks, decreases active warehouse inventory, and logs an audited stock movement.
                    </div>
                </div>
            </div>
        </div>

        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <?php if (empty($returns)): ?>
                        <div class="text-center py-12 bg-surface">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-500">
                                <i data-lucide="rotate-ccw" class="h-6 w-6"></i>
                            </div>
                            <h3 class="mt-2 text-sm font-semibold text-text-primary">No returns recorded</h3>
                            <p class="mt-1 text-sm text-text-muted">No stock-in returns have been processed yet. To return excess sacks, open any active Stock-In record and click "Return Sacks".</p>
                        </div>
                    <?php else: ?>
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-background">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Return No</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Return Date</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Origin Stock-In</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Supplier</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Rice Variant</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Sacks Returned</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Reason</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Processed By</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border bg-surface">
                            <?php foreach($returns as $ret): ?>
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-semibold text-error sm:pl-6">
                                        <?= e($ret['return_no']) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                        <?= date('M j, Y', strtotime($ret['return_date'] ?? $ret['created_at'])) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                        <a href="<?= url('/stock-ins/' . $ret['stock_in_id']) ?>" class="font-medium text-primary hover:underline">
                                            STI-<?= str_pad($ret['stock_in_id'], 5, '0', STR_PAD_LEFT) ?>
                                        </a>
                                        <?php if(!empty($ret['stock_in_reference'])): ?>
                                            <div class="text-xs text-text-muted">Ref: <?= e($ret['stock_in_reference']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                        <?= e($ret['supplier_name']) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-text-primary">
                                        <?= e($ret['variant_name']) ?>
                                        <?php if(!empty($ret['variant_unit'])): ?>
                                            <span class="text-xs text-text-muted font-normal">(<?= e($ret['variant_unit']) ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold text-error">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            -<?= number_format($ret['quantity']) ?> sacks
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                        <div class="max-w-xs truncate" title="<?= e($ret['reason']) ?>">
                                            <?= e($ret['reason']) ?>
                                        </div>
                                        <?php if(!empty($ret['notes'])): ?>
                                            <div class="text-xs text-text-muted truncate max-w-xs" title="<?= e($ret['notes']) ?>">
                                                <?= e($ret['notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                        <?= e($ret['returned_by_name'] ?? 'System') ?>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="<?= url('/stock-ins/' . $ret['stock_in_id']) ?>" class="text-primary hover:text-primary-hover inline-flex items-center gap-1 justify-end">
                                            <i data-lucide="eye" class="h-4 w-4"></i> View Shipment
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
