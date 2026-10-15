<?php 
$title = "Rice Variants";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Manage the inventory items you sell, their minimum stock levels, and initial pricing.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button type="button" onclick="openModal('addModal')" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="plus" class="-ml-1 mr-2 h-5 w-5"></i>
            Add Variant
        </button>
    </div>
</div>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($variants)): ?>
                    <?php 
                        $title = "No rice variants found";
                        $message = "Add your first rice variant to start managing inventory.";
                        $icon = 'wheat';
                        $actionUrl = "#"; // Handled by button above actually, so let's hide action if empty state used
                        require __DIR__ . '/../partials/empty-state.php'; 
                    ?>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Unit / Kgs</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Stock</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Current Price</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($variants as $variant): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-medium text-text-primary"><?= e($variant['name']) ?></div>
                                    <div class="text-text-muted truncate max-w-xs"><?= e($variant['description']) ?></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary hover:text-primary">
                                    <span class="inline-flex rounded bg-background px-2 py-0.5 text-xs font-semibold text-text-primary">
                                        <?= e(ucfirst($variant['unit'])) ?>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span class="<?= $variant['current_stock_cache'] <= $variant['minimum_stock'] ? 'text-error font-medium' : 'text-text-primary' ?>">
                                        <?= number_format($variant['current_stock_cache']) ?> <?= e($variant['unit']) ?>
                                    </span>
                                    <div class="text-xs text-text-muted mt-1">Min: <?= number_format($variant['minimum_stock']) ?></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <?= $variant['current_price'] ? '₱' . number_format($variant['current_price'], 2) : '<span class="text-text-muted text-xs">Not Set</span>' ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <?php if($variant['status'] === 'active'): ?>
                                        <span class="inline-flex rounded-full bg-status-successBg px-2 text-xs font-semibold leading-5 text-success">Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-status-cancelBg px-2 text-xs font-semibold leading-5 text-text-muted">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button onclick="editVariant(<?= htmlspecialchars(json_encode([
                                        'id' => $variant['id'],
                                        'name' => $variant['name'],
                                        'description' => $variant['description'],
                                        'minimum_stock' => $variant['minimum_stock'],
                                        'unit' => $variant['unit']
                                    ])) ?>)" class="inline-flex items-center text-primary hover:text-primary-hover mr-3">
                                        <i data-lucide="edit-3" class="h-4 w-4 mr-1"></i> Edit<span class="sr-only">, <?= e($variant['name']) ?></span>
                                    </button>
                                    
                                    <?php
                                        $toggleText = $variant['status'] === 'active' ? 'Deactivate' : 'Activate';
                                        $type = $variant['status'] === 'active' ? 'danger' : 'success';
                                        $msg = $variant['status'] === 'active' ? "Deactivating this variant will prevent new sales." : "Activating this variant will make it available for tracking and sales.";
                                        $iconName = $variant['status'] === 'active' ? 'power-off' : 'power';
                                    ?>
                                    <button onclick="showConfirmModal('<?= url('/rice-variants/'.$variant['id'].'/toggle') ?>', '<?= $toggleText ?> Variant', '<?= $msg ?>', '<?= $type ?>')" class="inline-flex items-center text-<?= $type === 'danger' ? 'error' : 'success' ?> hover:opacity-75">
                                        <i data-lucide="<?= $iconName ?>" class="h-4 w-4 mr-1"></i> <?= $toggleText ?><span class="sr-only">, <?= e($variant['name']) ?></span>
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

<!-- Add Modal -->
<div id="addModal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-text-primary bg-opacity-75 transition-opacity" onclick="closeModal('addModal')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-text-primary mb-4">Add Rice Variant</h3>
                    <form action="<?= url('/rice-variants') ?>" method="POST" id="addForm">
                        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-text-primary">Variant Name <span class="text-error">*</span></label>
                                <div class="mt-1">
                                    <input type="text" name="name" id="name" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                </div>
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-text-primary">Description</label>
                                <div class="mt-1">
                                    <textarea name="description" id="description" rows="2" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border"></textarea>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="unit" class="block text-sm font-medium text-text-primary">Kilos / Unit <span class="text-error">*</span></label>
                                    <div class="mt-1">
                                        <select name="unit" id="unit" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border bg-surface">
                                            <option value="sack">Sack</option>
                                            <option value="5 kg">5 kg</option>
                                            <option value="10 kg">10 kg</option>
                                            <option value="25 kg">25 kg</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="minimum_stock" class="block text-sm font-medium text-text-primary">Minimum Stock <span class="text-error">*</span></label>
                                    <div class="mt-1">
                                        <input type="number" step="0.01" min="0" value="0" name="minimum_stock" id="minimum_stock" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <label for="initial_price" class="block text-sm font-medium text-text-primary">Initial Price (₱)</label>
                                    <div class="mt-1">
                                        <input type="number" step="0.01" min="0" name="initial_price" id="initial_price" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                    </div>
                                    <p class="mt-1 text-xs text-text-muted">Can be set later via Pricing.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:col-start-2 sm:text-sm">Save</button>
                            <button type="button" onclick="closeModal('addModal')" class="mt-3 inline-flex w-full justify-center rounded-md border border-border bg-surface px-4 py-2 text-base font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-text-primary bg-opacity-75 transition-opacity" onclick="closeModal('editModal')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-text-primary mb-4">Edit Rice Variant</h3>
                    <form action="" method="POST" id="editForm">
                        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="edit_name" class="block text-sm font-medium text-text-primary">Variant Name <span class="text-error">*</span></label>
                                <div class="mt-1">
                                    <input type="text" name="name" id="edit_name" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                </div>
                            </div>
                            
                            <div>
                                <label for="edit_description" class="block text-sm font-medium text-text-primary">Description</label>
                                <div class="mt-1">
                                    <textarea name="description" id="edit_description" rows="2" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border"></textarea>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_unit" class="block text-sm font-medium text-text-primary">Kilos / Unit <span class="text-error">*</span></label>
                                    <div class="mt-1">
                                        <select name="unit" id="edit_unit" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border bg-surface">
                                            <option value="sack">Sack</option>
                                            <option value="5 kg">5 kg</option>
                                            <option value="10 kg">10 kg</option>
                                            <option value="25 kg">25 kg</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="edit_minimum_stock" class="block text-sm font-medium text-text-primary">Minimum Stock <span class="text-error">*</span></label>
                                    <div class="mt-1">
                                        <input type="number" step="0.01" min="0" name="minimum_stock" id="edit_minimum_stock" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:col-start-2 sm:text-sm">Save Changes</button>
                            <button type="button" onclick="closeModal('editModal')" class="mt-3 inline-flex w-full justify-center rounded-md border border-border bg-surface px-4 py-2 text-base font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    
    function editVariant(data) {
        let form = document.getElementById('editForm');
        form.action = "<?= url('/rice-variants/') ?>" + data.id;
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_description').value = data.description;
        document.getElementById('edit_minimum_stock').value = data.minimum_stock;
        document.getElementById('edit_unit').value = data.unit;
        openModal('editModal');
    }
</script>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
