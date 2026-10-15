<?php
$title = "Utang Report";
$currentStatus = $filters['status'] ?? 'outstanding';
ob_start();
?>
<div class="print-container max-w-6xl mx-auto bg-white p-8 font-sans text-stone-900">
    <!-- On-screen controls (hidden when printing) -->
    <div class="no-print flex justify-end gap-2 mb-4">
        <a href="javascript:window.close();" class="inline-flex items-center gap-2 h-10 px-4 rounded-xl border border-stone-200 text-sm hover:bg-stone-50 transition-colors">
            Close
        </a>
        <button onclick="window.print();" class="inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-stone-900 text-white text-sm font-semibold hover:bg-stone-800 transition-colors">
            Print
        </button>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-end mb-8 border-b border-stone-300 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 font-serif tracking-tight">MAE CONSUMER AND GOODS TRADING</h1>
            <p class="text-xs text-stone-500 uppercase tracking-widest mt-1 font-medium">MAE CONSUMER GOODS TRADING</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-medium text-stone-700">Utang (Credit) Report</p>
            <p class="text-sm text-stone-500">
                Status: <?= $currentStatus === 'paid' ? 'Fully Paid' : ($currentStatus === 'outstanding' ? 'Outstanding' : 'All Records') ?>
                <?php if (!empty($search)): ?>
                    <br>Filter: "<?= e($search) ?>"
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Table -->
    <h2 class="text-xl font-bold font-serif text-stone-800 mb-4">List of Utang Accounts</h2>
    <table class="w-full text-left text-sm mb-2 border-collapse">
        <thead>
            <tr class="border-b border-stone-300">
                <th class="py-2 pr-2 font-semibold text-stone-500 text-left w-1/4">Customer / Invoice</th>
                <th class="py-2 px-2 font-semibold text-stone-500 text-left w-1/4">Variant(s)</th>
                <th class="py-2 px-2 font-semibold text-stone-500 text-right">Initial Total</th>
                <th class="py-2 px-2 font-semibold text-stone-500 text-right">Paid Amount</th>
                <th class="py-2 pl-2 font-semibold text-stone-500 text-right">Balance</th>
                <th class="py-2 pl-4 font-semibold text-stone-500 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="6" class="py-8 text-center text-stone-400">No utang records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach($records as $rec): ?>
                    <tr class="border-b border-stone-200">
                        <td class="py-2.5 pr-2">
                            <div class="font-bold text-stone-900"><?= e($rec['customer_name']) ?></div>
                            <div class="text-stone-500 text-xs font-mono"><?= e($rec['transaction_no']) ?></div>
                            <div class="text-stone-400 text-xs mt-0.5"><?= date('M j, Y h:i A', strtotime($rec['created_at'])) ?></div>
                        </td>
                        <td class="py-2.5 px-2 text-stone-800">
                            <?= e($rec['variants_summary']) ?>
                        </td>
                        <td class="py-2.5 px-2 text-right tabular-nums text-stone-900">
                            ₱<?= number_format($rec['total_amount'], 2) ?>
                        </td>
                        <td class="py-2.5 px-2 text-right tabular-nums text-green-700 font-medium">
                            ₱<?= number_format($rec['paid_amount'], 2) ?>
                        </td>
                        <td class="py-2.5 pl-2 text-right tabular-nums text-red-600 font-bold">
                            ₱<?= number_format(max(0, $rec['balance_remaining']), 2) ?>
                        </td>
                        <td class="py-2.5 pl-4 flex items-center h-full pt-4">
                            <?php if($rec['payment_status'] === 'paid'): ?>
                                <span class="inline-flex rounded-full bg-green-100 border-green-200 border px-2.5 py-0.5 text-xs font-medium text-green-800">Fully Paid</span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-yellow-100 border-yellow-200 border px-2.5 py-0.5 text-xs font-medium text-yellow-800">Outstanding</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($records)): ?>
        <tfoot>
            <tr class="border-t-2 border-stone-800 bg-stone-100">
                <td colspan="2" class="py-3 pr-2 font-bold text-stone-900">Totals</td>
                <td class="py-3 px-2 text-right tabular-nums font-bold text-stone-900">₱<?= number_format(array_sum(array_column($records, 'total_amount')), 2) ?></td>
                <td class="py-3 px-2 text-right tabular-nums font-bold text-green-700">₱<?= number_format(array_sum(array_column($records, 'paid_amount')), 2) ?></td>
                <td class="py-3 pl-2 text-right tabular-nums font-bold text-red-600">₱<?= number_format(array_sum(array_column($records, 'balance_remaining')), 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

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
    @page { margin: 15mm; size: landscape; }
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
}
.font-serif {
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/print.php';
?>
