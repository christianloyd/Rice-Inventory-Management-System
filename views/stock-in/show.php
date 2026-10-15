<?php 
$title = "Stock-In Details";
ob_start(); 
$returns = $returns ?? [];
$items = $items ?? [];
$hasReturnableItems = false;
foreach ($items as $it) {
    if (($it['net_quantity'] ?? $it['quantity']) > 0) {
        $hasReturnableItems = true;
        break;
    }
}
?>

<div class="sm:flex sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-text-primary">Stock-In #STI-<?= str_pad($stockIn['id'], 5, '0', STR_PAD_LEFT) ?></h1>
            <?php if($stockIn['status'] === 'active'): ?>
                <span class="inline-flex rounded-full bg-status-successBg px-3 py-1 text-xs font-semibold text-success">Completed</span>
            <?php else: ?>
                <span class="inline-flex rounded-full bg-status-cancelBg px-3 py-1 text-xs font-semibold text-error">Cancelled</span>
            <?php endif; ?>
        </div>
        <p class="text-sm text-text-muted mt-1">Viewing shipment details, received sacks, and supplier return transactions.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center space-x-3">
        <?php if($stockIn['status'] === 'active' && $hasReturnableItems): ?>
            <button type="button" onclick="openReturnModal()" class="inline-flex items-center justify-center rounded-md border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 shadow-sm hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2">
                <i data-lucide="corner-up-left" class="-ml-1 mr-2 h-4 w-4 text-error"></i>
                Return Sacks to Supplier
            </button>
        <?php endif; ?>

        <?php if($stockIn['status'] === 'active' && empty($returns)): ?>
            <button onclick="showConfirmModal('<?= url('/stock-ins/'.$stockIn['id'].'/cancel') ?>', 'Cancel Stock-In', 'This will revert the added inventory. Are you sure?', 'danger')" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-error shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2">
                <i data-lucide="x-circle" class="-ml-1 mr-2 h-4 w-4"></i>
                Cancel Record
            </button>
        <?php endif; ?>

        <a href="<?= url('/stock-ins') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
            <i data-lucide="arrow-left" class="-ml-1 mr-2 h-4 w-4 text-text-muted"></i>
            Back
        </a>
    </div>
</div>

<!-- Shipment Metadata Card -->
<div class="mt-6 bg-surface shadow overflow-hidden sm:rounded-lg border border-border">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center bg-background/50 border-b border-border">
        <div>
            <h3 class="text-lg leading-6 font-semibold text-text-primary">
                Supplier: <?= e($stockIn['supplier_name']) ?>
            </h3>
            <p class="mt-1 text-sm text-text-muted">
                Received by <span class="font-medium text-text-primary"><?= e($stockIn['received_by_name']) ?></span> on <?= date('M j, Y', strtotime($stockIn['stock_in_date'] ?? $stockIn['created_at'])) ?>
            </p>
        </div>
        <div class="text-right">
            <div class="text-xs text-text-muted">Total Invoice Cost</div>
            <div class="text-xl font-bold text-text-primary">₱<?= number_format($stockIn['total_cost'], 2) ?></div>
        </div>
    </div>
    <div class="px-4 py-5 sm:px-6">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Reference / Receipt No</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary"><?= $stockIn['reference_number'] ? e($stockIn['reference_number']) : '<span class="italic text-text-muted">None provided</span>' ?></dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Total Sacks Returned</dt>
                <dd class="mt-1 text-sm font-semibold <?= (!empty($stockIn['total_returned_qty']) && $stockIn['total_returned_qty'] > 0) ? 'text-error' : 'text-text-muted' ?>">
                    <?php if (!empty($stockIn['total_returned_qty']) && $stockIn['total_returned_qty'] > 0): ?>
                        -<?= number_format($stockIn['total_returned_qty']) ?> sacks
                    <?php else: ?>
                        0 sacks
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Shipment Status</dt>
                <dd class="mt-1 text-sm">
                    <?php if($stockIn['status'] === 'active'): ?>
                        <span class="inline-flex rounded-full bg-status-successBg px-2.5 py-0.5 text-xs font-semibold text-success">Active Shipment</span>
                    <?php else: ?>
                        <span class="inline-flex rounded-full bg-status-cancelBg px-2.5 py-0.5 text-xs font-semibold text-error">Cancelled</span>
                    <?php endif; ?>
                </dd>
            </div>
            <div class="sm:col-span-3">
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wider">Shipment Notes</dt>
                <dd class="mt-1 text-sm text-text-primary"><?= $stockIn['notes'] ? nl2br(e($stockIn['notes'])) : '<span class="italic text-text-muted">No notes</span>' ?></dd>
            </div>
        </dl>
    </div>
</div>

<!-- Items Received Table -->
<div class="mt-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg leading-6 font-semibold text-text-primary">Items Received & Net Sacks</h3>
            <p class="text-xs text-text-muted">Displays inbound quantity, returned sacks sent back to supplier, and net inventory retained.</p>
        </div>
    </div>
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-background">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Variant Name</th>
                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-text-primary">Packaging</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Received Qty</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Returned Qty</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Net Kept</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Unit Cost</th>
                    <th scope="col" class="py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-text-primary sm:pr-6">Net Cost</th>
                    <?php if($stockIn['status'] === 'active'): ?>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-center text-sm font-semibold text-text-primary">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-surface">
                <?php 
                $netTotalCost = 0;
                foreach($items as $item): 
                    $origQty = (float)$item['quantity'];
                    $retQty = (float)($item['returned_quantity'] ?? 0);
                    $netQty = (float)($item['net_quantity'] ?? ($origQty - $retQty));
                    $cost = (float)($item['cost_per_unit'] ?? 0);
                    $netLine = $netQty * $cost;
                    $netTotalCost += $netLine;
                    $currentWhStock = (float)($item['current_variant_stock'] ?? 0);
                    $maxReturnable = min($netQty, $currentWhStock);
                ?>
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                            <div class="font-semibold text-text-primary"><?= e($item['variant_name']) ?></div>
                            <div class="text-xs text-text-muted">In Warehouse Stock: <?= number_format($currentWhStock) ?> sacks</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                            <?php if (!empty($item['variant_unit'])): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                    <?= e($item['variant_unit']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-text-muted text-xs">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary text-right font-medium">
                            <?= number_format($origQty) ?> <span class="text-xs text-text-muted">sacks</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-right">
                            <?php if ($retQty > 0): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <i data-lucide="corner-up-left" class="w-3 h-3 text-rose-600"></i>
                                    -<?= number_format($retQty) ?> sacks
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold text-text-primary">
                            <?= number_format($netQty) ?> <span class="text-xs text-text-muted font-normal">sacks</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted text-right">
                            ₱<?= number_format($cost, 2) ?>
                        </td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm font-semibold text-text-primary text-right sm:pr-6">
                            ₱<?= number_format($netLine, 2) ?>
                        </td>
                        <?php if($stockIn['status'] === 'active'): ?>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-center text-sm font-medium sm:pr-6">
                                <?php if ($netQty > 0 && $maxReturnable > 0): ?>
                                    <button type="button" 
                                            onclick="openReturnModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['variant_name'], ENT_QUOTES) ?>', <?= $netQty ?>, <?= $maxReturnable ?>)"
                                            class="inline-flex items-center gap-1 rounded bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 border border-rose-200 transition">
                                        <i data-lucide="corner-up-left" class="w-3.5 h-3.5"></i> Return
                                    </button>
                                <?php elseif ($netQty <= 0): ?>
                                    <span class="text-xs text-text-muted italic">Fully Returned</span>
                                <?php else: ?>
                                    <span class="text-xs text-text-muted italic" title="No available warehouse stock left to return">0 in stock</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-background">
                <tr>
                    <th scope="row" colspan="6" class="hidden pl-6 pr-3 pt-4 text-right text-sm font-semibold text-text-primary sm:table-cell sm:pl-0">Net Invoice Cost</th>
                    <th scope="row" class="pl-4 pr-3 pt-4 text-left text-sm font-semibold text-text-primary sm:hidden">Net Total</th>
                    <td class="pl-3 pr-4 pt-4 text-right text-sm font-bold text-text-primary sm:pr-6">₱<?= number_format($netTotalCost, 2) ?></td>
                    <?php if($stockIn['status'] === 'active'): ?><td></td><?php endif; ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Returned Section / Supplier Returns on this Shipment -->
<div class="mt-10">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg leading-6 font-semibold text-text-primary flex items-center gap-2">
                <i data-lucide="corner-up-left" class="w-5 h-5 text-error"></i>
                Supplier Returns on this Shipment
                <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-700">
                    <?= count($returns) ?>
                </span>
            </h3>
            <p class="text-xs text-text-muted">Audit trail of all sacks returned back to supplier from this shipment (Stock OUT).</p>
        </div>
    </div>

    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
        <?php if(empty($returns)): ?>
            <div class="bg-surface px-6 py-8 text-center">
                <i data-lucide="check-circle" class="mx-auto h-8 w-8 text-emerald-500"></i>
                <p class="mt-2 text-sm font-medium text-text-primary">No Returns on this Shipment</p>
                <p class="text-xs text-text-muted">All inbound sacks from this shipment are retained in inventory.</p>
            </div>
        <?php else: ?>
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-background">
                    <tr>
                        <th scope="col" class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-text-primary sm:pl-6">Return No</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-text-primary">Return Date</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-text-primary">Rice Variant</th>
                        <th scope="col" class="px-3 py-3 text-right text-xs font-semibold text-text-primary">Sacks Returned (OUT)</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-text-primary">Reason</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-text-primary">Processed By</th>
                        <th scope="col" class="py-3 pl-3 pr-4 text-left text-xs font-semibold text-text-primary sm:pr-6">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border bg-surface">
                    <?php foreach($returns as $r): ?>
                        <tr>
                            <td class="whitespace-nowrap py-3.5 pl-4 pr-3 text-sm font-semibold text-error sm:pl-6">
                                <?= e($r['return_no']) ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-text-muted">
                                <?= date('M j, Y', strtotime($r['return_date'] ?? $r['created_at'])) ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm font-medium text-text-primary">
                                <?= e($r['variant_name']) ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-right font-bold text-error">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    -<?= number_format($r['quantity']) ?> sacks
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-text-primary">
                                <?= e($r['reason']) ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-text-muted">
                                <?= e($r['returned_by_name'] ?? 'System') ?>
                            </td>
                            <td class="whitespace-nowrap py-3.5 pl-3 pr-4 text-xs text-text-muted sm:pr-6">
                                <?= $r['notes'] ? e($r['notes']) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- RETURN MODAL -->
<div id="returnModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeReturnModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-surface rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-border">
            <form action="<?= url('/stock-ins/'.$stockIn['id'].'/return') ?>" method="POST" id="returnForm">
                <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                
                <div class="bg-surface px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 text-error sm:mx-0 sm:h-10 sm:w-10">
                            <i data-lucide="corner-up-left" class="h-6 w-6"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-semibold text-text-primary" id="modal-title">
                                Return Sacks to Supplier
                            </h3>
                            <div class="mt-1">
                                <p class="text-xs text-text-muted">
                                    Returning sacks will deduct inventory (<span class="font-semibold text-error">Stock OUT</span>) and log an audited return movement.
                                </p>
                            </div>

                            <div class="mt-4 space-y-4">
                                <!-- Line Item Selector -->
                                <div>
                                    <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Select Rice Variant to Return</label>
                                    <select name="stock_in_item_id" id="modalItemSelect" onchange="onModalItemChanged()" required class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                        <?php foreach($items as $it): 
                                            $nQty = (float)($it['net_quantity'] ?? $it['quantity']);
                                            $cWhStock = (float)($it['current_variant_stock'] ?? 0);
                                            $mReturnable = min($nQty, $cWhStock);
                                            if ($mReturnable > 0):
                                        ?>
                                            <option value="<?= $it['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($it['variant_name'], ENT_QUOTES) ?>" 
                                                    data-net="<?= $nQty ?>" 
                                                    data-max="<?= $mReturnable ?>">
                                                <?= e($it['variant_name']) ?> (Max Returnable: <?= number_format($mReturnable) ?> sacks)
                                            </option>
                                        <?php endif; endforeach; ?>
                                    </select>
                                </div>

                                <!-- Quantity to Return -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider">Number of Sacks to Return</label>
                                        <span id="maxHelperText" class="text-xs text-text-muted font-medium"></span>
                                    </div>
                                    <div class="relative rounded-md shadow-sm">
                                        <input type="number" step="1" min="1" name="return_quantity" id="modalReturnQty" required
                                               class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm font-bold text-error focus:border-error focus:outline-none focus:ring-1 focus:ring-error"
                                               placeholder="e.g. 50">
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                            <span class="text-xs text-text-muted font-medium">sacks</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Return Reason -->
                                <div>
                                    <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Return Reason</label>
                                    <select name="reason" id="modalReasonSelect" onchange="onReasonChanged()" required class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                        <option value="Over-delivery / excess sacks received">Over-delivery / Excess sacks received</option>
                                        <option value="Damaged / torn packaging">Damaged / Torn packaging</option>
                                        <option value="Poor grain quality / rejected inspection">Poor grain quality / Rejected inspection</option>
                                        <option value="Infestation / Moisture problem">Infestation / Moisture problem</option>
                                        <option value="Wrong rice variety delivered">Wrong rice variety delivered</option>
                                        <option value="other">Other / Custom Reason</option>
                                    </select>
                                </div>

                                <div id="customReasonContainer" class="hidden">
                                    <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Specify Custom Reason</label>
                                    <input type="text" name="custom_reason" id="customReasonInput" class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Enter reason for returning sacks">
                                </div>

                                <!-- Return Date -->
                                <div>
                                    <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Return Date</label>
                                    <input type="date" name="return_date" value="<?= date('Y-m-d') ?>" required class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label class="block text-xs font-semibold text-text-primary uppercase tracking-wider mb-1">Additional Notes (Optional)</label>
                                    <textarea name="notes" rows="2" class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Add any supplier coordination or delivery remarks..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-background px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-border">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-error text-base font-medium text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm Return (Stock OUT)
                    </button>
                    <button type="button" onclick="closeReturnModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-border shadow-sm px-4 py-2 bg-surface text-base font-medium text-text-primary hover:bg-background sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReturnModal(itemId = null, variantName = '', netQty = 0, maxReturnable = 0) {
    const modal = document.getElementById('returnModal');
    const select = document.getElementById('modalItemSelect');
    
    if (itemId && select) {
        select.value = itemId;
    }
    
    onModalItemChanged();
    modal.classList.remove('hidden');
    lucide.createIcons();
}

function closeReturnModal() {
    const modal = document.getElementById('returnModal');
    modal.classList.add('hidden');
}

function onModalItemChanged() {
    const select = document.getElementById('modalItemSelect');
    const qtyInput = document.getElementById('modalReturnQty');
    const helper = document.getElementById('maxHelperText');
    
    if (!select || !select.selectedOptions || select.selectedOptions.length === 0) return;
    
    const selectedOption = select.selectedOptions[0];
    const maxVal = parseFloat(selectedOption.getAttribute('data-max') || '0');
    
    qtyInput.max = maxVal;
    helper.textContent = `Max: ${maxVal} sacks`;
}

function onReasonChanged() {
    const reasonSelect = document.getElementById('modalReasonSelect');
    const customContainer = document.getElementById('customReasonContainer');
    const customInput = document.getElementById('customReasonInput');
    
    if (reasonSelect.value === 'other') {
        customContainer.classList.remove('hidden');
        customInput.required = true;
    } else {
        customContainer.classList.add('hidden');
        customInput.required = false;
    }
}

// Close on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeReturnModal();
    }
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
