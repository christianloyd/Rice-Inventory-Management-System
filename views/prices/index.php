<?php 
$title = "Pricing Management";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Manage current prices for all active rice variants.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/prices/history') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="history" class="-ml-1 mr-2 h-5 w-5 text-text-muted"></i>
            Price History
        </a>
    </div>
</div>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($variants)): ?>
                    <?php 
                        $title = "No active variants";
                        $message = "You must have active rice variants to manage pricing.";
                        $icon = 'tag';
                        require __DIR__ . '/../partials/empty-state.php'; 
                    ?>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Variant Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Current Price</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Last Updated</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Updated By</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($variants as $variant): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                    <?= e($variant['name']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-text-primary">
                                    <?php if($variant['current_price'] !== null): ?>
                                        ₱<?= number_format($variant['current_price'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-text-muted font-normal italic">Not Set</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= $variant['effective_date'] ? e($variant['effective_date']) : '-' ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= $variant['updated_by'] ? e($variant['updated_by']) : '-' ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button onclick="updatePriceModal(<?= $variant['id'] ?>, '<?= e($variant['name']) ?>', '<?= $variant['current_price'] ?? '' ?>')" class="text-primary hover:text-primary-hover inline-flex items-center gap-1">
                                        <i data-lucide="edit" class="h-4 w-4"></i>
                                        Change Price
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Update Price Modal -->
<div id="priceModal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-text-primary bg-opacity-75 transition-opacity" onclick="closeModal('priceModal')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md sm:p-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-text-primary mb-2">Change Price</h3>
                    <p class="text-sm text-text-muted mb-4" id="priceModalVariantName"></p>
                    
                    <form action="" method="POST" id="priceForm" onsubmit="showLoadingState(this)">
                        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="new_price" class="block text-sm font-medium text-text-primary">New Price (₱) <span class="text-error">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-text-muted sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="new_price" id="new_price" required class="block w-full rounded-md border-border pl-7 focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                </div>
                            </div>
                            
                            <div>
                                <label for="reason" class="block text-sm font-medium text-text-primary">Reason for change <span class="text-error">*</span></label>
                                <div class="mt-1">
                                    <input type="text" name="reason" id="reason" placeholder="e.g. Supplier price increase" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:col-start-2 sm:text-sm">Update Price</button>
                            <button type="button" onclick="closeModal('priceModal')" class="mt-3 inline-flex w-full justify-center rounded-md border border-border bg-surface px-4 py-2 text-base font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    
    function updatePriceModal(id, name, currentPrice) {
        let form = document.getElementById('priceForm');
        form.action = "<?= url('/prices/') ?>" + id;
        document.getElementById('priceModalVariantName').textContent = name;
        document.getElementById('new_price').value = currentPrice;
        document.getElementById('reason').value = '';
        
        // Reset the form submit button state
        let btn = form.querySelector('button[type="submit"]');
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = 'Update Price';
        
        document.getElementById('priceModal').classList.remove('hidden');
    }
    
    function showLoadingState(form) {
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin -ml-1 mr-2 h-4 w-4"></i> Updating...';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
</script>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
