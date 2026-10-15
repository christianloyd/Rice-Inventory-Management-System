<?php 
$title = "Process New Stock Return";
ob_start(); 
$items = $items ?? [];
$selectedItemId = $selectedItemId ?? 0;
?>

<div class="sm:flex sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-text-primary">New Stock Return Transaction</h1>
        <p class="text-sm text-text-muted mt-1">Return excess, defective, or rejected sacks back to the supplier. Deducts active warehouse inventory (Stock OUT).</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3 sm:flex-none">
        <a href="<?= url('/stock-returns') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none">
            <i data-lucide="arrow-left" class="-ml-1 mr-2 h-4 w-4 text-text-muted"></i>
            Back to Returns
        </a>
    </div>
</div>

<div class="mt-6 max-w-3xl">
    <!-- Informational Banner -->
    <div class="mb-6 rounded-md bg-rose-50 p-4 border border-rose-200">
        <div class="flex">
            <div class="flex-shrink-0 text-error">
                <i data-lucide="corner-up-left" class="h-5 w-5"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-rose-800">Inventory Stock OUT Reversal</h3>
                <div class="mt-1 text-xs text-rose-700">
                    When you confirm this return, the specified quantity will be deducted from active warehouse stock, logged as an audited <code>STOCK_IN_REVERSAL</code> movement, and permanently tracked against the original supplier shipment.
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="rounded-lg border border-border bg-surface p-8 text-center shadow-sm">
            <i data-lucide="alert-circle" class="mx-auto h-10 w-10 text-warning"></i>
            <h3 class="mt-3 text-base font-semibold text-text-primary">No eligible shipment items available</h3>
            <p class="mt-1 text-sm text-text-muted">There are currently no active inbound shipments with remaining returnable stock in the warehouse.</p>
            <div class="mt-6">
                <a href="<?= url('/stock-ins') ?>" class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover">
                    View Stock-In History
                </a>
            </div>
        </div>
    <?php else: ?>
        <form action="<?= url('/stock-returns') ?>" method="POST" id="independentReturnForm" class="bg-surface shadow rounded-lg border border-border p-6 space-y-6">
            <?= \App\Middleware\CsrfMiddleware::csrfField() ?>

            <!-- Item Selector -->
            <div>
                <label for="itemSelect" class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">
                    Select Shipment Item to Return <span class="text-error">*</span>
                </label>
                <select name="stock_in_item_id" id="itemSelect" onchange="onItemSelectionChanged()" required
                        class="block w-full rounded-md border border-border bg-background px-3 py-2.5 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">-- Choose a shipment item --</option>
                    <?php foreach($items as $it): 
                        $rem = (float)$it['remaining_delivered_qty'];
                        $wh = (float)$it['current_stock_cache'];
                        $maxRet = min($rem, $wh);
                        $isSelected = ($selectedItemId > 0 && (int)$it['item_id'] === $selectedItemId);
                    ?>
                        <option value="<?= $it['item_id'] ?>" 
                                data-supplier="<?= htmlspecialchars($it['supplier_name'] ?? 'Supplier', ENT_QUOTES) ?>"
                                data-ref="<?= htmlspecialchars($it['stock_in_ref'] ?? '', ENT_QUOTES) ?>"
                                data-stock-in-id="<?= $it['stock_in_id'] ?>"
                                data-date="<?= $it['stock_in_date'] ?>"
                                data-variant="<?= htmlspecialchars($it['variant_name'], ENT_QUOTES) ?>"
                                data-unit="<?= htmlspecialchars($it['variant_unit'] ?? 'sack', ENT_QUOTES) ?>"
                                data-received="<?= $it['received_qty'] ?>"
                                data-already-returned="<?= $it['returned_qty'] ?>"
                                data-remaining="<?= $rem ?>"
                                data-wh-stock="<?= $wh ?>"
                                data-cost="<?= $it['cost_per_unit'] ?>"
                                data-max="<?= $maxRet ?>"
                                <?= $isSelected ? 'selected' : '' ?>>
                            [STI-<?= str_pad($it['stock_in_id'], 5, '0', STR_PAD_LEFT) ?>] <?= e($it['variant_name']) ?> (<?= e($it['variant_unit']) ?>) — Supplier: <?= e($it['supplier_name']) ?> | Max Returnable: <?= number_format($maxRet) ?> sacks
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dynamic Live Summary Card -->
            <div id="itemDetailCard" class="hidden bg-background rounded-md border border-border p-4">
                <h4 class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-3">Selected Shipment Item Details</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 text-sm">
                    <div>
                        <div class="text-xs text-text-muted">Supplier</div>
                        <div id="cardSupplier" class="font-semibold text-text-primary mt-0.5">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-text-muted">Shipment Reference</div>
                        <div id="cardRef" class="font-medium text-text-primary mt-0.5">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-text-muted">Shipment Date</div>
                        <div id="cardDate" class="font-medium text-text-primary mt-0.5">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-text-muted">Original Received</div>
                        <div id="cardReceived" class="font-medium text-text-primary mt-0.5">0 sacks</div>
                    </div>
                    <div>
                        <div class="text-xs text-text-muted">Remaining Delivered</div>
                        <div id="cardRemaining" class="font-medium text-text-primary mt-0.5">0 sacks</div>
                    </div>
                    <div>
                        <div class="text-xs text-text-muted">Active Warehouse Stock</div>
                        <div id="cardWhStock" class="font-medium text-text-primary mt-0.5">0 sacks</div>
                    </div>
                </div>
            </div>

            <!-- Return Quantity -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="returnQty" class="block text-xs font-semibold text-text-primary uppercase tracking-wider">
                        Number of Sacks to Return <span class="text-error">*</span>
                    </label>
                    <span id="maxLabel" class="text-xs font-semibold text-error"></span>
                </div>
                <div class="relative rounded-md shadow-sm">
                    <input type="number" step="1" min="1" name="return_quantity" id="returnQty" required
                           class="block w-full rounded-md border border-border bg-background px-3 py-2 text-base font-bold text-error focus:border-error focus:outline-none focus:ring-1 focus:ring-error"
                           placeholder="Enter quantity to return">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <span class="text-xs text-text-muted font-medium">sacks</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-text-muted">Must be between 1 and the maximum returnable sacks allowed.</p>
            </div>

            <!-- Return Reason -->
            <div>
                <label for="reasonSelect" class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">
                    Return Reason <span class="text-error">*</span>
                </label>
                <select name="reason" id="reasonSelect" onchange="onReasonSelectionChanged()" required
                        class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="Over-delivery / excess sacks received">Over-delivery / Excess sacks received</option>
                    <option value="Damaged / torn packaging">Damaged / Torn packaging</option>
                    <option value="Poor grain quality / rejected inspection">Poor grain quality / Rejected inspection</option>
                    <option value="Infestation / Moisture problem">Infestation / Moisture problem</option>
                    <option value="Wrong rice variety delivered">Wrong rice variety delivered</option>
                    <option value="other">Other / Custom Reason</option>
                </select>
            </div>

            <div id="customReasonBox" class="hidden">
                <label for="customReasonInput" class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">
                    Specify Custom Reason <span class="text-error">*</span>
                </label>
                <input type="text" name="custom_reason" id="customReasonInput"
                       class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                       placeholder="e.g. Returned per supplier request due to batch recall">
            </div>

            <!-- Return Date -->
            <div>
                <label for="returnDate" class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">
                    Return Date <span class="text-error">*</span>
                </label>
                <input type="date" name="return_date" id="returnDate" value="<?= date('Y-m-d') ?>" required
                       class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>

            <!-- Additional Notes -->
            <div>
                <label for="notes" class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">
                    Additional Notes (Optional)
                </label>
                <textarea name="notes" id="notes" rows="2"
                          class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                          placeholder="e.g. Handed to driver Juan dela Cruz, plate #ABC-123"></textarea>
            </div>

            <div class="pt-4 border-t border-border flex justify-end gap-3">
                <a href="<?= url('/stock-returns') ?>" class="rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary hover:bg-background">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-error px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2">
                    <i data-lucide="corner-up-left" class="-ml-1 mr-2 h-4 w-4"></i>
                    Confirm Return Transaction (Stock OUT)
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function onItemSelectionChanged() {
    const select = document.getElementById('itemSelect');
    const card = document.getElementById('itemDetailCard');
    const qtyInput = document.getElementById('returnQty');
    const maxLabel = document.getElementById('maxLabel');
    
    if (!select || select.selectedIndex <= 0) {
        if (card) card.classList.add('hidden');
        if (maxLabel) maxLabel.textContent = '';
        if (qtyInput) qtyInput.removeAttribute('max');
        return;
    }
    
    const opt = select.options[select.selectedIndex];
    const supplier = opt.getAttribute('data-supplier') || '—';
    const ref = opt.getAttribute('data-ref') || 'None';
    const stiId = opt.getAttribute('data-stock-in-id') || '';
    const date = opt.getAttribute('data-date') || '—';
    const received = opt.getAttribute('data-received') || '0';
    const remaining = opt.getAttribute('data-remaining') || '0';
    const whStock = opt.getAttribute('data-wh-stock') || '0';
    const maxVal = parseFloat(opt.getAttribute('data-max') || '0');
    
    document.getElementById('cardSupplier').textContent = supplier;
    document.getElementById('cardRef').textContent = `STI-${stiId.padStart(5, '0')} (${ref})`;
    document.getElementById('cardDate').textContent = date;
    document.getElementById('cardReceived').textContent = `${Number(received).toLocaleString()} sacks`;
    document.getElementById('cardRemaining').textContent = `${Number(remaining).toLocaleString()} sacks`;
    document.getElementById('cardWhStock').textContent = `${Number(whStock).toLocaleString()} sacks`;
    
    qtyInput.max = maxVal;
    maxLabel.textContent = `Max Allowed: ${maxVal} sacks`;
    card.classList.remove('hidden');
}

function onReasonSelectionChanged() {
    const reasonSelect = document.getElementById('reasonSelect');
    const box = document.getElementById('customReasonBox');
    const input = document.getElementById('customReasonInput');
    
    if (reasonSelect.value === 'other') {
        box.classList.remove('hidden');
        input.required = true;
        input.focus();
    } else {
        box.classList.add('hidden');
        input.required = false;
    }
}

// Initialize on page load if an item was pre-selected
document.addEventListener('DOMContentLoaded', function() {
    onItemSelectionChanged();
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
