<?php 
$title = "Override Current Stock";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Force exactly what the inventory count should be. Use this for physical stock corrections instead of logging supplier delta additions.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/stock-ins') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="arrow-left" class="-ml-1 mr-2 h-5 w-5 text-text-muted"></i>
            Back to Stock-Ins
        </a>
    </div>
</div>

<div class="mt-8">
    <form action="<?= url('/stock-ins/override') ?>" method="POST" class="space-y-8 divide-y divide-border">
        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
        <div class="space-y-6 sm:space-y-5">
            <div class="bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6 divide-y divide-border">
                <div class="md:grid md:grid-cols-3 md:gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-lg font-medium leading-6 text-text-primary">Absolute Stock Correction</h3>
                        <p class="mt-1 text-sm text-text-muted">Select the specific variant and type exactly how many sacks you physically counted. The system will automatically reverse or add the missing metrics natively.</p>
                    </div>
                    <div class="mt-5 space-y-6 md:col-span-2 md:mt-0">
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            
                            <!-- Variant Selection -->
                            <div class="sm:col-span-4">
                                <label for="variant_id" class="block text-sm font-medium text-text-primary">Rice Variant <span class="text-error">*</span></label>
                                <div class="mt-1 relative">
                                    <select id="variant_id" name="variant_id" required class="block w-full rounded-md border-border py-2 pl-3 pr-10 text-base focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm">
                                        <option value="">Select a variant...</option>
                                        <?php foreach ($variants as $variant): ?>
                                            <option value="<?= $variant['id'] ?>" data-stock="<?= $variant['current_stock_cache'] ?>">
                                                <?= e($variant['name']) ?><?= !empty($variant['unit']) ? ' (' . e($variant['unit']) . ')' : '' ?> — Current Stock: <?= number_format($variant['current_stock_cache']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Absolute Quantity Input -->
                            <div class="sm:col-span-4">
                                <label for="absolute_quantity" class="block text-sm font-medium text-error">True Physical Count (Absolute) <span class="text-error">*</span></label>
                                <div class="mt-1">
                                    <input type="number" step="0.01" min="0" name="absolute_quantity" id="absolute_quantity" required placeholder="e.g. 15" class="block w-full rounded-md border-error shadow-sm focus:border-error focus:ring-error sm:text-sm border-2 px-3 py-2">
                                </div>
                                <p class="mt-2 text-xs text-text-muted">Type the ACTUAL number of sacks on the floor. Do not type the difference.</p>
                            </div>

                            <!-- Reason String -->
                            <div class="sm:col-span-4">
                                <label for="reason" class="block text-sm font-medium text-text-primary">Correction Reason</label>
                                <div class="mt-1">
                                    <input type="text" name="reason" id="reason" placeholder="e.g. Discovered undocumented damaged sacks..." class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5">
            <div class="flex justify-end gap-3">
                <a href="<?= url('/stock-ins') ?>" class="rounded-md border border-border bg-white py-2 px-4 text-sm font-medium text-text-primary shadow-sm hover:bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-error py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-error/90 focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2">
                    <i data-lucide="shield-alert" class="-ml-1 mr-2 h-5 w-5"></i>
                    Execute Override Checkout
                </button>
            </div>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
