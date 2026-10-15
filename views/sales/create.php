<?php 
$title = "Point of Sale (POS)";
ob_start(); 
?>

<!-- Pass PHP variants data to JS for dynamic pricing -->
<script>
    const inventoryVariants = <?= json_encode(array_values($variants)) ?>;
</script>

<div class="sm:flex sm:items-center justify-end mb-6">
    <a href="<?= url('/sales') ?>" class="inline-flex items-center text-sm font-medium text-text-muted hover:text-text-primary transition-colors">
        <i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i> Back to Sales
    </a>
</div>

<div class="lg:grid lg:grid-cols-12 lg:gap-8">
    <!-- Left Column: Items Selection -->
    <div class="lg:col-span-8">
        <div class="bg-surface shadow rounded-lg border border-border">
            <div class="px-4 py-5 sm:p-6">
                <form action="<?= url('/sales') ?>" method="POST" id="posForm">
                    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium leading-6 text-text-primary">Cart Items</h3>
                        <button type="button" onclick="addPosRow()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary bg-primary bg-opacity-10 hover:bg-opacity-20 focus:outline-none">
                            <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Add Item
                        </button>
                    </div>

                    <div id="posItemsContainer" class="space-y-4">
                        <!-- Initial Row -->
                        <div class="flex flex-wrap items-end gap-3 pos-row p-3 border border-border rounded-md bg-background">
                            <div class="flex-1 min-w-[240px] md:min-w-[300px]">
                                <label class="block text-xs font-medium text-text-muted mb-1">Variant <span class="text-error">*</span></label>
                                <select name="variants[]" onchange="updateRowData(this)" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border variant-select">
                                    <option value="">Select Variant...</option>
                                    <?php foreach($variants as $v): ?>
                                        <option value="<?= $v['id'] ?>" data-price="<?= $v['price'] ?>" data-stock="<?= $v['stock'] ?>" data-discountable="<?= (int)($v['allow_wholesale_discount'] ?? 1) ?>">
                                            <?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?> — ₱<?= number_format((float)$v['price']) ?> (<?= number_format((float)$v['stock']) ?> left)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                             <!-- Qty Ordered -->
                            <div class="w-24">
                                <label class="block text-xs font-medium text-text-muted mb-1">Quantity <span class="text-error">*</span></label>
                                <input type="number" step="1" min="1" name="quantities[]" oninput="onQtyChange(this)" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border row-qty">
                            </div>
                            <!-- NEW TBT TOGGLE -->
                            <div class="w-36">
                                <label class="block text-xs font-medium text-text-muted mb-1">Type</label>
                                <select name="remarks[]" onchange="toggleQtyTaken(this); calculatePosTotals()" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border bg-surface">
                                    <option value="out">Release Now</option>
                                    <option value="tbt">To Be Taken (TBT)</option>
                                    <option value="utang">Utang (Credit)</option>
                                </select>
                            </div>
                            <!-- END NEW TBT TOGGLE -->
                            <!-- Qty Taken Now (shown only for Utang rows) -->
                            <div class="w-24 qty-taken-wrapper hidden">
                                <label class="block text-xs font-medium text-yellow-600 mb-1">Taken Now</label>
                                <input type="number" step="1" min="0" name="quantities_taken[]" oninput="onTakenChange(this); calculatePosTotals()" placeholder="0" class="block w-full rounded-md border-yellow-400 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 sm:text-sm px-3 py-2 border bg-yellow-50 row-qty-taken">
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-medium text-text-muted mb-1">Unit Price (₱)</label>
                                <input type="number" step="0.01" min="0" name="prices[]" readonly class="block w-full rounded-md border-border bg-background shadow-sm sm:text-sm px-3 py-2 border text-text-muted cursor-not-allowed row-price">
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-medium text-text-muted mb-1">Subtotal (₱)</label>
                                <input type="text" readonly class="block w-full font-semibold rounded-md border-transparent bg-transparent sm:text-sm py-2 text-right text-text-primary row-subtotal" value="0.00">
                            </div>
                            <div class="w-10 flex pb-2 items-center justify-center">
                                <!-- empty placeholder -->
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-border pt-4"
                         id="afterHoursBanner" style="display:none">
                        <div class="rounded-md bg-amber-50 border border-amber-300 px-4 py-3 text-sm text-amber-800">
                            <i data-lucide="alert-triangle" class="inline h-4 w-4 mr-1"></i>
                            <strong>After-hours sale.</strong> This transaction will be recorded under the selected date.
                            Please enter notes below for accountability.
                        </div>
                    </div>

                    <div class="mt-4 border-t border-border pt-4">
                        <label for="notes" class="block text-sm font-medium text-text-primary">Sale Notes / Remarks</label>
                        <div class="mt-1">
                            <textarea id="notes" name="notes" rows="2" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border"></textarea>
                        </div>
                    </div>

                    <!-- Hidden inputs handled by sidebar submit -->
                    <input type="hidden" name="customer_name" id="hidden_customer" value="Walk-in Customer">
                    <input type="hidden" name="payment_method" id="hidden_payment_method" value="cash">
                    <input type="hidden" name="cheque_number" id="hidden_cheque_number" value="">
                    <input type="hidden" name="cheque_date" id="hidden_cheque_date" value="">
                    <input type="hidden" name="amount_tendered" id="hidden_tendered" value="0">
                    <input type="hidden" name="discount" id="hidden_discount" value="0">
                    <input type="hidden" name="sale_date" id="hidden_sale_date" value="<?= date('Y-m-d') ?>">
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Checkout Summary -->
    <div class="lg:col-span-4 mt-8 lg:mt-0">
        <div class="bg-surface shadow rounded-lg border border-border sticky top-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-text-primary border-b border-border pb-4 mb-4">Checkout Summary</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="display_customer" class="block text-sm font-medium text-text-primary">Customer Name</label>
                        <input type="text" id="display_customer" value="Walk-in Customer" oninput="document.getElementById('hidden_customer').value = this.value; calculatePosTotals();" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>

                    <div>
                        <label for="display_discount" class="block text-sm font-medium text-text-primary">Manual Discount (₱)</label>
                        <input type="number" step="0.01" min="0" id="display_discount" oninput="updateDiscount(this.value)" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border" placeholder="e.g. 50">
                    </div>

                    <div id="posDiscountRow" class="flex justify-between items-center py-2 hidden">
                        <span class="text-sm font-medium text-error">Discount Applied</span>
                        <span class="text-sm font-bold text-error" id="posTotalDiscount">-₱0.00</span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-t border-border my-2">
                        <span class="text-base font-medium text-text-primary">Total Amount Due</span>
                        <span class="text-2xl font-bold text-primary" id="posGrandTotal">₱0.00</span>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="border-t border-border pt-3">
                        <label class="block text-sm font-medium text-text-primary mb-2">Payment Method</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="display_payment_method" value="cash" checked onchange="togglePaymentMethod()" class="form-radio text-primary focus:ring-primary h-4 w-4">
                                <span class="ml-2 text-sm text-text-secondary">Cash</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="display_payment_method" value="cheque" onchange="togglePaymentMethod()" class="form-radio text-primary focus:ring-primary h-4 w-4">
                                <span class="ml-2 text-sm text-text-secondary">Cheque (Credit)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Cheque Details (Hidden by default) -->
                    <div id="chequeDetailsBlock" class="hidden space-y-3 p-3 bg-gray-50 border border-gray-200 rounded-md mt-2">
                        <div>
                            <label for="display_cheque_number" class="block text-xs font-medium text-text-primary mb-1">Cheque Number <span class="text-error">*</span></label>
                            <input type="text" id="display_cheque_number" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-1.5 border" placeholder="Enter cheque #">
                        </div>
                        <div>
                            <label for="display_cheque_date" class="block text-xs font-medium text-text-primary mb-1">Expected Clearance Date <span class="text-error">*</span></label>
                            <input type="date" id="display_cheque_date" min="<?= date('Y-m-d') ?>" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-1.5 border">
                        </div>
                        <p class="text-[10px] text-text-muted italic"><i data-lucide="info" class="inline h-3 w-3 mr-0.5"></i> Cheque payments are treated as fully paid cash-equivalents.</p>
                    </div>

                    <div id="tenderedBlock" class="mt-2">
                        <label for="display_tendered" class="block text-sm font-medium text-text-primary">Amount Tendered (₱)</label>
                        <input type="number" step="0.01" min="0" id="display_tendered" oninput="updateTendered(this.value)" onwheel="this.blur()" onkeydown="if(event.key==='ArrowUp' || event.key==='ArrowDown') event.preventDefault();" class="mt-1 block w-full text-lg font-semibold rounded-md border-border shadow-sm focus:border-primary focus:ring-primary px-3 py-2 border [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    </div>

                    <!-- Sale Date: for after-hours / backdated sales -->
                    <div class="mt-2">
                        <label for="display_sale_date" class="block text-sm font-medium text-text-primary">
                            Sale Date
                            <span class="ml-1 text-xs text-text-muted font-normal">(change for after-hours sales)</span>
                        </label>
                        <input type="date" id="display_sale_date"
                               value="<?= date('Y-m-d') ?>"
                               max="<?= date('Y-m-d') ?>"
                               oninput="onSaleDateChange(this.value)"
                               class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                    </div>

                    <div class="flex justify-between items-center pt-2 rounded p-3 bg-background">
                        <span class="text-sm font-medium text-text-muted">Change Due</span>
                        <span class="text-lg font-bold text-text-primary" id="posChange">₱0.00</span>
                    </div>

                    <div class="pt-6">
                        <button type="button" onclick="submitPOS()" id="checkoutBtn" disabled class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Complete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="posRowTemplate">
    <div class="flex flex-wrap items-end gap-3 pos-row p-3 border border-border rounded-md bg-background mt-3 shadow-sm">
        <div class="flex-1 min-w-[240px] md:min-w-[300px]">
            <select name="variants[]" onchange="updateRowData(this)" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border variant-select">
                <option value="">Select Variant...</option>
                <?php foreach($variants as $v): ?>
                    <option value="<?= $v['id'] ?>" data-price="<?= $v['price'] ?>" data-stock="<?= $v['stock'] ?>" data-discountable="<?= (int)($v['allow_wholesale_discount'] ?? 1) ?>">
                        <?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?> — ₱<?= number_format((float)$v['price']) ?> (<?= number_format((float)$v['stock']) ?> left)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-24">
            <input type="number" step="1" min="1" name="quantities[]" oninput="onQtyChange(this)" required class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border row-qty">
        </div>
        <div class="w-36">
            <select name="remarks[]" onchange="toggleQtyTaken(this); calculatePosTotals()" class="block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border bg-surface">
                <option value="out">Release Now</option>
                <option value="tbt">To Be Taken (TBT)</option>
                <option value="utang">Utang (Credit)</option>
            </select>
        </div>
        <!-- Qty Taken Now (shown only for Utang rows) -->
        <div class="w-24 qty-taken-wrapper hidden">
            <label class="block text-xs font-medium text-yellow-600 mb-1">Taken Now</label>
            <input type="number" step="1" min="0" name="quantities_taken[]" oninput="onTakenChange(this); calculatePosTotals()" placeholder="0" class="block w-full rounded-md border-yellow-400 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 sm:text-sm px-3 py-2 border bg-yellow-50 row-qty-taken">
        </div>
        <div class="w-32">
            <input type="number" step="0.01" min="0" name="prices[]" readonly class="block w-full rounded-md border-border bg-background shadow-sm sm:text-sm px-3 py-2 border text-text-muted cursor-not-allowed row-price">
        </div>
        <div class="w-32">
            <input type="text" readonly class="block w-full font-semibold rounded-md border-transparent bg-transparent sm:text-sm py-2 text-right text-text-primary row-subtotal" value="0.00">
        </div>
        <div class="w-10 flex pb-2 items-center justify-center">
            <button type="button" onclick="removePosRow(this)" class="text-error hover:opacity-75">
                <i data-lucide="trash-2" class="h-5 w-5"></i>
            </button>
        </div>
    </div>
</template>

<script>
    let currentTotal = 0;

    function addPosRow() {
        const container = document.getElementById('posItemsContainer');
        const template = document.getElementById('posRowTemplate');
        const clone = template.content.cloneNode(true);
        
        // Inherit the remark type from the first row to prevent conflict
        const firstRowSelect = document.querySelector('.pos-row select[name="remarks[]"]');
        const cloneSelect = clone.querySelector('select[name="remarks[]"]');
        if (firstRowSelect && cloneSelect) {
            cloneSelect.value = firstRowSelect.value;
            if (firstRowSelect.value === 'utang' || firstRowSelect.value === 'tbt') {
                const wrapper = clone.querySelector('.qty-taken-wrapper');
                if (wrapper) wrapper.classList.remove('hidden');
            }
        }
        
        container.appendChild(clone);
        if (window.lucide) lucide.createIcons();
    }

    function onQtyChange(input) {
        // Auto-set qty-taken max to match ordered qty for Utang rows
        const row = input.closest('.pos-row');
        const taken = row.querySelector('.row-qty-taken');
        if (taken) {
            taken.max = input.value || 0;
            // Also trigger check just in case they lower the master quantity below Taken Now
            if (parseFloat(taken.value) > parseFloat(input.value)) {
                taken.value = input.value;
            }
        }
        calculatePosTotals();
    }

    function onTakenChange(input) {
        const row = input.closest('.pos-row');
        const masterQty = parseFloat(row.querySelector('.row-qty').value) || 0;
        const takenVal = parseFloat(input.value) || 0;
        
        if (takenVal > masterQty) {
            input.value = masterQty;
            showAlertModal("Invalid Amount", "You cannot take more sacks (" + takenVal + ") than the total quantity purchased (" + masterQty + ").", "error");
        }
    }

    function toggleQtyTaken(selectEl) {
        const row = selectEl.closest('.pos-row');
        const currentVal = selectEl.value;
        const allSelects = document.querySelectorAll('.pos-row select[name="remarks[]"]');
        
        let hasUtang = false;
        let hasCash = false; // 'out' or 'tbt'
        
        allSelects.forEach(s => {
            const sRow = s.closest('.pos-row');
            // Only evaluate if row has a variant selected, or if it's the exact dropdown we just changed
            if (s === selectEl || (sRow && sRow.querySelector('.variant-select').value)) {
                if (s.value === 'utang') {
                    hasUtang = true;
                } else if (s.value === 'out' || s.value === 'tbt') {
                    hasCash = true;
                }
            }
        });
        
        if (hasUtang && hasCash) {
            showAlertModal(
                "Transaction Conflict",
                "Utang (Credit) items cannot be mixed with Cash (Release Now / TBT) items in the same transaction.\n\nThey generate different receipts. Please process them separately.",
                "error"
            );
            // Revert the one that was just changed
            selectEl.value = (currentVal === 'utang') ? 'out' : 'utang';
        }
        
        const finalVal = selectEl.value;
        const wrapper = row.querySelector('.qty-taken-wrapper');
        if (!wrapper) return;
        if (finalVal === 'utang' || finalVal === 'tbt') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
            const taken = wrapper.querySelector('.row-qty-taken');
            if (taken) taken.value = '';
        }
    }

    function onSaleDateChange(val) {
        document.getElementById('hidden_sale_date').value = val;
        const today = new Date().toISOString().slice(0, 10);
        const banner = document.getElementById('afterHoursBanner');
        if (banner) banner.style.display = (val && val < today) ? 'block' : 'none';
    }

    function removePosRow(btn) {
        btn.closest('.pos-row').remove();
        calculatePosTotals();
    }

    function updateRowData(selectElement) {
        const row = selectElement.closest('.pos-row');
        const priceInput = row.querySelector('.row-price');
        const qtyInput = row.querySelector('.row-qty');
        
        const option = selectElement.options[selectElement.selectedIndex];
        if (option.value) {
            const price = parseFloat(option.getAttribute('data-price')) || 0;
            const maxStock = parseFloat(option.getAttribute('data-stock')) || 0;
            
            priceInput.value = price.toFixed(2);
            qtyInput.max = maxStock;
            
            if (qtyInput.value) {
                if (parseFloat(qtyInput.value) > maxStock) {
                    qtyInput.value = maxStock;
                    // Provide a distinct modal or browser alert as requested by user
                    showAlertModal("Insufficient Stock", "Available stock for " + (option.textContent.split('—')[0].trim() || 'this item') + " is only " + maxStock + ".", 'error');
                    showToast('Reduced quantity to available stock limit.', 'warning');
                }
            } else {
                qtyInput.value = 1;
            }
        } else {
            priceInput.value = '';
            qtyInput.value = '';
            qtyInput.removeAttribute('max');
        }
        
        calculatePosTotals();
    }

    function calculatePosTotals() {
        const rows = document.querySelectorAll('.pos-row');
        let grandTotal = 0;
        let hasValidItems = false;
        let hasStockErrors = false;
        let hasUtang = false;

        // Base evaluation
        const method = document.querySelector('input[name="display_payment_method"]:checked')?.value;

        // Pass 1: Aggregate total quantities per variant and check if credit/utang
        const variantTotalQuantities = {};
        rows.forEach(row => {
            const select = row.querySelector('.variant-select');
            const qty = parseFloat(row.querySelector('.row-qty').value) || 0;
            const remarkSelect = row.querySelector('select[name="remarks[]"]');
            if (remarkSelect && remarkSelect.value === 'utang') {
                hasUtang = true;
            }
            if (select.value && qty > 0) {
                const vId = select.value;
                variantTotalQuantities[vId] = (variantTotalQuantities[vId] || 0) + qty;
            }
        });

        let totalWholesaleDiscount = 0;
        // Pass 2: Calculate sub-totals mapping the aggregated quantities for wholesale
        rows.forEach(row => {
            const select = row.querySelector('.variant-select');
            let qty = parseFloat(row.querySelector('.row-qty').value) || 0;
            const priceInput = row.querySelector('.row-price');
            const remark = row.querySelector('select[name="remarks[]"]').value;
            const subtotalInput = row.querySelector('.row-subtotal');
            
            if (remark === 'utang') {
                hasUtang = true;
            }

            if (select.value && qty > 0) {
                const vId = select.value;
                const aggregatedQty = variantTotalQuantities[vId] || 0;
                let rawBasePrice = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 0;
                
                const maxStock = parseFloat(select.options[select.selectedIndex].getAttribute('data-stock'));
                const itemName = select.options[select.selectedIndex].text.split('—')[0].trim();
                
                if (qty > maxStock) {
                    row.querySelector('.row-qty').value = maxStock;
                    row.querySelector('.row-qty').classList.add('border-error');
                    showAlertModal("Insufficient Stock", "Available stock for " + itemName + " is only " + maxStock + ".", 'error');
                    qty = maxStock; // Clamp it for calculation
                } else {
                    row.querySelector('.row-qty').classList.remove('border-error');
                }

                const discountable = select.options[select.selectedIndex]?.getAttribute('data-discountable') !== '0';
                // Wholesale discount ONLY applies to regular/cash sales, NEVER on Utang (Credit) purchases
                if (!hasUtang && remark !== 'utang' && discountable && aggregatedQty >= 10) {
                    totalWholesaleDiscount += (10 * qty);
                }

                // Visually keep the UI Input referencing the explicit Standard Base Price
                priceInput.value = rawBasePrice.toFixed(2);

                const subtotal = qty * rawBasePrice;
                subtotalInput.value = subtotal.toFixed(2);
                grandTotal += subtotal;
                hasValidItems = true;
            } else {
                subtotalInput.value = '0.00';
            }
        });
        
        const manualDiscount = parseFloat(document.getElementById('hidden_discount').value) || 0;
        const totalDiscount = totalWholesaleDiscount + manualDiscount;
        
        // Show visually if wholesale or manual discount triggered
        if (totalDiscount > 0) {
            document.getElementById('posDiscountRow').classList.remove('hidden');
            document.getElementById('posTotalDiscount').textContent = '-₱' + totalDiscount.toFixed(2);
        } else {
            document.getElementById('posDiscountRow').classList.add('hidden');
        }

        grandTotal = Math.max(0, grandTotal - totalDiscount);

        currentTotal = grandTotal;
        document.getElementById('posGrandTotal').textContent = '₱' + grandTotal.toFixed(2);
        
        // Re-evaluate change and button state
        const tendered = parseFloat(document.getElementById('display_tendered').value) || 0;
        updateChangeDisplay(tendered, grandTotal, hasUtang);
        
        const customerName = document.getElementById('hidden_customer').value.trim();
        const hasValidCustomer = hasUtang ? (customerName !== '' && customerName.toLowerCase() !== 'walk-in customer') : true;

        const btn = document.getElementById('checkoutBtn');
        const hasValidPayment = hasUtang ? true : (tendered >= grandTotal);
        
        if (hasValidItems && !hasStockErrors && hasValidPayment && hasValidCustomer && grandTotal > 0) {
            btn.removeAttribute('disabled');
        } else {
            btn.setAttribute('disabled', 'true');
        }
    }

    function updateTendered(val) {
        document.getElementById('hidden_tendered').value = val;
        calculatePosTotals();
    }
    
    function updateDiscount(val) {
        document.getElementById('hidden_discount').value = val;
        calculatePosTotals();
    }

    function updateChangeDisplay(tendered, total, hasUtang) {
        const changeSpan = document.getElementById('posChange');
        if (tendered >= total && total > 0) {
            const change = tendered - total;
            changeSpan.textContent = '₱' + change.toFixed(2);
            changeSpan.classList.add('text-success');
            changeSpan.classList.remove('text-text-primary');
            changeSpan.classList.remove('text-error');
        } else if (hasUtang && tendered < total && total > 0) {
            const balance = total - tendered;
            changeSpan.textContent = 'Balance ₱' + balance.toFixed(2);
            changeSpan.classList.add('text-error');
            changeSpan.classList.remove('text-text-primary');
            changeSpan.classList.remove('text-success');
        } else {
            changeSpan.textContent = '₱0.00';
            changeSpan.classList.remove('text-success');
            changeSpan.classList.remove('text-error');
            changeSpan.classList.add('text-text-primary');
        }
    }

    function togglePaymentMethod() {
        const method = document.querySelector('input[name="display_payment_method"]:checked').value;
        const chequeBlock = document.getElementById('chequeDetailsBlock');
        const tenderedBlock = document.getElementById('tenderedBlock');
        const changeRow = document.getElementById('posChange').parentNode;
        
        if (method === 'cheque') {
            chequeBlock.classList.remove('hidden');
            tenderedBlock.classList.remove('hidden');
            changeRow.classList.remove('hidden');
        } else {
            chequeBlock.classList.add('hidden');
            tenderedBlock.classList.remove('hidden');
            changeRow.classList.remove('hidden');
        }
        calculatePosTotals();
    }

    function submitPOS() {
        const displayCust = document.getElementById('display_customer');
        const displayTend = document.getElementById('display_tendered');
        const displayDisc = document.getElementById('display_discount');
        const displayDate = document.getElementById('display_sale_date');
        const method = document.querySelector('input[name="display_payment_method"]:checked').value;
        const displayChqNo = document.getElementById('display_cheque_number');
        const displayChqDate = document.getElementById('display_cheque_date');

        if (method === 'cheque') {
            if (!displayChqNo.value.trim() || !displayChqDate.value) {
                showAlertModal("Missing Details", "Please complete all Cheque details before completing the sale.", "error");
                return;
            }
        }

        if (displayCust) document.getElementById('hidden_customer').value = displayCust.value;
        document.getElementById('hidden_payment_method').value = method;
        document.getElementById('hidden_cheque_number').value = displayChqNo.value;
        document.getElementById('hidden_cheque_date').value = displayChqDate.value;
        
        if (displayTend) document.getElementById('hidden_tendered').value = displayTend.value;
        if (displayDisc) document.getElementById('hidden_discount').value = displayDisc.value || '0';
        if (displayDate) document.getElementById('hidden_sale_date').value = displayDate.value;
        document.getElementById('posForm').submit();
    }
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
