<?php 
$title = "Invoice Details";
$storeSettings = \App\Models\Setting::all();
ob_start(); 
?>

<div class="sm:flex sm:items-center no-print">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Viewing sales receipt for <?= e($sale['transaction_no']) ?></p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex items-center space-x-3">
        <?php if($sale['status'] === 'completed'): ?>
            <button onclick="window.print()" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
                <i data-lucide="printer" class="-ml-1 mr-2 h-5 w-5"></i>
                Print Invoice
            </button>
            <a href="<?= url('/sales/'.$sale['id'].'/receipt') ?>" target="_blank"
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:w-auto">
                <i data-lucide="file-text" class="-ml-1 mr-2 h-5 w-5"></i>
                Print Control Sheet
            </a>
            <button onclick="openVoidAuthModal('<?= url('/sales/'.$sale['id'].'/cancel') ?>')" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-error shadow-sm hover:bg-background focus:outline-none flex-shrink-0">
                Void Sale
            </button>
        <?php elseif($sale['status'] === 'exchanged'): ?>
            <button onclick="window.print()" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
                <i data-lucide="printer" class="-ml-1 mr-2 h-5 w-5"></i>
                Print Exchanged Invoice
            </button>
            <?php if (!empty($replacementSale)): ?>
            <a href="<?= url('/sales/'.$replacementSale['id']) ?>" 
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                <i data-lucide="external-link" class="-ml-1 mr-2 h-5 w-5"></i>
                View Replacement Sale
            </a>
            <?php endif; ?>
            <a href="<?= url('/sales/'.$sale['id'].'/receipt') ?>" target="_blank"
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:w-auto">
                <i data-lucide="file-text" class="-ml-1 mr-2 h-5 w-5"></i>
                Print Control Sheet
            </a>
        <?php endif; ?>
        <a href="<?= url('/sales/create') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none">
            Back to POS
        </a>
    </div>
</div>

<div class="mt-8 bg-surface shadow overflow-hidden sm:rounded-lg border border-border print:shadow-none print:border-none print:mt-0">
    <!-- Invoice Header -->
    <div class="px-4 py-8 border-b border-border sm:px-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-primary"><?= e($storeSettings['store_name'] ?? 'RISMS INVOICE') ?></h1>
                <p class="mt-1 text-sm text-text-muted"><?= e($storeSettings['store_address'] ?? 'MAE CONSUMER GOODS TRADING') ?></p>
                <?php if (!empty($storeSettings['store_contact'])): ?>
                <p class="text-xs text-text-muted"><?= e($storeSettings['store_contact']) ?></p>
                <?php endif; ?>
                <div class="mt-4">
                    <p class="text-sm font-medium text-text-primary">Billed To:</p>
                    <p class="text-sm text-text-muted"><?= e($sale['customer_name'] ?: 'Walk-in Customer') ?></p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-text-muted mb-1">Invoice Number: <span class="font-mono font-medium text-text-primary"><?= e($sale['transaction_no']) ?></span></p>
                <p class="text-sm text-text-muted mb-1">Date: <span class="font-medium text-text-primary"><?= date('F j, Y g:i A', strtotime($sale['created_at'])) ?></span></p>
                <p class="text-sm text-text-muted">Cashier: <span class="font-medium text-text-primary"><?= e($sale['cashier_name']) ?></span></p>
                
                <div class="mt-4 no-print">
                    <?php if($sale['status'] === 'completed'): ?>
                        <span class="inline-flex rounded-full bg-status-successBg px-3 py-1 text-sm font-semibold text-success">Completed</span>
                    <?php elseif($sale['status'] === 'exchanged'): ?>
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">EXCHANGED</span>
                        <?php if(!empty($replacementSale)): ?>
                            <div class="mt-2 text-xs text-text-muted">
                                Replaced by: <a href="<?= url('/sales/'.$replacementSale['id']) ?>" class="text-blue-600 hover:underline"><?= e($replacementSale['transaction_no']) ?></a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="inline-flex rounded-full bg-status-cancelBg px-3 py-1 text-sm font-semibold text-error">VOIDED</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="px-4 py-5 sm:px-6">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-background">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-0">Description</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Qty</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Unit Price</th>
                    <th scope="col" class="py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-text-primary sm:pr-0">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-surface">
                <?php foreach($items as $item): ?>
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-0">
                            <?= e($item['variant_name']) ?><?= !empty($item['variant_unit']) ? ' <span class="text-text-muted">(' . e($item['variant_unit']) . ')</span>' : '' ?>
                            <?php if (in_array($item['remark'], ['tbt', 'utang']) && (float)$item['quantity_taken'] < (float)$item['quantity']): ?>
                                <div class="text-xs text-text-muted mt-0.5 font-normal">
                                    <span class="text-green-600">Released: <?= number_format($item['quantity_taken']) ?></span> | <span class="text-orange-600">TBT: <?= number_format($item['quantity'] - $item['quantity_taken']) ?></span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary text-right">
                            <?= number_format($item['quantity']) ?>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted text-right">
                            ₱<?= number_format($item['unit_price'], 2) ?>
                        </td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm font-medium text-text-primary text-right sm:pr-0">
                            ₱<?= number_format($item['subtotal'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Invoice Totals -->
    <div class="px-4 py-5 sm:px-6 border-t border-border bg-background flex flex-col items-end">
        <dl class="space-y-3 text-sm text-text-muted min-w-[300px]">
            <?php $recordedSubtotal = $sale['total_amount'] + ($sale['discount'] ?? 0); ?>
            <div class="flex justify-between">
                <dt>Subtotal</dt>
                <dd class="text-text-primary text-right font-medium">₱<?= number_format($recordedSubtotal, 2) ?></dd>
            </div>
            
            <?php if (!empty($sale['discount']) && $sale['discount'] > 0): ?>
            <div class="flex justify-between text-error">
                <dt>Discount</dt>
                <dd class="text-right font-medium">-₱<?= number_format($sale['discount'], 2) ?></dd>
            </div>
            <?php endif; ?>

            <div class="flex justify-between pt-3 border-t border-border mt-3 text-base font-semibold text-text-primary">
                <dt>Total Amount</dt>
                <dd class="text-right">₱<?= number_format($sale['total_amount'], 2) ?></dd>
            </div>
            <?php $isCheque = ($sale['payment_method'] ?? '') === 'cheque'; ?>
            <div class="flex justify-between">
                <dt><?= $isCheque ? 'Cheque Amount' : 'Cash Tendered' ?></dt>
                <dd class="text-text-primary text-right">₱<?= number_format($sale['amount_tendered'], 2) ?></dd>
            </div>
            <div class="flex justify-between pt-3 border-t border-border mt-3">
                <dt class="font-medium text-text-primary">Change</dt>
                <?php $change = $sale['amount_tendered'] - $sale['total_amount']; ?>
                <dd class="text-text-primary text-right font-medium <?= $change > 0 ? 'text-success' : '' ?>">₱<?= number_format(max(0, $change), 2) ?></dd>
            </div>

            <!-- Payment Method Badge -->
            <div class="flex justify-between pt-3 border-t border-border mt-3">
                <dt class="font-medium text-text-primary">Payment Method</dt>
                <dd class="text-right">
                    <?php if ($isCheque): ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-700">
                            <i data-lucide="file-check" class="h-3 w-3"></i> Cheque
                        </span>
                    <?php elseif (($sale['payment_method'] ?? '') === 'cash'): ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                            <i data-lucide="banknote" class="h-3 w-3"></i> Cash
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">
                            <?= e(ucfirst($sale['payment_method'] ?? '—')) ?>
                        </span>
                    <?php endif; ?>
                </dd>
            </div>
        </dl>
    </div>

    <!-- Cheque Information Box -->
    <?php if ($isCheque): ?>
    <div class="px-4 py-5 sm:px-6 border-t border-border">
        <div class="rounded-lg border-2 border-purple-300 bg-purple-50 p-4">
            <p class="font-bold text-sm text-purple-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <i data-lucide="file-check" class="h-4 w-4"></i>
                Cheque Payment Details
            </p>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-text-muted uppercase tracking-wide">Cheque Number</p>
                    <p class="font-semibold text-text-primary font-mono"><?= e($sale['cheque_number'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <p class="text-xs text-text-muted uppercase tracking-wide">Expected Clearance Date</p>
                    <p class="font-semibold text-text-primary"><?= !empty($sale['cheque_date']) ? date('F j, Y', strtotime($sale['cheque_date'])) : 'N/A' ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($sale['notes']): ?>
    <div class="px-4 py-5 sm:px-6 border-t border-border bg-surface text-sm text-text-muted">
        <strong>Notes:</strong><br>
        <?= nl2br(e($sale['notes'])) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($storeSettings['receipt_footer'])): ?>
    <div class="px-4 py-4 sm:px-6 border-t border-border bg-background text-center text-xs text-text-muted print:block">
        <?= nl2br(e($storeSettings['receipt_footer'])) ?>
    </div>
    <?php endif; ?>
</div>

<style>
    @media print {
        @page { margin: 20mm; }
        body { background: white !important; }
        .no-print { display: none !important; }
    }
</style>

<!-- Void Authorization Modal -->
<div id="voidAuthModal" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black bg-opacity-25 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-border">
                <form id="voidAuthForm" method="POST">
                    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                    <div class="bg-surface px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-status-cancelBg sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="lock" class="h-6 w-6 text-error"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-medium leading-6 text-text-primary" id="modal-title">Admin Authorization Required</h3>
                                <div class="mt-2 text-sm text-text-muted">
                                    <p>Voiding this sale will restore the stock inventory natively. Please provide an Admin or Manager password to authorize this action.</p>
                                </div>
                                <div class="mt-4">
                                    <label for="admin_password" class="block text-sm font-medium text-text-primary">Admin Password</label>
                                    <input type="password" name="admin_password" id="admin_password" required class="mt-1 block w-full rounded-md border border-border bg-background px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-background px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-error px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirm Void
                        </button>
                        <button type="button" onclick="closeVoidAuthModal()" class="mt-3 inline-flex w-full justify-center rounded-md border border-border bg-surface px-4 py-2 text-base font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openVoidAuthModal(actionUrl) {
    document.getElementById('voidAuthForm').action = actionUrl;
    document.getElementById('voidAuthModal').classList.remove('hidden');
    document.getElementById('admin_password').value = '';
    // Focus the input safely after UI renders
    setTimeout(() => document.getElementById('admin_password').focus(), 100);
}

function closeVoidAuthModal() {
    document.getElementById('voidAuthModal').classList.add('hidden');
}
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
