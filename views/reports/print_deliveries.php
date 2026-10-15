<?php
$title = "Deliveries Report";
ob_start();
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
            <h1 class="text-2xl font-bold text-stone-900 font-serif tracking-tight"><?= e(\App\Models\Setting::get('store_name', 'MAE CONSUMER AND GOODS TRADING')) ?></h1>
            <p class="text-xs text-stone-500 uppercase tracking-widest mt-1 font-medium">Outbound Logistics</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-medium text-stone-700">Deliveries Report</p>
            <p class="text-sm text-stone-500">
                <?php
                if (!empty($endDate) && date('Y-m-d', strtotime($endDate)) !== date('Y-m-d', strtotime($startDate ?? 'now'))) {
                    echo date('F j', strtotime($startDate)) . ' – ' . date('F j, Y', strtotime($endDate));
                } else {
                    echo date('F j, Y', strtotime($startDate));
                }
                ?>
            </p>
        </div>
    </div>
 
    <!-- Delivery Section -->
    <div class="mt-8">
        <h2 class="text-xl font-bold font-serif text-stone-800 mb-4">Stock Deliveries</h2>
        <table class="w-full text-left text-sm mb-2">
            <thead>
                <tr class="border-b border-stone-300">
                    <th class="py-2 pr-2 font-semibold text-stone-500 text-left w-1/2">Variety</th>
                    <th class="py-2 px-2 font-semibold text-stone-500 text-right">Sacks Delivered</th>
                    <th class="py-2 pl-2 font-semibold text-stone-500 text-right">Income Generated</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deliveryReport)): ?>
                    <tr>
                        <td colspan="3" class="py-8 text-center text-stone-400">No deliveries recorded during this period.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($deliveryReport as $item): ?>
                        <tr class="border-b border-stone-200">
                            <td class="py-2.5 pr-2 text-stone-900"><?= e($item['variant_name']) ?><?= !empty($item['variant_unit']) ? ' (' . e($item['variant_unit']) . ')' : '' ?></td>
                            <td class="py-2.5 px-2 text-right tabular-nums text-stone-800"><?= number_format($item['total_quantity_delivered']) ?></td>
                            <td class="py-2.5 pl-2 text-right tabular-nums text-stone-800">₱<?= number_format($item['total_revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="flex flex-col items-end gap-1 mb-8 mt-2">
            <div class="flex justify-between w-[250px] text-sm pt-2">
                <span class="font-medium text-stone-500">Gross Sacks Delivered:</span>
                <span class="font-bold tabular-nums text-stone-900"><?= number_format($totalDeliveries) ?></span>
            </div>
            <div class="flex justify-between w-[250px] text-sm">
                <span class="font-medium text-stone-500">Total Sacks Income:</span>
                <span class="font-bold tabular-nums text-stone-900">₱<?= number_format($totalRevenue, 2) ?></span>
            </div>
        </div>
    </div>

</div>

<!-- Auto-print script -->
<script>
window.onload = function() {
    window.print();
};
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/print.php'; 
?>
