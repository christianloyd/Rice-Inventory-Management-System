<?php
$title = 'New Delivery';
ob_start();
$csrfField = App\Middleware\CsrfMiddleware::csrfField();
?>
<div class="mb-6">
    <p class="text-xs font-semibold uppercase tracking-widest text-text-muted">DELIVERIES</p>
    <h1 class="text-3xl font-bold text-text-primary mt-1">New Delivery</h1>
    <p class="text-sm text-text-muted mt-1">Record a delivery to a customer as cash or utang. A matching receipt prints automatically.</p>
</div>

<?php if (!empty($_SESSION['error'])): ?>
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
    <?= e($_SESSION['error']) ?><?php unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('/deliveries') ?>" id="deliveryForm">
    <?= $csrfField ?>
    <input type="hidden" name="payment_type" id="paymentTypeInput" value="cash">

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Left: Items -->
        <div class="flex-1 space-y-4">
            <!-- Customer + Date -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">Customer name</label>
                    <input type="text" name="customer_name" placeholder="e.g. Aling Nena Store"
                           class="w-full border border-border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">Delivery date</label>
                    <input type="date" name="delivery_date" value="<?= date('Y-m-d') ?>"
                           class="w-full border border-border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface" required>
                </div>
            </div>

            <!-- Line Items Header -->
            <div class="flex items-center gap-2 px-4 pb-1 text-xs font-semibold text-text-muted uppercase tracking-wider">
                <div class="flex-1">Item Details</div>
                <div class="w-24">Quantity</div>
                <div class="w-24">Disc/Sk</div>
                <div class="w-28 text-right">Subtotal</div>
                <div class="w-6"></div> <!-- Space for remove button in extra rows -->
            </div>
            
            <div id="itemsContainer" class="space-y-2">
                <div class="item-row flex items-center gap-2 bg-surface border border-border rounded-lg p-3">
                    <select name="rice_variant_id[]" class="variant-select flex-1 border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface" onchange="updatePrice(this)">
                        <option value="">Choose rice variety</option>
                        <?php foreach ($variants as $v): ?>
                        <option value="<?= $v['id'] ?>"
                                data-price="<?= $v['unit_price'] ?>"
                                data-stock="<?= $v['stock'] ?>"
                                data-name="<?= e($v['name']) ?>">
                            <?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?> – ₱<?= number_format($v['unit_price'], 2) ?> · <?= number_format($v['stock'], 0) ?> left
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="unit_price[]" class="unit-price-hidden" value="0">
                    <input type="number" name="quantity[]" placeholder="Qty" min="1" step="1"
                           class="qty-input w-24 border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface" oninput="this.value = this.value.replace(/[^0-9]/g, ''); recalc()">
                    <input type="number" name="discount_per_sack[]" placeholder="Disc/Sk" min="0" step="0.01" value="0"
                           class="discount-input w-24 border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface text-red-600 font-semibold" oninput="recalc()">
                    <span class="subtotal-display w-28 text-right text-sm font-semibold text-text-primary">₱0.00</span>
                    <button type="button" class="remove-row text-text-muted hover:text-red-500 transition-colors p-1 rounded invisible w-6 flex justify-center">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>

            <button type="button" id="addItemBtn"
                    class="w-full border-2 border-dashed border-border rounded-lg py-3 text-sm text-text-muted hover:border-primary hover:text-primary transition-colors">
                <i data-lucide="plus" class="inline h-4 w-4 mr-1"></i> Add another item
            </button>

            <div>
                <label class="block text-sm font-medium text-text-primary mb-1">Notes (optional)</label>
                <textarea name="notes" rows="2" placeholder="Delivery notes..."
                          class="w-full border border-border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface resize-none"></textarea>
            </div>
        </div>

        <!-- Right: Payment panel -->
        <div class="lg:w-72 shrink-0">
            <div class="bg-surface border border-border rounded-xl p-5 sticky top-6">
                <p class="text-xs font-bold uppercase tracking-widest text-text-muted mb-3">Payment Type</p>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <button type="button" id="btnCash"
                            class="payment-btn py-2.5 rounded-lg text-sm font-semibold border-2 border-primary bg-primary text-white transition-all"
                            onclick="setPaymentType('cash')">Cash</button>
                    <button type="button" id="btnUtang"
                            class="payment-btn py-2.5 rounded-lg text-sm font-semibold border-2 border-border text-text-primary bg-surface hover:border-primary transition-all"
                            onclick="setPaymentType('utang')">Utang (Debt)</button>
                    <button type="button" id="btnOwner"
                            class="payment-btn col-span-2 py-2.5 rounded-lg text-sm font-semibold border-2 border-border text-text-primary bg-surface hover:border-primary transition-all"
                            onclick="setPaymentType('owner')">Branch / Owner Transfer</button>
                </div>
                <p id="utangNote" class="text-xs text-red-600 mb-4 hidden">
                    This delivery will be recorded as unpaid debt in the Utang Monitor.
                </p>
                <p id="ownerNote" class="text-xs text-amber-600 mb-4 hidden">
                    Internal transfer. This will NOT be recorded as revenue or a sale.
                </p>

                <div class="border-t border-border pt-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-text-muted mb-1">Total Due</p>
                    <p class="text-3xl font-bold text-text-primary" id="totalDisplay">₱0.00</p>
                    <p class="text-xs font-semibold text-red-600 mt-1 hidden" id="totalDiscountDisplay">Saved: ₱0.00</p>
                </div>

                <div id="cashTenderedGroup" class="mt-4 pt-4 border-t border-border">
                    <label class="block text-xs font-bold uppercase tracking-widest text-text-muted mb-2">Amount Tendered</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-text-muted">₱</span>
                        <input type="number" name="amount_tendered" id="amountTenderedInput" min="0" step="0.01" placeholder="0.00"
                               class="w-full bg-surface border-2 border-border focus:border-primary rounded-xl py-3 pl-9 pr-4 font-bold text-xl text-text-primary outline-none transition-colors"
                               oninput="recalcChange()">
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <span class="text-sm font-semibold text-text-muted">Change</span>
                        <span class="text-xl font-bold text-primary" id="changeDisplay">₱0.00</span>
                    </div>
                </div>

                <button type="submit" id="submitBtn"
                        class="mt-5 w-full flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold
                               bg-muted text-text-muted cursor-not-allowed transition-all"
                        disabled>
                    <i data-lucide="printer" class="h-4 w-4"></i> Save &amp; print receipt
                </button>
            </div>
        </div>
    </div>
</form>

<template id="itemRowTemplate">
    <div class="item-row flex items-center gap-2 bg-surface border border-border rounded-lg p-3">
        <select name="rice_variant_id[]" class="variant-select flex-1 border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface" onchange="updatePrice(this)">
            <option value="">Choose rice variety</option>
            <?php foreach ($variants as $v): ?>
            <option value="<?= $v['id'] ?>"
                    data-price="<?= $v['unit_price'] ?>"
                    data-stock="<?= $v['stock'] ?>"
                    data-name="<?= e($v['name']) ?>">
                <?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?> – ₱<?= number_format($v['unit_price'], 2) ?> · <?= number_format($v['stock'], 0) ?> left
            </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="unit_price[]" class="unit-price-hidden" value="0">
        <input type="number" name="quantity[]" placeholder="Qty" min="1" step="1"
               class="qty-input w-24 border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface" oninput="this.value = this.value.replace(/[^0-9]/g, ''); recalc()">
        <input type="number" name="discount_per_sack[]" placeholder="Disc/Sk" min="0" step="0.01" value="0"
               class="discount-input w-24 border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface text-red-600 font-semibold" oninput="recalc()">
        <span class="subtotal-display w-28 text-right text-sm font-semibold text-text-primary">₱0.00</span>
        <button type="button" class="remove-row text-text-muted hover:text-red-500 transition-colors p-1 rounded w-6 flex justify-center">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>
</template>

<script>
function setPaymentType(type) {
    document.getElementById('paymentTypeInput').value = type;
    const btnCash  = document.getElementById('btnCash');
    const btnUtang = document.getElementById('btnUtang');
    const btnOwner = document.getElementById('btnOwner');
    const noteUtang = document.getElementById('utangNote');
    const noteOwner = document.getElementById('ownerNote');
    const customerInput = document.querySelector('input[name="customer_name"]');

    // Reset all buttons
    [btnCash, btnUtang, btnOwner].forEach(btn => {
        btn.classList.replace('border-primary', 'border-border');
        btn.classList.remove('bg-primary', 'bg-red-600', 'bg-amber-600', 'text-white');
        btn.classList.add('bg-surface', 'text-text-primary');
    });
    noteUtang.classList.add('hidden');
    noteOwner.classList.add('hidden');

    if (type === 'cash') {
        btnCash.classList.replace('border-border', 'border-primary');
        btnCash.classList.remove('bg-surface', 'text-text-primary');
        btnCash.classList.add('bg-primary', 'text-white');
        if (customerInput.value === 'Owner - Internal Transfer') customerInput.value = '';
        customerInput.readOnly = false;
        document.querySelectorAll('.discount-input, .subtotal-display').forEach(el => el.style.visibility = 'visible');
    } else if (type === 'utang') {
        btnUtang.classList.replace('border-border', 'border-primary');
        btnUtang.classList.remove('bg-surface', 'text-text-primary');
        btnUtang.classList.add('bg-red-600', 'text-white');
        noteUtang.classList.remove('hidden');
        if (customerInput.value === 'Owner - Internal Transfer') customerInput.value = '';
        customerInput.readOnly = false;
        document.querySelectorAll('.discount-input, .subtotal-display').forEach(el => el.style.visibility = 'visible');
    } else if (type === 'owner') {
        btnOwner.classList.replace('border-border', 'border-primary');
        btnOwner.classList.remove('bg-surface', 'text-text-primary');
        btnOwner.classList.add('bg-amber-600', 'text-white');
        noteOwner.classList.remove('hidden');
        customerInput.value = 'Owner - Internal Transfer';
        customerInput.readOnly = true;
        // Hide price blocks
        document.querySelectorAll('.discount-input, .subtotal-display').forEach(el => el.style.visibility = 'hidden');
    }
    recalcChange();
}

function updatePrice(select) {
    const opt   = select.options[select.selectedIndex];
    const row   = select.closest('.item-row');
    const ph    = row.querySelector('.unit-price-hidden');
    const price = parseFloat(opt.dataset.price || 0);
    ph.value = price;
    recalc();
}

function recalc() {
    let total = 0;
    let totalDiscount = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const select = row.querySelector('.variant-select');
        const price = parseFloat(row.querySelector('.unit-price-hidden')?.value || 0);
        let qty   = parseInt(row.querySelector('.qty-input')?.value || 0, 10);
        const disc  = parseFloat(row.querySelector('.discount-input')?.value   || 0);
        
        if (select && select.selectedIndex > 0) {
            const maxStock = parseFloat(select.options[select.selectedIndex].getAttribute('data-stock') || 0);
            const itemName = select.options[select.selectedIndex].text.split('–')[0].trim();
            if (qty > maxStock) {
                row.querySelector('.qty-input').value = maxStock;
                qty = maxStock;
                showAlertModal("Insufficient Stock", "Available stock for " + itemName + " is only " + maxStock + ".", 'error');
            }
        }
        
        const netPrice = Math.max(0, price - disc);
        const sub   = netPrice * qty;
        
        row.querySelector('.subtotal-display').textContent = '₱' + sub.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
        total += sub;
        totalDiscount += (disc * qty);
    });

    const isOwner = document.getElementById('paymentTypeInput').value === 'owner';
    
    if (isOwner) {
        document.getElementById('totalDisplay').textContent = '₱0.00';
    } else {
        document.getElementById('totalDisplay').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    const discDisplay = document.getElementById('totalDiscountDisplay');
    if (totalDiscount > 0 && !isOwner) {
        discDisplay.textContent = 'Discount Applied: ₱' + totalDiscount.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
        discDisplay.classList.remove('hidden');
    } else {
        discDisplay.classList.add('hidden');
    }

    const btn = document.getElementById('submitBtn');
    if (total > 0) {
        btn.disabled = false;
        btn.classList.remove('bg-muted','text-text-muted','cursor-not-allowed');
        btn.classList.add('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
    } else {
        btn.disabled = true;
        btn.classList.add('bg-muted','text-text-muted','cursor-not-allowed');
        btn.classList.remove('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
    }
    recalcChange();
}

function recalcChange() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const price = parseFloat(row.querySelector('.unit-price-hidden')?.value || 0);
        const qty   = parseInt(row.querySelector('.qty-input')?.value || 0, 10);
        const disc  = parseFloat(row.querySelector('.discount-input')?.value   || 0);
        const netPrice = Math.max(0, price - disc);
        total += (netPrice * qty);
    });

    const isUtang = document.getElementById('paymentTypeInput').value === 'utang';
    const isOwner = document.getElementById('paymentTypeInput').value === 'owner';
    const tendered = parseFloat(document.getElementById('amountTenderedInput').value || 0);
    const changeDisplay = document.getElementById('changeDisplay');
    const btn = document.getElementById('submitBtn');
    const cashGroup = document.getElementById('cashTenderedGroup');

    if (isOwner) {
        cashGroup.style.display = 'none';
        
        let hasItems = false;
        document.querySelectorAll('.item-row').forEach(row => {
            if (row.querySelector('.variant-select').selectedIndex > 0) hasItems = true;
        });

        if (hasItems) {
            btn.disabled = false;
            btn.classList.remove('bg-muted','text-text-muted','cursor-not-allowed');
            btn.classList.add('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
        } else {
            btn.disabled = true;
            btn.classList.add('bg-muted','text-text-muted','cursor-not-allowed');
            btn.classList.remove('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
        }
        return;
    }

    if (isUtang) {
        cashGroup.style.display = 'none';
        if (total > 0) {
            btn.disabled = false;
            btn.classList.remove('bg-muted','text-text-muted','cursor-not-allowed');
            btn.classList.add('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
        } else {
            btn.disabled = true;
            btn.classList.add('bg-muted','text-text-muted','cursor-not-allowed');
            btn.classList.remove('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
        }
        return; 
    }

    document.getElementById('cashTenderedGroup').style.display = 'block';

    const diff = tendered - total;
    if (diff < 0) {
        changeDisplay.textContent = '-₱' + Math.abs(diff).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
        changeDisplay.classList.replace('text-primary', 'text-red-500');
        btn.disabled = true;
        btn.classList.add('bg-muted','text-text-muted','cursor-not-allowed');
        btn.classList.remove('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
    } else {
        changeDisplay.textContent = '₱' + diff.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
        changeDisplay.classList.replace('text-red-500', 'text-primary');
        if (total > 0) {
            btn.disabled = false;
            btn.classList.remove('bg-muted','text-text-muted','cursor-not-allowed');
            btn.classList.add('bg-primary','text-white','cursor-pointer','hover:bg-primary/90');
        }
    }
}

document.getElementById('addItemBtn').addEventListener('click', () => {
    const tpl  = document.getElementById('itemRowTemplate');
    const clone = tpl.content.cloneNode(true);
    document.getElementById('itemsContainer').appendChild(clone);
    updateRemoveButtons();
    if (window.lucide) lucide.createIcons();
});

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.item-row');
    rows.forEach((row, i) => {
        const btn = row.querySelector('.remove-row');
        if (!btn) return;
        btn.classList.toggle('hidden', rows.length === 1);
        btn.onclick = () => { row.remove(); recalc(); updateRemoveButtons(); };
    });
}

updateRemoveButtons();
</script>

<?php
$content = ob_get_clean();
require base_path('views/layouts/app.php');
