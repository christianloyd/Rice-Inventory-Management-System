<?php
$title = 'Deliveries';
ob_start();
?>
<div class="sm:flex sm:items-center justify-between mb-6">
    <div>
        <p class="text-sm text-text-muted mt-1">Deliveries to customers, recorded as cash or utang (credit).</p>
    </div>
    <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
    <a href="<?= url('/deliveries/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition-colors shadow">
        <i data-lucide="plus" class="h-4 w-4"></i> New Delivery
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between mb-5">
    <form method="GET" action="<?= url('/deliveries') ?>" class="relative">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-text-muted pointer-events-none"></i>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search receipt no. or customer"
               class="pl-9 pr-4 py-2 text-sm border border-border rounded-lg bg-surface focus:outline-none focus:ring-2 focus:ring-primary w-64">
        <?php if (!empty($filter) && $filter !== 'all'): ?>
        <input type="hidden" name="type" value="<?= e($filter) ?>">
        <?php endif; ?>
    </form>
    <div class="flex gap-1">
        <?php foreach (['all' => 'All', 'cash' => 'Cash', 'utang' => 'Utang'] as $val => $label): ?>
        <a href="<?= url('/deliveries?type=' . $val . ($search ? '&search=' . urlencode($search) : '')) ?>"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                  <?= $filter === $val ? 'bg-primary text-white shadow-sm' : 'bg-surface border border-border text-text-muted hover:bg-background' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="mt-6 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Receipt / Customer</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Variant(s)</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Total Amount</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Type / Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php if (empty($deliveries)): ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <i data-lucide="truck" class="h-10 w-10 text-text-muted mx-auto mb-3"></i>
                                    <p class="text-text-muted text-sm">No deliveries found.</p>
                                    <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
                                    <a href="<?= url('/deliveries/create') ?>" class="mt-3 inline-flex items-center gap-1 text-primary text-sm font-medium hover:underline">
                                        <i data-lucide="plus" class="h-4 w-4"></i> Record your first delivery
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deliveries as $d): ?>
                            <?php
                                $isCancelled = $d['sale_status'] === 'cancelled';
                                $isUtang = $d['payment_type'] === 'utang';
                                $isOwner = $d['payment_type'] === 'owner';
                                $isPaid  = $d['pay_status']   === 'paid';
                                
                                if ($isCancelled) {
                                    $badgeClass = 'bg-gray-100 text-gray-700';
                                    $badgeText  = 'Voided';
                                } else {
                                    if ($isUtang) {
                                        $badgeClass = $isPaid ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                                        $badgeText = $isPaid ? 'Utang · Paid' : 'Utang · Outstanding';
                                    } elseif ($isOwner) {
                                        $badgeClass = 'bg-amber-100 text-amber-700';
                                        $badgeText = 'Owner Transfer';
                                    } else {
                                        $badgeClass = 'bg-blue-100 text-blue-700';
                                        $badgeText = 'Cash';
                                    }
                                }
                            ?>
                            <tr class="<?= $isCancelled ? 'opacity-60 bg-gray-50' : 'hover:bg-gray-50 transition-colors' ?>">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6 <?= $isCancelled ? 'line-through text-gray-500' : '' ?>">
                                    <div class="font-medium text-text-primary"><?= e($d['receipt_no']) ?></div>
                                    <div class="text-text-muted text-xs"><?= e($d['customer_name']) ?></div>
                                </td>
                                <td class="px-3 py-4 text-sm text-text-primary <?= $isCancelled ? 'line-through text-gray-500' : '' ?>">
                                    <?= date('M j, Y', strtotime($d['delivery_date'])) ?> · <?= e($d['items_summary'] ?? '–') ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold <?= $isCancelled ? 'text-gray-400 line-through' : 'text-text-primary' ?>">
                                    <?= $isOwner ? '-' : '₱' . number_format($d['total_amount'], 2) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $badgeClass ?>"><?= $badgeText ?></span>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <a href="<?= url('/deliveries/' . $d['id']) ?>" title="View details"
                                       class="text-primary hover:text-primary-hover transition-colors inline-block p-1 mr-1">
                                        <i data-lucide="eye" class="h-5 w-5"></i>
                                    </a>
                                    <?php if (!$isCancelled): ?>
                                    <a href="<?= url('/deliveries/' . $d['id'] . '/receipt') ?>" target="_blank" title="Print receipt"
                                       class="text-text-muted hover:text-primary transition-colors inline-block p-1">
                                        <i data-lucide="printer" class="h-5 w-5"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pages) && $pages > 1): ?>
            <div class="mt-4 flex items-center justify-between border-t border-border bg-surface px-4 py-3 sm:px-6 rounded-lg shadow-sm">
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-text-muted">
                            Showing page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $pages ?></span>
                            (Total: <?= $total ?> deliveries)
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <?php
                                $qs = '';
                                if ($search) $qs .= '&search=' . urlencode($search);
                                if (!empty($filter) && $filter !== 'all') $qs .= '&type=' . urlencode($filter);
                            ?>
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?><?= $qs ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-text-muted ring-1 ring-inset ring-border hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                    <span class="sr-only">Previous</span>
                                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($page < $pages): ?>
                                <a href="?page=<?= $page + 1 ?><?= $qs ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-text-muted ring-1 ring-inset ring-border hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                    <span class="sr-only">Next</span>
                                    <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require base_path('views/layouts/app.php');
