<?php
$title = "Daily Sales Report";
ob_start();
// User considers cheques as positive accumulated value
$netDay = $todayTotalRevenue + ($todayTotalChequeRevenue ?? 0) + ($todayTotalCoins ?? 0) - $todayTotalExpenses;
?>
<div class="print-container max-w-4xl mx-auto bg-white p-8 font-sans text-stone-900">
 
    <!-- On-screen controls (hidden when printing) -->
    <div class="no-print flex justify-end gap-2 mb-4">
        <a href="javascript:window.close();" class="inline-flex items-center gap-2 h-10 px-4 rounded-xl border text-sm hover:bg-stone-50">
            Close
        </a>
        <button onclick="window.print();" class="inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-stone-900 text-white text-sm font-semibold hover:bg-stone-800">
            Print
        </button>
    </div>
 
    <!-- Header -->
    <div class="flex justify-between items-end mb-4 border-b border-stone-300 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 font-serif tracking-tight">MAE CONSUMER AND GOODS TRADING</h1>
            <p class="text-xs text-stone-500 uppercase tracking-widest mt-1 font-medium">MAE CONSUMER GOODS TRADING</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-medium text-stone-700">Daily Sales Report</p>
            <p class="text-sm text-stone-500">
                <?php
                if (!empty($endDate) && date('Y-m-d', strtotime($endDate)) !== date('Y-m-d', strtotime($startDate ?? 'now'))) {
                    echo date('F j', strtotime($startDate)) . ' – ' . date('F j, Y', strtotime($endDate));
                } else {
                    echo date('F j, Y', strtotime($startDate ?? 'now'));
                }
                ?>
            </p>
        </div>
    </div>
 
    <!-- Rice Sold (Cash) Section -->
    <div class="mt-8">
        <h2 class="text-xl font-bold font-serif text-stone-800 mb-4">Rice sold (Cash)</h2>
        <table class="w-full text-left text-sm mb-2">
            <thead>
                <tr class="border-b border-stone-300">
                    <th class="py-2 pr-2 font-semibold text-stone-500 text-left w-1/2">Variety</th>
                    <th class="py-2 px-2 font-semibold text-stone-500 text-right">Units sold</th>
                    <th class="py-2 pl-2 font-semibold text-stone-500 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($todayVariantSales)): ?>
                    <tr>
                        <td colspan="3" class="py-8 text-center text-stone-400">No cash sales recorded.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($todayVariantSales as $v): ?>
                        <tr class="border-b border-stone-200">
                            <td class="py-2.5 pr-2 text-stone-900"><?= e($v['variant_name']) ?><?= !empty($v['variant_unit']) ? ' (' . e($v['variant_unit']) . ')' : '' ?></td>
                            <td class="py-2.5 px-2 text-right tabular-nums text-stone-800"><?= number_format($v['total_quantity_sold']) ?></td>
                            <td class="py-2.5 pl-2 text-right tabular-nums text-stone-800">₱<?= number_format($v['total_revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (isset($globalDiscountCash) && $globalDiscountCash > 0): ?>
                <tr class="font-semibold text-rose-700 border-t border-stone-300">
                    <td class="py-2.5 pr-2">Less: Global Discounts</td>
                    <td class="py-2.5 px-2 text-right"></td>
                    <td class="py-2.5 pl-2 text-right tabular-nums">−₱<?= number_format($globalDiscountCash, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="font-semibold <?= isset($globalDiscountCash) && $globalDiscountCash > 0 ? '' : 'border-t border-stone-300' ?>">
                    <td class="py-2.5 pr-2">Total Net Cash Revenue</td>
                    <td class="py-2.5 px-2 text-right tabular-nums"><?= number_format($todayTotalQuantity) ?></td>
                    <td class="py-2.5 pl-2 text-right tabular-nums">₱<?= number_format($todayTotalRevenue, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Rice Sold (Cheques) Section -->
    <?php if (!empty($todayChequeSales) || $todayTotalChequeRevenue > 0): ?>
    <div class="mt-12">
        <h2 class="text-xl font-bold font-serif text-stone-800 mb-4">Rice sold (Cheques)</h2>
        <table class="w-full text-left text-sm mb-2">
            <thead>
                <tr class="border-b border-stone-300">
                    <th class="py-2 pr-2 font-semibold text-stone-500 text-left w-1/2">Variety</th>
                    <th class="py-2 px-2 font-semibold text-stone-500 text-right">Units sold</th>
                    <th class="py-2 pl-2 font-semibold text-stone-500 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($todayChequeSales as $cv): ?>
                    <tr class="border-b border-stone-200">
                        <td class="py-2.5 pr-2 text-stone-900"><?= e($cv['variant_name']) ?><?= !empty($cv['variant_unit']) ? ' (' . e($cv['variant_unit']) . ')' : '' ?></td>
                        <td class="py-2.5 px-2 text-right tabular-nums text-stone-800"><?= number_format($cv['total_quantity_sold']) ?></td>
                        <td class="py-2.5 pl-2 text-right tabular-nums text-stone-800">₱<?= number_format($cv['total_revenue'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (isset($globalDiscountCheque) && $globalDiscountCheque > 0): ?>
                <tr class="font-semibold text-rose-700 border-t border-stone-300">
                    <td class="py-2.5 pr-2">Less: Global Discounts</td>
                    <td class="py-2.5 px-2 text-right"></td>
                    <td class="py-2.5 pl-2 text-right tabular-nums">−₱<?= number_format($globalDiscountCheque, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="font-semibold <?= isset($globalDiscountCheque) && $globalDiscountCheque > 0 ? '' : 'border-t border-stone-300' ?>">
                    <td class="py-2.5 pr-2">Total Net Cheque Revenue</td>
                    <td class="py-2.5 px-2 text-right tabular-nums"><?= number_format($todayTotalChequeQuantity) ?></td>
                    <td class="py-2.5 pl-2 text-right tabular-nums text-stone-900">₱<?= number_format($todayTotalChequeRevenue, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
 
    <!-- Expenses Section -->
    <div class="mt-12">
        <h2 class="text-xl font-bold font-serif text-stone-800 mb-4">Expenses</h2>
        <table class="w-full text-left text-sm mb-2">
            <thead>
                <tr class="border-b border-stone-300">
                    <th class="py-2 pr-2 font-semibold text-stone-500 text-left w-1/2">Description</th>
                    <th class="py-2 px-2 font-semibold text-stone-500 text-left">Category</th>
                    <th class="py-2 pl-2 font-semibold text-stone-500 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($todayExpenses)): ?>
                    <tr>
                        <td colspan="3" class="py-8 text-center text-stone-400">No expenses recorded.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($todayExpenses as $ex): ?>
                        <tr class="border-b border-stone-200">
                            <td class="py-2.5 pr-2 text-stone-900"><?= e($ex['description']) ?></td>
                            <td class="py-2.5 px-2 text-stone-600 capitalize"><?= e($ex['category']) ?></td>
                            <td class="py-2.5 pl-2 text-right tabular-nums text-stone-800">₱<?= number_format($ex['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="font-semibold">
                    <td class="py-2.5 pr-2" colspan="2">Total expenses</td>
                    <td class="py-2.5 pl-2 text-right tabular-nums">₱<?= number_format($todayTotalExpenses, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
 
    <!-- Coins Section (if any recorded) -->
    <?php if (!empty($todayCoins)): ?>
    <div class="mt-12">
        <h2 class="text-xl font-bold font-serif text-stone-800 mb-4">Coins (Store Coins / Change)</h2>
        <table class="w-full text-left text-sm mb-2">
            <thead>
                <tr class="border-b border-stone-300">
                    <th class="py-2 pr-2 font-semibold text-stone-500 text-left w-1/2">Description / Note</th>
                    <th class="py-2 px-2 font-semibold text-stone-500 text-left">Date</th>
                    <th class="py-2 pl-2 font-semibold text-stone-500 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($todayCoins as $coin): ?>
                    <tr class="border-b border-stone-200">
                        <td class="py-2.5 pr-2 text-stone-900"><?= e($coin['notes'] ?: 'Coins for change') ?></td>
                        <td class="py-2.5 px-2 text-stone-600"><?= date('M j, Y', strtotime($coin['coin_date'])) ?></td>
                        <td class="py-2.5 pl-2 text-right tabular-nums text-stone-800">+₱<?= number_format($coin['amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="font-semibold">
                    <td class="py-2.5 pr-2" colspan="2">Total coins added</td>
                    <td class="py-2.5 pl-2 text-right tabular-nums text-stone-900">+₱<?= number_format($todayTotalCoins, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Net Calculation -->
    <div class="mt-12 border-t border-stone-300 pt-4">
        
        <div class="flex justify-between items-center text-base mb-1">
            <span class="font-medium text-stone-900">Total Cash Sales</span>
            <span class="tabular-nums text-stone-900">₱<?= number_format($todayTotalRevenue, 2) ?></span>
        </div>
        
        <?php if (!empty($todayTotalChequeRevenue) && $todayTotalChequeRevenue > 0): ?>
        <div class="flex justify-between items-center text-base mb-1 text-stone-600">
            <span class="font-medium">Add Cheque Payments (To Be Cleared)</span>
            <span class="tabular-nums">+₱<?= number_format($todayTotalChequeRevenue, 2) ?></span>
        </div>
        <?php endif; ?>

        <div class="flex justify-between items-center text-lg mb-4 font-semibold text-stone-900 border-b border-stone-200 pb-2">
            <span>Total Sales Revenue</span>
            <span class="tabular-nums">₱<?= number_format($todayTotalRevenue + ($todayTotalChequeRevenue ?? 0), 2) ?></span>
        </div>

        <?php if (!empty($todayTotalCoins)): ?>
        <div class="flex justify-between items-center text-base text-stone-800 mb-1">
            <span class="font-medium">Add coins (change / store coins)</span>
            <span class="tabular-nums">+₱<?= number_format($todayTotalCoins, 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="flex justify-between items-center text-base text-rose-700 pb-4 border-b border-stone-300 mb-4">
            <span class="font-medium">Less expenses</span>
            <span class="tabular-nums">−₱<?= number_format($todayTotalExpenses, 2) ?></span>
        </div>
        <div class="flex justify-between items-center text-2xl font-serif text-stone-900">
            <span>Net total for the <?= (!empty($endDate) && date('Y-m-d', strtotime($endDate)) !== date('Y-m-d', strtotime($startDate ?? 'now'))) ? 'period' : 'day' ?></span>
            <span class="font-bold">₱<?= number_format($netDay, 2) ?></span>
        </div>
    </div>
 
    <!-- Footer -->
    <div class="mt-10 text-center text-xs text-stone-400">
        <p>Generated <?= date('M j, Y g:i A') ?></p>
    </div>
</div>
 
<script>
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 500);
}
</script>
 
<style>
@media print {
    @page { margin: 15mm; size: auto; }
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
}
/* Ensure elegant serif for specific classes */
.font-serif {
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}
</style>
 
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/print.php';
?>
