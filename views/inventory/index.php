<?php 
$title = "Inventory Overview";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Current stock levels for all registered rice variants.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/inventory/movements') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="activity" class="-ml-1 mr-2 h-5 w-5 text-text-muted"></i>
            Global Ledger
        </a>
    </div>
</div>

<!-- Search Bar -->
<form method="GET" action="<?= url('/inventory') ?>" class="mt-6">
    <div class="relative max-w-sm">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <i data-lucide="search" class="h-4 w-4 text-text-muted"></i>
        </div>
        <input
            type="text"
            name="search"
            id="inventory-search"
            value="<?= e($search) ?>"
            placeholder="Search by name, unit…"
            autocomplete="off"
            class="block w-full rounded-lg border border-border bg-surface py-2 pl-10 pr-10 text-sm text-text-primary placeholder-text-muted shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        <?php if ($search !== ''): ?>
        <a href="<?= url('/inventory') ?>"
           class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted hover:text-text-primary"
           title="Clear search">
            <i data-lucide="x" class="h-4 w-4"></i>
        </a>
        <?php endif; ?>
    </div>
</form>

<?php if ($search !== ''): ?>
<p class="mt-2 text-xs text-text-muted">
    <?= $total ?> result<?= $total !== 1 ? 's' : '' ?> for "<strong><?= e($search) ?></strong>"
</p>
<?php endif; ?>

<div class="mt-4 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($variants) && $search === ''): ?>
                    <?php 
                        $title = "Warehouse empty";
                        $message = "You have no variants registered to track inventory for.";
                        $icon = 'warehouse';
                        require __DIR__ . '/../partials/empty-state.php'; 
                    ?>
                <?php elseif (empty($variants)): ?>
                    <div class="flex flex-col items-center justify-center py-16 text-text-muted">
                        <i data-lucide="search-x" class="h-10 w-10 mb-3 opacity-40"></i>
                        <p class="text-sm font-medium">No variants match "<strong><?= e($search) ?></strong>"</p>
                        <a href="<?= url('/inventory') ?>" class="mt-2 text-xs text-primary hover:underline">Clear search</a>
                    </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Variant Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Unit</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Current Stock</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Min. Threshold</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($variants as $variant): ?>
                            <tr class="hover:bg-background/50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                    <?= e($variant['name']) ?>
                                    <?php if ($variant['description']): ?>
                                    <div class="text-xs font-normal text-text-muted"><?= e($variant['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <span class="inline-flex rounded bg-background px-2 py-0.5 text-xs font-semibold text-text-primary"><?= e($variant['unit']) ?></span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold <?= $variant['current_stock_cache'] <= $variant['minimum_stock'] ? 'text-error' : 'text-text-primary' ?>">
                                    <?= number_format($variant['current_stock_cache']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= number_format($variant['minimum_stock']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <?php if($variant['current_stock_cache'] <= $variant['minimum_stock']): ?>
                                        <span class="inline-flex rounded-full bg-status-errorBg px-2 py-0.5 text-xs font-medium leading-5 text-error">Low Stock</span>
                                    <?php elseif($variant['status'] === 'inactive'): ?>
                                        <span class="inline-flex rounded-full bg-status-cancelBg px-2 py-0.5 text-xs font-medium leading-5 text-text-muted">Inactive</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-status-successBg px-2 py-0.5 text-xs font-medium leading-5 text-success">Optimal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <a href="<?= url('/inventory/movements/' . $variant['id']) ?>" class="text-primary hover:text-primary-hover">Ledger</a>
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
        of <span class="font-medium text-text-primary"><?= $total ?></span> variants
    </p>

    <nav class="flex items-center gap-1" aria-label="Pagination">
        <?php
        // Build base query string preserving search
        $qs = $search !== '' ? '&search=' . urlencode($search) : '';
        $pageUrl = fn(int $p) => url('/inventory') . '?page=' . $p . $qs;
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

        <!-- Page numbers (show up to 7 around current) -->
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
<?php else: ?>
<!-- Single page — just show count -->
<p class="mt-3 text-xs text-text-muted"><?= $total ?> variant<?= $total !== 1 ? 's' : '' ?> total</p>
<?php endif; ?>

<script>
// Auto-submit search form on Enter (it's already a form, but also support live search on Ctrl+Enter or debounce)
(function () {
    const input = document.getElementById('inventory-search');
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
