<?php
$title = "Record Stock Delivery";
ob_start();
?>

<div class="mt-6 bg-surface shadow sm:rounded-lg mb-6 max-w-2xl mx-auto">
    <div class="px-4 py-5 sm:p-6">
        <form action="<?= url('/deliveries') ?>" method="POST" id="deliveryForm" class="space-y-6">
            <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
            
            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                <div class="sm:col-span-2">
                    <label for="stock_in_date" class="block text-sm font-medium text-text-primary">Date *</label>
                    <div class="mt-1">
                        <input type="date" name="stock_in_date" id="stock_in_date" value="<?= date('Y-m-d') ?>" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="rice_variant_id" class="block text-sm font-medium text-text-primary">Variant *</label>
                    <div class="mt-1">
                        <select name="rice_variant_id" id="rice_variant_id" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border bg-white">
                            <option value="">Select Variant...</option>
                            <?php foreach($variants as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="quantity" class="block text-sm font-medium text-text-primary">Quantity (Sacks) *</label>
                    <div class="mt-1">
                        <input type="number" min="1" step="1" name="quantity" id="quantity" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>
                
                <div class="sm:col-span-2">
                    <label for="reference_note" class="block text-sm font-medium text-text-primary">Reference Note / Supplier</label>
                    <div class="mt-1">
                        <input type="text" name="reference_note" id="reference_note" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end pt-4">
                <a href="<?= url('/deliveries') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-text-secondary shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 w-auto mr-3">
                    Cancel
                </a>
                <button type="submit" id="submitBtn" class="inline-flex justify-center rounded-md border border-transparent bg-primary py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Record Delivery
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('deliveryForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin mr-2 h-4 w-4"></i> Saving...';
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
