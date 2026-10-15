<?php 
$title = "Record Stock-In";
ob_start(); 
?>

<div class="bg-surface shadow rounded-lg border border-border">
    <div class="px-4 py-5 sm:p-6">
        <form action="<?= url('/stock-ins') ?>" method="POST" id="stockInForm">
            <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
            
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-2">
                    <label for="supplier_name" class="block text-sm font-medium text-text-primary">Supplier Name <span class="text-error">*</span></label>
                    <div class="mt-1">
                        <input type="text" name="supplier_name" id="supplier_name" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="reference_number" class="block text-sm font-medium text-text-primary">Reference / Receipt Number</label>
                    <div class="mt-1">
                        <input type="text" name="reference_number" id="reference_number" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="stock_in_date" class="block text-sm font-medium text-text-primary">Stock-In Date <span class="text-error">*</span></label>
                    <div class="mt-1">
                        <input type="date" name="stock_in_date" id="stock_in_date" value="<?= date('Y-m-d') ?>" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <hr class="border-border my-2">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium leading-6 text-text-primary">Received Items</h3>
                        <button type="button" onclick="addItemRow()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary bg-primary bg-opacity-10 hover:bg-opacity-20 focus:outline-none">
                            <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Add Row
                        </button>
                    </div>

                    <div id="itemsContainer" class="space-y-3">
                        <!-- Initial Row -->
                        <div class="flex items-end gap-3 item-row">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-text-muted mb-1">Variant <span class="text-error">*</span></label>
                                <select name="variants[]" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                    <option value="">Select Variant...</option>
                                    <?php foreach($variants as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-medium text-text-muted mb-1">Quantity <span class="text-error">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="quantities[]" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-medium text-text-muted mb-1">Unit Cost (₱)</label>
                                <input type="number" step="0.01" min="0" name="costs[]" value="0" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                            </div>
                            <div class="w-10 flex border border-transparent pb-1">
                                <!-- empty placeholder so spacing aligns with inputs -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <label for="notes" class="block text-sm font-medium text-text-primary">Additional Notes</label>
                    <div class="mt-1">
                        <textarea id="notes" name="notes" rows="3" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border"></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="<?= url('/stock-ins') ?>" class="bg-surface border border-border rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-text-primary hover:bg-background focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Cancel</a>
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Save Stock-In</button>
            </div>
        </form>
    </div>
</div>

<template id="itemRowTemplate">
    <div class="flex items-end gap-3 item-row mt-3">
        <div class="flex-1">
            <select name="variants[]" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                <option value="">Select Variant...</option>
                <?php foreach($variants as $v): ?>
                    <option value="<?= $v['id'] ?>"><?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-32">
            <input type="number" step="0.01" min="0.01" name="quantities[]" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
        </div>
        <div class="w-32">
            <input type="number" step="0.01" min="0" name="costs[]" value="0" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
        </div>
        <div class="w-10 flex pb-1 items-center justify-center">
            <button type="button" onclick="this.closest('.item-row').remove()" class="text-error hover:opacity-75">
                <i data-lucide="trash-2" class="h-5 w-5"></i>
            </button>
        </div>
    </div>
</template>

<script>
    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const template = document.getElementById('itemRowTemplate');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
        lucide.createIcons();
    }
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
