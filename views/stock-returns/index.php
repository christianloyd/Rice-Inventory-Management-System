<?php 
$title = "Stock Returns";
ob_start(); 
$returns = $returns ?? [];
$stats = $stats ?? ['total_returns_count' => 0, 'total_sacks_returned' => 0, 'total_returned_value' => 0];
$search = $search ?? '';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
?>

<div class="sm:flex sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-text-primary">Stock Returns</h1>
        <p class="text-sm text-text-muted mt-1">Audit and record supplier returns for excess, damaged, or rejected sacks (Stock OUT).</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3 sm:flex-none">
        <a href="<?= url('/stock-ins') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
            <i data-lucide="package-plus" class="-ml-1 mr-2 h-4 w-4 text-text-muted"></i>
            Stock-In Shipments
        </a>
        <a href="<?= url('/stock-returns/create') ?>" class="inline-flex items-center justify-center rounded-md border border-transparent bg-error px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2">
            <i data-lucide="corner-up-left" class="-ml-1 mr-2 h-4 w-4"></i>
            Return Rice Stock
        </a>
    </div>
</div>

<!-- Highlight Stat Cards -->
<div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
    <div class="bg-surface overflow-hidden rounded-lg border border-border p-5 shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-rose-50 p-3 text-error">
                <i data-lucide="corner-up-left" class="h-6 w-6"></i>
            </div>
            <div class="ml-4 w-0 flex-1">
                <dl>
                    <dt class="truncate text-xs font-medium text-text-muted uppercase tracking-wider">Total Sacks Returned (OUT)</dt>
                    <dd class="text-xl font-bold text-error mt-1">
                        -<?= number_format($stats['total_sacks_returned'] ?? 0) ?> <span class="text-xs font-normal text-text-muted">sacks</span>
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
                    <dd class="text-xl font-bold text-text-primary mt-1">
                        <?= number_format($stats['total_returns_count'] ?? 0) ?> <span class="text-xs font-normal text-text-muted">records</span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="bg-surface overflow-hidden rounded-lg border border-border p-5 shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-amber-50 p-3 text-amber-600">
                <i data-lucide="receipt" class="h-6 w-6"></i>
            </div>
            <div class="ml-4 w-0 flex-1">
                <dl>
                    <dt class="truncate text-xs font-medium text-text-muted uppercase tracking-wider">Total Value Returned</dt>
                    <dd class="text-xl font-bold text-text-primary mt-1">
                        ₱<?= number_format($stats['total_returned_value'] ?? 0, 2) ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="mt-6 bg-surface p-4 rounded-lg border border-border shadow-sm">
    <form method="GET" action="<?= url('/stock-returns') ?>" class="grid grid-cols-1 gap-4 sm:grid-cols-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Search</label>
            <div class="relative rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i data-lucide="search" class="h-4 w-4 text-text-muted"></i>
                </div>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Return No, Supplier, Variant..."
                       class="block w-full rounded-md border border-border bg-background pl-9 pr-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Date From</label>
            <input type="date" name="date_from" value="<?= e($dateFrom) ?>"
                   class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Date To</label>
            <input type="date" name="date_to" value="<?= e($dateTo) ?>"
                   class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none">
                <i data-lucide="filter" class="w-4 h-4 mr-1.5"></i> Filter
            </button>
            <?php if($search !== '' || $dateFrom !== '' || $dateTo !== ''): ?>
                <a href="<?= url('/stock-returns') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-3 py-2 text-sm font-medium text-text-muted hover:text-text-primary">
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="mt-6 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($returns)): ?>
                    <div class="text-center py-12 bg-surface">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-error">
                            <i data-lucide="corner-up-left" class="h-6 w-6"></i>
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-text-primary">No return transactions found</h3>
                        <p class="mt-1 text-sm text-text-muted">
                            <?= ($search !== '' || $dateFrom !== '' || $dateTo !== '') ? 'No return transactions match your filters.' : 'No stock return records have been created yet.' ?>
                        </p>
                        <div class="mt-6">
                            <a href="<?= url('/stock-returns/create') ?>" class="inline-flex items-center rounded-md bg-error px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                                <i data-lucide="corner-up-left" class="-ml-1 mr-2 h-4 w-4"></i>
                                Process New Return
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Return No</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Origin Shipment</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Supplier</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Rice Variant</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Sacks Returned</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Return Value</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Reason</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-semibold text-text-primary">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($returns as $r): 
                            $lineValue = (float)$r['quantity'] * (float)($r['cost_per_unit'] ?? 0);
                        ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-semibold text-error sm:pl-6">
                                    <a href="<?= url('/stock-returns/' . $r['id']) ?>" class="hover:underline">
                                        <?= e($r['return_no']) ?>
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= date('M j, Y', strtotime($r['return_date'] ?? $r['created_at'])) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <a href="<?= url('/stock-ins/' . $r['stock_in_id']) ?>" class="font-medium text-primary hover:underline">
                                        STI-<?= str_pad($r['stock_in_id'], 5, '0', STR_PAD_LEFT) ?>
                                    </a>
                                    <?php if(!empty($r['stock_in_reference'])): ?>
                                        <div class="text-xs text-text-muted">Ref: <?= e($r['stock_in_reference']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <?= e($r['supplier_name']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-text-primary">
                                    <?= e($r['variant_name']) ?>
                                    <?php if(!empty($r['variant_unit'])): ?>
                                        <span class="text-xs text-text-muted font-normal">(<?= e($r['variant_unit']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold text-error">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        -<?= number_format($r['quantity']) ?> sacks
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-medium text-text-primary">
                                    ₱<?= number_format($lineValue, 2) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <div class="max-w-xs truncate" title="<?= e($r['reason']) ?>">
                                        <?= e($r['reason']) ?>
                                    </div>
                                    <?php if(!empty($r['notes'])): ?>
                                        <div class="text-xs text-text-muted truncate max-w-xs" title="<?= e($r['notes']) ?>">
                                            <?= e($r['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('/stock-returns/' . $r['id']) ?>" class="text-primary hover:text-primary-hover inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded bg-primary/10">
                                            <i data-lucide="eye" class="h-3.5 w-3.5"></i> Details
                                        </a>
                                        <a href="<?= url('/stock-returns/' . $r['id'] . '/print') ?>" target="_blank" class="text-text-muted hover:text-text-primary inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded border border-border">
                                            <i data-lucide="printer" class="h-3.5 w-3.5"></i> Print
                                        </a>
                                    </div>
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

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
