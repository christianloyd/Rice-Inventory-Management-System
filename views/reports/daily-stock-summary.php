<?php
$title = "Daily Stock Summary";
ob_start();
?>

<div class="sm:flex sm:items-center justify-between">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-text-muted">A derived report showing the movement of stock for any given day.</p>
    </div>
</div>

<div class="mt-6 flex flex-col md:flex-row gap-4 justify-between items-center bg-surface p-4 rounded-lg shadow-sm w-max">
    <form action="" method="GET" class="flex items-center space-x-4">
        <label for="date" class="block text-sm font-medium text-text-primary">Filter Date:</label>
        <div class="relative">
            <input type="date" name="date" id="date" value="<?= e($date) ?>" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
        </div>
        <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
            View
        </button>
    </form>
</div>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Variant</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-text-muted">Old Stock</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-green-700">Stock In</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-red-700">Stock Out (Walk-in)</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-red-700">Stock Out (Delivered)</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-rose-800">Stock Out (Returned)</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-red-700 border-l border-border">Stock Out (Total)</th>
                            <th scope="col" class="py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-bold text-text-primary border-l border-border">Remaining Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($summary as $row): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                    <?= e($row['variant_name']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-text-muted">
                                    <?= number_format($row['old_stock']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center font-semibold text-green-700">
                                    +<?= number_format($row['stock_in']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center font-semibold text-red-700">
                                    -<?= number_format($row['stock_out_walkin']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center font-semibold text-red-700">
                                    -<?= number_format($row['stock_out_delivered']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center font-semibold text-rose-800">
                                    -<?= number_format($row['stock_out_returned'] ?? 0) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center font-bold text-red-900 border-l border-border bg-red-50/30">
                                    -<?= number_format($row['stock_out_total']) ?>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-base font-bold sm:pr-6 border-l border-border bg-gray-50/50">
                                    <?= number_format($row['remaining_stock']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($summary)): ?>
                            <tr><td colspan="7" class="py-4 text-center text-sm text-text-muted">No variants found to report on.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <p class="text-xs text-text-muted text-right mt-2">
                * Note: Remaining Stock calculation evaluates all movements up to this day. Stock-out totals include pending TBT items.
            </p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
