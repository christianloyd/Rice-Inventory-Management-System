<?php
$title = "Daily Utang Report";
ob_start();
?>
<div class="print-container max-w-4xl mx-auto bg-white p-8 font-sans text-stone-900">
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
            <p class="text-sm font-medium text-stone-700">Daily Utang Report</p>
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

    <!-- Table -->
    <h2 class="text-xl font-bold font-serif text-stone-800 mb-4">Utang Summary</h2>
    <table class="w-full text-left text-sm mb-2 border-collapse">
        <thead>
            <tr class="border-b border-stone-300">
                <th class="py-2 pr-2 font-semibold text-stone-500 text-left w-1/2">Variant</th>
                <th class="py-2 px-2 font-semibold text-stone-500 text-right">Quantity</th>
                <th class="py-2 pl-2 font-semibold text-stone-500 text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($utangReport)): ?>
                <tr>
                    <td colspan="3" class="py-8 text-center text-stone-400">No utang records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach($utangReport as $v): ?>
                    <tr class="border-b border-stone-200">
                        <td class="py-2.5 pr-2 font-medium text-stone-900">
                            <?= e($v['variant_name']) ?> <?= !empty($v['variant_unit']) ? '<span class="text-xs text-stone-500 ml-1">(' . e($v['variant_unit']) . ')</span>' : '' ?>
                        </td>
                        <td class="py-2.5 px-2 text-right tabular-nums text-stone-800"><?= number_format($v['total_quantity']) ?></td>
                        <td class="py-2.5 pl-2 text-right tabular-nums text-stone-900 font-bold">₱<?= number_format($v['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($utangReport)): ?>
        <tfoot>
            <tr class="border-t-2 border-stone-800 bg-stone-100">
                <td class="py-3 pr-2 font-bold text-stone-900">Total</td>
                <td class="py-3 px-2 text-right tabular-nums font-bold text-stone-900"><?= number_format($totalUtangQuantity) ?></td>
                <td class="py-3 pl-2 text-right tabular-nums font-bold text-stone-900">₱<?= number_format($totalUtangAmount, 2) ?></td>
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
    @page { margin: 15mm; size: portrait; }
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
