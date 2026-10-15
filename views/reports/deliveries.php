<?php 
$title = "Deliveries Report";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-2xl font-semibold text-text-primary">Deliveries Report</h1>
        <p class="mt-2 text-sm text-text-muted">Analyze outbound customer deliveries, stock volumes, and total income generated.</p>
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
            <button type="submit" class="inline-flex justify-center flex-1 rounded-md border border-transparent bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                Generate Report
            </button>
            <a href="<?= url('/reports/print_deliveries') ?>?filter_type=<?= e($filterType) ?>&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>" target="_blank" class="inline-flex justify-center flex-1 rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <i data-lucide="printer" class="h-4 w-4 mr-2"></i> Print Report
            </a>
            <a href="<?= url('/reports') ?>" class="inline-flex justify-center flex-1 rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                Back to Sales Reports
            </a>
        </div>
    </form>
</div>

<!-- Key Metrics -->
<div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-2">
    <div class="relative overflow-hidden rounded-lg bg-surface px-4 pt-5 pb-12 shadow border border-border sm:px-6 sm:pt-6">
        <dt>
            <div class="absolute rounded-md bg-teal-500 p-3">
                <i data-lucide="truck" class="h-6 w-6 text-white"></i>
            </div>
            <p class="ml-16 truncate text-sm font-medium text-text-muted">Total Sacks Delivered</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
            <p class="text-2xl font-semibold text-text-primary"><?= number_format($totalDeliveries) ?></p>
        </dd>
    </div>
    <div class="relative overflow-hidden rounded-lg bg-surface px-4 pt-5 pb-12 shadow border border-border sm:px-6 sm:pt-6">
        <dt>
            <div class="absolute rounded-md bg-green-500 p-3">
                <i data-lucide="banknote" class="h-6 w-6 text-white"></i>
            </div>
            <p class="ml-16 truncate text-sm font-medium text-text-muted">Total Delivery Income</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
            <p class="text-2xl font-semibold text-text-primary">₱<?= number_format($totalRevenue, 2) ?></p>
        </dd>
    </div>
</div>

<!-- Variant Delivery Summary -->
<div class="mt-8 bg-surface shadow rounded-lg border border-border p-4">
    <h2 class="text-lg font-medium text-text-primary mb-4">Delivery Variant Summary</h2>
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Rice Variant</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Sacks Delivered</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Income Generated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php if (empty($deliveryReport)): ?>
                            <tr><td colspan="3" class="py-4 text-center text-sm text-text-muted">No deliveries recorded during this period.</td></tr>
                        <?php else: ?>
                            <?php foreach ($deliveryReport as $item): ?>
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6"><?= e($item['variant_name']) ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary text-right"><?= number_format($item['total_quantity_delivered']) ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary text-right">₱<?= number_format($item['total_revenue'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
