<?php 
$title = "Business Reports";
ob_start(); 
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Analyze sales performance and track business growth.</p>
    </div>
</div>

<!-- Filters -->
<div class="mt-8 bg-surface shadow rounded-lg border border-border p-4 mb-8">
    <form action="" method="GET" class="flex flex-col sm:flex-row items-end gap-4" id="reportFilterForm">
        <div>
            <label for="filter_type" class="block text-sm font-medium text-text-primary">Report Period</label>
            <select name="filter_type" id="filter_type" onchange="toggleCustomDates()" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2 bg-background">
                <option value="daily" <?= ($filterType === 'daily') ? 'selected' : '' ?>>Daily / Today</option>
                <option value="weekly" <?= ($filterType === 'weekly') ? 'selected' : '' ?>>This Week</option>
                <option value="monthly" <?= ($filterType === 'monthly') ? 'selected' : '' ?>>This Month</option>
                <option value="custom" <?= ($filterType === 'custom') ? 'selected' : '' ?>>Custom Range</option>
            </select>
        </div>
        <div id="custom_start_date_container" class="<?= ($filterType === 'custom') ? '' : 'hidden' ?>">
            <label for="start_date" class="block text-sm font-medium text-text-primary">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="<?= e($startDate) ?>" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
        </div>
        <div id="custom_end_date_container" class="<?= ($filterType === 'custom') ? '' : 'hidden' ?>">
            <label for="end_date" class="block text-sm font-medium text-text-primary">End Date</label>
            <input type="date" name="end_date" id="end_date" value="<?= e($endDate) ?>" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="inline-flex justify-center flex-1 rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                Generate Report
            </button>
            <a href="<?= url('/reports/print') ?>?filter_type=<?= e($filterType) ?>&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>" target="_blank" class="inline-flex justify-center flex-1 rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <i data-lucide="printer" class="h-4 w-4 mr-2"></i> Print Report
            </a>
            <a href="<?= url('/reports/deliveries') ?>" class="inline-flex justify-center flex-1 rounded-md border border-transparent bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                <i data-lucide="truck" class="h-4 w-4 mr-2"></i> Deliveries Report
            </a>
            <a href="<?= url('/reports/utang/print') ?>?filter_type=<?= e($filterType) ?>&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>" target="_blank" class="inline-flex justify-center flex-1 rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                <i data-lucide="banknote" class="h-4 w-4 mr-2"></i> Utang Report
            </a>
            <button type="button" onclick="openCoinsModal()" class="inline-flex items-center justify-center flex-1 rounded-md border border-transparent bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                <i data-lucide="coins" class="h-4 w-4 mr-2"></i> Add Coins
            </button>
        </div>
    </form>
</div>

<script>
function toggleCustomDates() {
    const filterType = document.getElementById('filter_type').value;
    const startContainer = document.getElementById('custom_start_date_container');
    const endContainer = document.getElementById('custom_end_date_container');

    if (filterType === 'custom') {
        startContainer.classList.remove('hidden');
        endContainer.classList.remove('hidden');
    } else {
        startContainer.classList.add('hidden');
        endContainer.classList.add('hidden');
        document.getElementById('reportFilterForm').submit();
    }
}
</script>

<!-- Overview Cards -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mt-4">
    <div class="overflow-hidden rounded-lg bg-surface shadow border border-border">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i data-lucide="banknote" class="h-6 w-6 text-primary"></i>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-xs font-medium uppercase tracking-wider text-text-muted">Total Sales Revenue</dt>
                        <dd>
                            <div class="text-xl font-bold text-primary">₱<?= number_format($totalRevenue, 2) ?></div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-surface shadow border border-border">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i data-lucide="trending-down" class="h-6 w-6 text-rose-600"></i>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-xs font-medium uppercase tracking-wider text-text-muted">Total Expenses</dt>
                        <dd>
                            <div class="text-xl font-bold text-rose-600">−₱<?= number_format($totalExpenses ?? 0, 2) ?></div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-surface shadow border border-border">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i data-lucide="coins" class="h-6 w-6 text-amber-500"></i>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-xs font-medium uppercase tracking-wider text-text-muted">Coins Added (Change)</dt>
                        <dd>
                            <div class="text-xl font-bold text-amber-600">+₱<?= number_format($totalCoins, 2) ?></div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-surface shadow border border-border">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i data-lucide="wallet" class="h-6 w-6 text-emerald-600"></i>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-xs font-medium uppercase tracking-wider text-text-muted">Net Total (<?= ($startDate === $endDate) ? 'Day' : 'Period' ?>)</dt>
                        <dd>
                            <div class="text-xl font-bold text-emerald-600">₱<?= number_format($netTotalDay ?? $cashInStoreTotal ?? 0, 2) ?></div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-surface shadow border border-border">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i data-lucide="shopping-cart" class="h-6 w-6 text-text-muted"></i>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-xs font-medium uppercase tracking-wider text-text-muted">Total Transactions</dt>
                        <dd>
                            <div class="text-xl font-bold text-text-primary"><?= number_format($totalTransactions) ?></div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2 mt-8">
    <div class="bg-surface shadow rounded-lg border border-border p-4">
        <h3 class="text-sm font-medium text-text-primary mb-4">Daily Revenue Trend</h3>
        <div class="relative h-64 w-full">
            <canvas id="dailyRevenueChart"></canvas>
        </div>
    </div>
    <div class="bg-surface shadow rounded-lg border border-border p-4">
        <h3 class="text-sm font-medium text-text-primary mb-4">Variant Sales Breakdown</h3>
        <div class="relative h-64 w-full">
            <canvas id="variantSalesChart"></canvas>
        </div>
    </div>
</div>

<!-- Comprehensive Analysis -->
<div class="mt-8">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg leading-6 font-medium text-text-primary">Comprehensive Stock & Revenue Analysis</h3>
        <a href="<?= url('/reports/comprehensive/print') ?>?filter_type=<?= e($filterType) ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-stone-800 transition-colors">
            <i data-lucide="printer" class="h-4 w-4"></i> Print
        </a>
    </div>
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg mb-8">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-background">
                <tr>
                    <th scope="col" class="py-3 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Variant</th>
                    <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-text-primary">Stock In</th>
                    <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-text-primary">Stock Out</th>
                    <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-orange-600">Pending TBT</th>
                    <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-error">Voided Rice</th>
                    <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-text-primary bg-background/50 border-l border-border">Current Stock Left</th>
                    <th scope="col" class="px-3 py-3 text-right text-sm font-bold text-primary border-l border-border">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-surface">
                <?php if (empty($comprehensive)): ?>
                    <tr><td colspan="7" class="py-4 text-center text-sm text-text-muted">No data available.</td></tr>
                <?php else: ?>
                    <?php foreach($comprehensive as $v): ?>
                        <tr>
                            <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                <?= e($v['variant_name']) ?> <span class="text-xs text-text-muted ml-1"><?= e($v['unit']) ?></span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-text-primary text-right">+<?= number_format($v['stock_in']) ?></td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-text-primary text-right">
                                -<?= number_format($v['stock_out']) ?>
                                <?php if(!empty($v['returned_to_supplier']) && $v['returned_to_supplier'] > 0): ?>
                                    <div class="text-[10px] text-rose-600 font-normal">incl. <?= number_format($v['returned_to_supplier']) ?> returned</div>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-orange-600 text-right"><?= number_format($v['total_tbt']) ?></td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-error text-right"><?= number_format($v['voided_rice']) ?></td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm font-semibold text-text-primary text-right bg-background/20 border-l border-border"><?= number_format($v['stock_left']) ?></td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm font-bold text-primary text-right border-l border-border">₱<?= number_format($v['revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-background">
                <?php
                if (!empty($comprehensive)) {
                    $totalGrossRevenue = array_sum(array_column($comprehensive, 'revenue'));
                    $globalDiscountAll = $globalDiscountAll ?? 0;
                    $totalNetRevenue = $totalGrossRevenue - $globalDiscountAll;
                ?>
                <?php if ($globalDiscountAll > 0): ?>
                <tr>
                    <td colspan="6" class="px-3 py-3 text-right text-sm font-semibold text-rose-600 border-l border-border">Less: Global Discounts</td>
                    <td class="px-3 py-3 text-right text-sm font-bold text-rose-600 border-l border-border">−₱<?= number_format($globalDiscountAll, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="6" class="px-3 py-3 text-right text-sm font-semibold text-text-primary border-l border-border">Net Total Revenue</td>
                    <td class="px-3 py-4 text-right text-sm font-bold text-primary border-l border-border border-t-2">₱<?= number_format($totalNetRevenue, 2) ?></td>
                </tr>
                <?php } ?>
            </tfoot>
        </table>
    </div>

    <!-- Daily Revenue -->
    <div>
        <h3 class="text-lg leading-6 font-medium text-text-primary mb-4">Daily Sales Summary</h3>
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-background">
                    <tr>
                        <th scope="col" class="py-3 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Date</th>
                        <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-text-primary">Transactions</th>
                        <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-text-primary">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border bg-surface">
                    <?php if (empty($salesSummary)): ?>
                        <tr><td colspan="3" class="py-4 text-center text-sm text-text-muted">No sales data for this period.</td></tr>
                    <?php else: ?>
                        <?php foreach($salesSummary as $s): ?>
                            <tr>
                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm text-text-muted sm:pl-6"><?= date('M j, Y', strtotime($s['sale_date'])) ?></td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm text-text-primary text-right"><?= $s['total_transactions'] ?></td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-text-primary text-right">₱<?= number_format($s['total_revenue'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Coins Added Section (Change Fund / Store Coins) -->
    <div class="mt-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg leading-6 font-semibold text-text-primary flex items-center gap-2">
                    <i data-lucide="coins" class="h-5 w-5 text-amber-500"></i>
                    Coins Added (Change Fund / Store Coins)
                </h3>
                <p class="text-xs text-text-muted mt-0.5">Coins inside the store recorded after the day transaction or for change.</p>
            </div>
            <button type="button" onclick="openCoinsModal()" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-3.5 py-2 text-sm font-medium text-white shadow hover:bg-amber-700 transition-colors">
                <i data-lucide="plus-circle" class="h-4 w-4"></i> Add Coins
            </button>
        </div>

        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <?php if (empty($dailyCoins)): ?>
                <div class="bg-surface p-8 text-center text-text-muted text-sm border-dashed border-2 border-border rounded-lg">
                    <i data-lucide="coins" class="h-8 w-8 text-amber-400 mx-auto mb-2 opacity-50"></i>
                    <p class="font-medium text-text-primary">No coins recorded for this period.</p>
                    <p class="text-xs text-text-muted mt-1">Need to add coins for change or store cash? Click below.</p>
                    <button type="button" onclick="openCoinsModal()" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 hover:text-amber-700 bg-amber-500/10 px-3 py-1.5 rounded-md hover:bg-amber-500/20 transition-colors">
                        <i data-lucide="plus-circle" class="h-3.5 w-3.5"></i> Add Coins Now
                    </button>
                </div>
            <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-text-primary">Description / Remarks</th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-text-primary">Recorded By</th>
                            <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-text-primary">Amount</th>
                            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
                                <th scope="col" class="py-3 pr-4 pl-3 text-right text-sm font-semibold text-text-primary sm:pr-6">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach ($dailyCoins as $c): ?>
                            <tr>
                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                    <?= date('M j, Y', strtotime($c['coin_date'])) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm text-text-muted">
                                    <?= e($c['notes'] ?: 'Coins for change') ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm text-text-muted">
                                    <?= e($c['recorded_by_name'] ?? 'System') ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm font-bold text-amber-600 text-right tabular-nums">
                                    +₱<?= number_format($c['amount'], 2) ?>
                                </td>
                                <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
                                    <td class="whitespace-nowrap py-3 pr-4 pl-3 text-right text-sm font-medium sm:pr-6">
                                        <form action="<?= url('/reports/coins/' . $c['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this coin entry of ₱<?= number_format($c['amount'], 2) ?>?');" class="inline">
                                            <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                                            <input type="hidden" name="redirect_filter_type" value="<?= e($filterType) ?>">
                                            <input type="hidden" name="redirect_start_date" value="<?= e($startDate) ?>">
                                            <input type="hidden" name="redirect_end_date" value="<?= e($endDate) ?>">
                                            <button type="submit" class="text-error hover:text-red-700 text-xs font-semibold px-2 py-1 rounded hover:bg-error/10 transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-background font-semibold">
                        <tr>
                            <td class="py-3 pl-4 pr-3 text-sm text-text-primary sm:pl-6" colspan="3">Total Coins for Period</td>
                            <td class="px-3 py-3 text-right text-sm font-bold text-amber-600 tabular-nums">+₱<?= number_format($totalCoins, 2) ?></td>
                            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
                                <td></td>
                            <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Expenses Recorded Section -->
    <div class="mt-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg leading-6 font-semibold text-text-primary flex items-center gap-2">
                    <i data-lucide="trending-down" class="h-5 w-5 text-rose-500"></i>
                    Expenses Recorded
                </h3>
                <p class="text-xs text-text-muted mt-0.5">Operating and store expenses deducted from the day/period net earnings.</p>
            </div>
            <a href="<?= url('/expenses') ?>" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-3.5 py-2 text-sm font-medium text-white shadow hover:bg-rose-700 transition-colors">
                <i data-lucide="external-link" class="h-4 w-4"></i> Manage Expenses
            </a>
        </div>

        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <?php if (empty($expenses)): ?>
                <div class="bg-surface p-8 text-center text-text-muted text-sm border-dashed border-2 border-border rounded-lg">
                    <i data-lucide="receipt" class="h-8 w-8 text-rose-400 mx-auto mb-2 opacity-50"></i>
                    <p class="font-medium text-text-primary">No expenses recorded for this period.</p>
                </div>
            <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-text-primary">Description</th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-text-primary">Category</th>
                            <th scope="col" class="px-3 py-3 text-right text-sm font-semibold text-text-primary">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach ($expenses as $ex): ?>
                            <tr>
                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                    <?= date('M j, Y', strtotime($ex['expense_date'])) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm text-text-muted">
                                    <?= e($ex['description']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm text-text-muted capitalize">
                                    <?= e($ex['category']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm font-bold text-rose-600 text-right tabular-nums">
                                    −₱<?= number_format($ex['amount'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-background font-semibold">
                        <tr>
                            <td class="py-3 pl-4 pr-3 text-sm text-text-primary sm:pl-6" colspan="3">Total Expenses for Period</td>
                            <td class="px-3 py-3 text-right text-sm font-bold text-rose-600 tabular-nums">−₱<?= number_format($totalExpenses, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Data Preparation
    const salesSummaryData = <?= json_encode($salesSummary) ?>;
    const comprehensiveData = <?= json_encode($comprehensive) ?>;

    /** 
     * 1. Daily Revenue Chart (Line Chart) 
     */
    const dailyLabels = salesSummaryData.map(item => item.sale_date).reverse();
    const dailyRevenue = salesSummaryData.map(item => parseFloat(item.total_revenue)).reverse();

    const ctxDaily = document.getElementById('dailyRevenueChart').getContext('2d');
    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Revenue (₱)',
                data: dailyRevenue,
                borderColor: '#16a34a', // Using a primary-like green
                backgroundColor: 'rgba(22, 163, 74, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    /** 
     * 2. Variant Sales Breakdown (Bar Chart)
     */
    const variantLabels = comprehensiveData.map(item => item.variant_name);
    const variantRevenue = comprehensiveData.map(item => parseFloat(item.revenue));

    const ctxVariant = document.getElementById('variantSalesChart').getContext('2d');
    new Chart(ctxVariant, {
        type: 'bar',
        data: {
            labels: variantLabels,
            datasets: [{
                label: 'Revenue (₱)',
                data: variantRevenue,
                backgroundColor: '#16a34a',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<!-- Modal for Adding Coins -->
<div id="coinsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-md bg-surface rounded-2xl shadow-2xl border border-border p-6 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-border">
            <div class="flex items-center gap-2.5">
                <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                    <i data-lucide="coins" class="h-5 w-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-text-primary">Add Store Coins</h3>
                    <p class="text-xs text-text-muted">Record change fund or coins inside store</p>
                </div>
            </div>
            <button type="button" onclick="closeCoinsModal()" class="text-text-muted hover:text-text-primary p-1.5 rounded-lg hover:bg-background transition-colors">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>

        <form action="<?= url('/reports/coins') ?>" method="POST" class="mt-5 space-y-4">
            <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
            <input type="hidden" name="redirect_filter_type" value="<?= e($filterType) ?>">
            <input type="hidden" name="redirect_start_date" value="<?= e($startDate) ?>">
            <input type="hidden" name="redirect_end_date" value="<?= e($endDate) ?>">

            <div>
                <label for="coin_date" class="block text-sm font-medium text-text-primary mb-1">Date</label>
                <input type="date" name="coin_date" id="coin_date" value="<?= e($startDate ?: date('Y-m-d')) ?>" required class="block w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
            </div>

            <div>
                <label for="coin_amount" class="block text-sm font-medium text-text-primary mb-1">Coin Amount (₱)</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-text-muted sm:text-sm font-semibold">₱</span>
                    </div>
                    <input type="number" step="0.01" min="0.01" name="amount" id="coin_amount" required placeholder="0.00" class="block w-full rounded-lg border border-border bg-background pl-8 pr-3 py-2 text-base font-semibold text-text-primary focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                </div>
            </div>

            <div>
                <label for="coin_notes" class="block text-sm font-medium text-text-primary mb-1">Description / Notes</label>
                <input type="text" name="notes" id="coin_notes" value="Coins for change" placeholder="e.g., End of day coins, Sukli coins" class="block w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-border">
                <button type="button" onclick="closeCoinsModal()" class="px-4 py-2 rounded-lg border border-border text-sm font-medium text-text-muted hover:bg-background transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-amber-600 text-sm font-semibold text-white shadow hover:bg-amber-700 transition-colors flex items-center gap-2">
                    <i data-lucide="check" class="h-4 w-4"></i> Save Coins
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCoinsModal() {
    const modal = document.getElementById('coinsModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        const input = document.getElementById('coin_amount');
        if (input) input.focus();
        if (window.lucide) {
            lucide.createIcons();
        }
    }, 50);
}

function closeCoinsModal() {
    document.getElementById('coinsModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCoinsModal();
    }
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
