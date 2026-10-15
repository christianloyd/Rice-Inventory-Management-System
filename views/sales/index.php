<?php 
$title = "Sales Records";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Browse historical sales transactions and issue refunds.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex items-center space-x-3">
        <a href="<?= url('/sales/exchange') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="refresh-cw" class="-ml-1 mr-2 h-5 w-5 text-blue-500"></i>
            Exchange Item
        </a>
        <a href="<?= url('/sales/create') ?>" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="shopping-cart" class="-ml-1 mr-2 h-5 w-5"></i>
            New Sale (POS)
        </a>
    </div>
</div>

<?php
// Build a clean query-string for pagination links
$_qsBase = [];
if ($search !== '')        $_qsBase['search']    = $search;
if ($status !== 'all')     $_qsBase['status']    = $status;
if ($payFilter !== 'all')  $_qsBase['pay']       = $payFilter;
if ($dateFrom !== '')      $_qsBase['date_from'] = $dateFrom;
if ($dateTo   !== '')      $_qsBase['date_to']   = $dateTo;
$_qsStr = $_qsBase ? '&' . http_build_query($_qsBase) : '';
?>

<!-- Search & Filter Bar -->
<form method="GET" action="<?= url('/sales') ?>" id="sales-filter-form" class="mt-6 space-y-3">

    <!-- Row 1: Search + Status + Date Preset -->
    <div class="flex flex-wrap items-center gap-3">
        <!-- Search -->
        <div class="relative flex-1 min-w-[180px] max-w-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i data-lucide="search" class="h-4 w-4 text-text-muted"></i>
            </div>
            <input
                type="text"
                name="search"
                id="sales-search"
                value="<?= e($search) ?>"
                placeholder="Search invoice or customer…"
                autocomplete="off"
                class="block w-full rounded-lg border border-border bg-surface py-2 pl-10 pr-10 text-sm text-text-primary placeholder-text-muted shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            <?php if ($search !== ''): ?>
            <a href="<?= url('/sales') ?>?status=<?= e($status) ?>&pay=<?= e($payFilter) ?>"
               class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted hover:text-text-primary" title="Clear search">
                <i data-lucide="x" class="h-4 w-4"></i>
            </a>
            <?php endif; ?>
        </div>

        <!-- Transaction Status -->
        <select name="status" id="sales-status" onchange="this.form.submit()"
                class="rounded-lg border border-border bg-surface py-2 pl-3 pr-8 text-sm text-text-primary shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="all"       <?= $status === 'all'       ? 'selected' : '' ?>>All Statuses</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            <option value="exchanged" <?= $status === 'exchanged' ? 'selected' : '' ?>>Exchanged</option>
        </select>

        <!-- Today shortcut -->
        <a href="<?= url('/sales') ?>?preset=today&status=<?= e($status) ?>&pay=<?= e($payFilter) ?>"
           class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium transition-colors
                  <?= $datePreset === 'today' ? 'bg-primary border-primary text-white' : 'border-border bg-surface text-text-muted hover:bg-background' ?>">
            <i data-lucide="calendar-check" class="h-4 w-4"></i> Today
        </a>

        <!-- Clear all filters -->
        <?php $hasFilters = ($search !== '' || $status !== 'all' || $payFilter !== 'all' || $dateFrom !== '' || $dateTo !== '' || $datePreset !== ''); ?>
        <?php if ($hasFilters): ?>
        <a href="<?= url('/sales') ?>" class="text-xs text-primary hover:underline ml-auto">Clear all filters</a>
        <?php endif; ?>
    </div>

    <!-- Row 2: Date Range -->
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 text-sm text-text-muted">
            <i data-lucide="calendar" class="h-4 w-4"></i>
            <span>From</span>
            <input type="date" name="date_from" id="date_from"
                   value="<?= e($dateFrom) ?>"
                   class="rounded-lg border border-border bg-surface py-1.5 px-3 text-sm text-text-primary shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex items-center gap-2 text-sm text-text-muted">
            <span>To</span>
            <input type="date" name="date_to" id="date_to"
                   value="<?= e($dateTo) ?>"
                   class="rounded-lg border border-border bg-surface py-1.5 px-3 text-sm text-text-primary shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-border bg-surface px-3 py-1.5 text-sm text-text-primary hover:bg-background transition-colors">
            <i data-lucide="filter" class="h-4 w-4"></i> Apply
        </button>
    </div>

    <!-- Row 3: Payment type pills -->
    <div class="flex flex-wrap items-center gap-1">
        <?php
        $payOptions = [
            'all'    => ['label' => 'All Types', 'icon' => 'layers'],
            'cash'   => ['label' => 'Cash',      'icon' => 'banknote'],
            'cheque' => ['label' => 'Cheque',    'icon' => 'file-check'],
            'utang'  => ['label' => 'Utang',     'icon' => 'credit-card'],
            'tbt'    => ['label' => 'TBT',       'icon' => 'clock'],
        ];
        foreach ($payOptions as $val => $opt):
            $isActive = $payFilter === $val;
            $qs = '?pay=' . $val;
            if ($search  !== '') $qs .= '&search='    . urlencode($search);
            if ($status  !== 'all') $qs .= '&status=' . urlencode($status);
            if ($dateFrom !== '')   $qs .= '&date_from=' . urlencode($dateFrom);
            if ($dateTo  !== '')    $qs .= '&date_to='   . urlencode($dateTo);
        ?>
        <a href="<?= url('/sales') . $qs ?>"
           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition-colors
                  <?= $isActive ? 'bg-primary text-white shadow-sm' : 'bg-surface border border-border text-text-muted hover:bg-background' ?>">
            <i data-lucide="<?= $opt['icon'] ?>" class="h-3.5 w-3.5"></i>
            <?= $opt['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

</form>

<?php if ($hasFilters || $search !== '' || $status !== 'all'): ?>
<p class="mt-2 text-xs text-text-muted">
    <?= $total ?> result<?= $total !== 1 ? 's' : '' ?> found.
</p>
<?php endif; ?>


<div class="mt-4 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($sales) && !$hasFilters): ?>
                    <?php 
                        $title = "No sales records";
                        $message = "You haven't processed any sales yet.";
                        $icon = 'banknote';
                        require __DIR__ . '/../partials/empty-state.php'; 
                    ?>
                <?php elseif (empty($sales)): ?>
                    <div class="flex flex-col items-center justify-center py-16 text-text-muted">
                        <i data-lucide="search-x" class="h-10 w-10 mb-3 opacity-40"></i>
                        <p class="text-sm font-medium">No sales match your filters.</p>
                        <a href="<?= url('/sales') ?>" class="mt-2 text-xs text-primary hover:underline">Clear all filters</a>
                    </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Invoice</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Date / Cashier</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Customer</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Total Amount</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($sales as $row): ?>
                            <tr class="hover:bg-background/50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono font-medium text-text-primary sm:pl-6">
                                    <?= e($row['transaction_no']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= e($row['created_at']) ?>
                                    <div class="text-xs text-text-muted mt-1">by <?= e($row['cashier_name'] ?? 'System') ?></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <?= e($row['customer_name'] ?: 'Walk-in') ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-text-primary">
                                    ₱<?= number_format($row['total_amount'], 2) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <?php if($row['status'] === 'completed'): ?>
                                        <span class="inline-flex rounded-full bg-status-successBg px-2 py-0.5 text-xs font-semibold leading-5 text-success">Completed</span>
                                    <?php elseif($row['status'] === 'exchanged'): ?>
                                        <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold leading-5 text-blue-800">Exchanged</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-status-cancelBg px-2 py-0.5 text-xs font-semibold leading-5 text-error">Cancelled</span>
                                    <?php endif; ?>
                                    <?php if (($row['payment_method'] ?? '') === 'cheque'): ?>
                                        <span class="ml-1 inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold leading-5 text-purple-700">Cheque</span>
                                    <?php endif; ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <a href="<?= url('/sales/' . $row['id']) ?>" class="group inline-flex items-center text-primary hover:text-primary-hover">
                                        <i data-lucide="eye" class="mr-1.5 h-4 w-4 text-primary group-hover:text-primary-hover"></i>
                                        View Receipt
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

<?php if ($pages > 1): ?>
<!-- Pagination Bar -->
<div class="mt-4 flex items-center justify-between">
    <p class="text-sm text-text-muted">
        Showing
        <span class="font-medium text-text-primary"><?= (($page - 1) * 10) + 1 ?></span>–<span class="font-medium text-text-primary"><?= min($page * 10, $total) ?></span>
        of <span class="font-medium text-text-primary"><?= $total ?></span> records
    </p>

    <nav class="flex items-center gap-1" aria-label="Pagination">
        <?php
        // Build base query string preserving all filters
        $qs = '';
        if ($search !== '')       $qs .= '&search='    . urlencode($search);
        if ($status !== 'all')    $qs .= '&status='    . urlencode($status);
        if ($payFilter !== 'all') $qs .= '&pay='       . urlencode($payFilter);
        if ($dateFrom !== '')     $qs .= '&date_from=' . urlencode($dateFrom);
        if ($dateTo !== '')       $qs .= '&date_to='   . urlencode($dateTo);
        $pageUrl = fn(int $p) => url('/sales') . '?page=' . $p . $qs;
        ?>

        <!-- Prev -->
        <?php if ($page > 1): ?>
        <a href="<?= $pageUrl($page - 1) ?>"
           class="inline-flex items-center gap-1 rounded-md border border-border bg-surface px-3 py-1.5 text-sm font-medium text-text-primary hover:bg-background transition-colors">
            <i data-lucide="chevron-left" class="h-4 w-4"></i> Prev
        </a>
        <?php else: ?>
        <span class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium text-text-muted opacity-50 cursor-not-allowed">
            <i data-lucide="chevron-left" class="h-4 w-4"></i> Prev
        </span>
        <?php endif; ?>

        <!-- Page numbers -->
        <?php
        $start = max(1, $page - 3);
        $end   = min($pages, $page + 3);
        if ($start > 1): ?>
            <a href="<?= $pageUrl(1) ?>" class="inline-flex items-center rounded-md border border-border bg-surface px-3 py-1.5 text-sm font-medium text-text-primary hover:bg-background transition-colors">1</a>
            <?php if ($start > 2): ?><span class="px-1 text-text-muted text-sm">…</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($p = $start; $p <= $end; $p++): ?>
            <?php if ($p === $page): ?>
            <span class="inline-flex items-center rounded-md border border-primary bg-primary px-3 py-1.5 text-sm font-semibold text-white"><?= $p ?></span>
            <?php else: ?>
            <a href="<?= $pageUrl($p) ?>" class="inline-flex items-center rounded-md border border-border bg-surface px-3 py-1.5 text-sm font-medium text-text-primary hover:bg-background transition-colors"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end < $pages): ?>
            <?php if ($end < $pages - 1): ?><span class="px-1 text-text-muted text-sm">…</span><?php endif; ?>
            <a href="<?= $pageUrl($pages) ?>" class="inline-flex items-center rounded-md border border-border bg-surface px-3 py-1.5 text-sm font-medium text-text-primary hover:bg-background transition-colors"><?= $pages ?></a>
        <?php endif; ?>

        <!-- Next -->
        <?php if ($page < $pages): ?>
        <a href="<?= $pageUrl($page + 1) ?>"
           class="inline-flex items-center gap-1 rounded-md border border-border bg-surface px-3 py-1.5 text-sm font-medium text-text-primary hover:bg-background transition-colors">
            Next <i data-lucide="chevron-right" class="h-4 w-4"></i>
        </a>
        <?php else: ?>
        <span class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium text-text-muted opacity-50 cursor-not-allowed">
            Next <i data-lucide="chevron-right" class="h-4 w-4"></i>
        </span>
        <?php endif; ?>
    </nav>
</div>
<?php elseif (empty($sales)): ?>
<!-- Empty space since there's already an empty state shown -->
<?php else: ?>
<!-- Single page — just show count -->
<p class="mt-3 text-xs text-text-muted"><?= $total ?> record<?= $total !== 1 ? 's' : '' ?> total</p>
<?php endif; ?>

<script>
// Auto-submit search form on typed input (with debounce)
(function () {
    const input = document.getElementById('sales-search');
    if (!input) return;
    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => input.closest('form').submit(), 400);
    });
})();
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
