<?php
$title = "New Transaction";
ob_start();
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-text-muted">Record a new transaction with single or multiple rice variant line items.</p>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="mt-4 rounded-md bg-red-50 p-4">
        <div class="flex">
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800"><?= e($_SESSION['error']) ?></h3>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="mt-4 rounded-md bg-green-50 p-4">
        <div class="flex">
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800"><?= e($_SESSION['success']) ?></h3>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<form action="<?= url('/transactions') ?>" method="POST" id="transactionForm" class="mt-6 space-y-6">
    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
    
    <div class="bg-surface shadow sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-text-primary">Transaction Header</h3>
            <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-3 sm:gap-x-4">
                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-text-primary">Date</label>
                    <div class="mt-1">
                        <input type="date" name="transaction_date" id="transaction_date" value="<?= date('Y-m-d') ?>" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-surface shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium leading-6 text-text-primary">Line Items</h3>
                <button type="button" id="addItemBtn" class="inline-flex items-center rounded border border-border bg-white px-2.5 py-1.5 text-xs font-medium text-text-secondary shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Add Item
                </button>
            </div>
            
            <div id="itemsContainer" class="space-y-4">
                <!-- Javascript will populate items here -->
            </div>
            
            <div class="mt-6 pt-6 border-t border-border flex justify-end">
                <div class="text-right">
                    <p class="text-sm font-medium text-text-muted">Total Amount</p>
                    <p class="text-2xl font-bold text-text-primary">₱<span id="grandTotal">0.00</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" id="submitBtn" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-primary py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
            Record Transaction
        </button>
    </div>
</form>

<template id="itemTemplate">
    <div class="item-row relative rounded-lg border border-border bg-gray-50 p-4 shadow-sm">
        <button type="button" class="remove-btn absolute top-2 right-2 text-text-muted hover:text-error" title="Remove Item">
            <i data-lucide="x" class="h-5 w-5"></i>
        </button>
        
        <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-6 sm:gap-x-4">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-text-primary">Receipt # *</label>
                <div class="mt-1">
                    <input type="text" name="items[{index}][receipt_no]" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-text-primary">Variant *</label>
                <div class="mt-1">
                    <select name="items[{index}][rice_variant_id]" required class="variant-select block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                        <option value="">Select Variant...</option>
                        <?php foreach($variants as $v): ?>
                            <option value="<?= $v['id'] ?>" data-price="<?= $v['current_price'] ?? '' ?>"><?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-text-primary">Qty *</label>
                <div class="mt-1">
                    <input type="number" min="1" step="1" name="items[{index}][quantity]" value="1" required class="qty-input block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                </div>
            </div>
            
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-text-primary">Unit Price ₱ *</label>
                <div class="mt-1">
                    <input type="number" min="0" step="0.01" name="items[{index}][unit_price]" required class="price-input block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border bg-gray-100">
                </div>
            </div>
            
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-text-primary">Subtotal</label>
                <div class="mt-1">
                    <p class="pt-2 text-sm font-medium text-text-primary subtotal-display">₱0.00</p>
                </div>
            </div>
            
            <div class="sm:col-span-3">
                <label class="block text-sm font-medium text-text-primary">Remarks & Status</label>
                <div class="mt-2 flex items-center space-x-6">
                    <div class="flex items-center">
                        <input type="radio" value="out" name="items[{index}][remarks]" id="rem_out_{index}" checked class="remark-radio h-4 w-4 border-border text-primary focus:ring-primary">
                        <label for="rem_out_{index}" class="ml-2 block text-sm text-text-primary">Out (Normal)</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" value="tbt" name="items[{index}][remarks]" id="rem_tbt_{index}" class="remark-radio h-4 w-4 border-border text-primary focus:ring-primary">
                        <label for="rem_tbt_{index}" class="ml-2 block text-sm text-text-primary font-bold text-error">TBT (To Be Taken)</label>
                    </div>
                </div>
            </div>
            
            <div class="sm:col-span-3">
                <label class="block text-sm font-medium text-text-primary">Fulfillment</label>
                <div class="mt-2 flex items-center space-x-6">
                    <div class="flex items-center">
                        <input type="radio" value="walkin" name="items[{index}][fulfillment_type]" id="ful_w_{index}" checked class="h-4 w-4 border-border text-primary focus:ring-primary">
                        <label for="ful_w_{index}" class="ml-2 block text-sm text-text-primary">Walk-in</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" value="delivered" name="items[{index}][fulfillment_type]" id="ful_d_{index}" class="h-4 w-4 border-border text-primary focus:ring-primary">
                        <label for="ful_d_{index}" class="ml-2 block text-sm text-text-primary">Delivered</label>
                    </div>
                </div>
            </div>
            
            <!-- Conditional TBT fields -->
            <div class="tbt-fields hidden sm:col-span-6 mt-4 p-4 bg-white border border-error rounded-md grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
                <div>
                    <label class="block text-sm font-medium text-text-primary">Customer Name (TBT) *</label>
                    <div class="mt-1">
                        <input type="text" name="items[{index}][customer_name]" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border tbt-req">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-primary">Contact Number</label>
                    <div class="mt-1">
                        <input type="text" name="items[{index}][contact_number]" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script src="<?= url('/assets/js/transaction-form.js') ?>?v=<?= time() ?>"></script>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
