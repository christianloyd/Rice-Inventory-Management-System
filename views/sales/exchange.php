<?php
$title = "Rice Variant Exchange";
ob_start();
?>

<div class="sm:flex sm:items-center mb-6">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Process a rice variant exchange for a customer. The original sale is marked as exchanged and a new sale is recorded.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/sales') ?>" class="inline-flex items-center text-sm text-text-muted hover:text-text-primary">
            <i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i> Back to Sales
        </a>
    </div>
</div>

<!-- ── Stage 1: Search for original sale ─────────────────────────── -->
<?php if (!$originalSale): ?>
<div class="bg-surface rounded-lg border border-border shadow-sm p-6 mb-6">
    <h3 class="text-base font-semibold text-text-primary mb-4 flex items-center gap-2">
        <i data-lucide="search" class="h-4 w-4 text-text-muted"></i>
        Step 1 — Find the Original Sale
    </h3>
    <form method="GET" action="<?= url('/sales/exchange') ?>" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-text-primary mb-1">Receipt / Transaction No. or Customer Name</label>
            <input type="text" name="q" value="<?= e($query) ?>" placeholder="e.g. INV-20240920 or Juan dela Cruz"
                   class="block w-full rounded-md border border-border px-3 py-2 text-sm bg-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-text-primary mb-1">Date (optional)</label>
            <input type="date" name="date" value="<?= e($date) ?>"
                   class="block rounded-md border border-border px-3 py-2 text-sm bg-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
        </div>
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary">
            <i data-lucide="search" class="h-4 w-4"></i> Search
        </button>
    </form>
</div>

<?php if (!empty($searchResults)): ?>
<div class="bg-surface rounded-lg border border-border shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-border">
        <p class="text-sm font-medium text-text-primary"><?= count($searchResults) ?> result<?= count($searchResults) !== 1 ? 's' : '' ?> found</p>
    </div>
    <table class="min-w-full divide-y divide-border text-sm">
        <thead class="bg-background">
            <tr>
                <th class="py-3 pl-6 pr-3 text-left font-semibold text-text-primary">Transaction No.</th>
                <th class="px-3 py-3 text-left font-semibold text-text-primary">Customer</th>
                <th class="px-3 py-3 text-left font-semibold text-text-primary">Date</th>
                <th class="px-3 py-3 text-right font-semibold text-text-primary">Total</th>
                <th class="px-3 py-3 text-right font-semibold text-text-primary"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border bg-surface">
            <?php foreach ($searchResults as $s): ?>
            <tr class="hover:bg-background/50">
                <td class="py-3 pl-6 pr-3 font-mono text-primary"><?= e($s['transaction_no']) ?></td>
                <td class="px-3 py-3 text-text-primary"><?= e($s['customer_name']) ?></td>
                <td class="px-3 py-3 text-text-muted"><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
                <td class="px-3 py-3 text-right font-semibold text-text-primary">₱<?= number_format($s['total_amount'], 2) ?></td>
                <td class="px-3 py-3 text-right">
                    <a href="<?= url('/sales/exchange') ?>?sale_id=<?= $s['id'] ?>"
                       class="inline-flex items-center gap-1 rounded-md bg-primary px-3 py-1 text-xs font-medium text-white hover:bg-primary-hover">
                        Select <i data-lucide="chevron-right" class="h-3 w-3"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php elseif ($query !== ''): ?>
<div class="flex flex-col items-center justify-center py-12 text-text-muted">
    <i data-lucide="search-x" class="h-10 w-10 mb-3 opacity-40"></i>
    <p class="text-sm font-medium">No completed sales matching "<strong><?= e($query) ?></strong>".</p>
</div>
<?php endif; ?>

<?php else: /* ── Stage 2: Exchange form ─── */ ?>

<!-- Original sale summary -->
<div class="bg-surface rounded-lg border border-border shadow-sm p-6 mb-6">
    <div class="flex items-start justify-between mb-4">
        <h3 class="text-base font-semibold text-text-primary flex items-center gap-2">
            <i data-lucide="receipt" class="h-4 w-4 text-text-muted"></i>
            Original Sale — <?= e($originalSale['transaction_no']) ?>
        </h3>
        <a href="<?= url('/sales/exchange') ?>" class="text-xs text-text-muted hover:text-text-primary flex items-center gap-1">
            <i data-lucide="x" class="h-3 w-3"></i> Change
        </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm mb-4">
        <div>
            <p class="text-text-muted text-xs uppercase tracking-wide">Customer</p>
            <p class="font-medium text-text-primary"><?= e($originalSale['customer_name']) ?></p>
        </div>
        <div>
            <p class="text-text-muted text-xs uppercase tracking-wide">Date</p>
            <p class="font-medium text-text-primary"><?= date('M j, Y', strtotime($originalSale['created_at'])) ?></p>
        </div>
        <div>
            <p class="text-text-muted text-xs uppercase tracking-wide">Old Total</p>
            <p class="font-bold text-primary text-base">₱<?= number_format($originalSale['total_amount'], 2) ?></p>
        </div>
        <?php if (!empty($originalSale['control_number'])): ?>
        <div>
            <p class="text-text-muted text-xs uppercase tracking-wide">Control No.</p>
            <p class="font-mono font-semibold text-text-primary"><?= e($originalSale['control_number']) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <div class="overflow-hidden rounded-lg border border-border">
        <table class="min-w-full divide-y divide-border text-sm">
            <thead class="bg-background">
                <tr>
                    <th class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-text-muted uppercase">Variant</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold text-text-muted uppercase">Qty</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold text-text-muted uppercase">Price</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold text-text-muted uppercase">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-surface">
                <?php foreach ($originalItems as $oi): ?>
                <tr>
                    <td class="py-2 pl-4 pr-3 font-medium text-text-primary"><?= e($oi['variant_name'] ?? '—') ?></td>
                    <td class="px-3 py-2 text-right text-text-primary"><?= number_format($oi['quantity']) ?></td>
                    <td class="px-3 py-2 text-right text-text-muted">₱<?= number_format($oi['unit_price'], 2) ?></td>
                    <td class="px-3 py-2 text-right font-semibold text-text-primary">₱<?= number_format($oi['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Exchange form -->
<div class="bg-surface rounded-lg border border-border shadow-sm p-6">
    <h3 class="text-base font-semibold text-text-primary mb-4 flex items-center gap-2">
        <i data-lucide="arrow-left-right" class="h-4 w-4 text-text-muted"></i>
        Step 2 — Select New Variant
    </h3>

    <form method="POST" action="<?= url('/sales/exchange') ?>" id="exchangeForm">
        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
        <input type="hidden" name="original_sale_id" value="<?= (int) $originalSale['id'] ?>">
        <input type="hidden" name="old_total" id="old_total" value="<?= (float) $originalSale['total_amount'] ?>">

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
            <div>
                <label for="customer_name" class="block text-sm font-medium text-text-primary mb-1">Customer Name</label>
                <input type="text" name="customer_name" id="customer_name"
                       value="<?= e($originalSale['customer_name']) ?>"
                       class="block w-full rounded-md border border-border px-3 py-2 text-sm bg-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <div>
                <label for="new_variant_id" class="block text-sm font-medium text-text-primary mb-1">New Rice Variant <span class="text-error">*</span></label>
                <select name="new_variant_id" id="new_variant_id" required
                        class="block w-full rounded-md border border-border px-3 py-2 text-sm bg-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">— Select variant —</option>
                    <?php foreach ($variants as $v): ?>
                    <option value="<?= $v['id'] ?>"
                            data-price="<?= (float) $v['price'] ?>"
                            data-stock="<?= (int) $v['stock'] ?>">
                        <?= e($v['name']) ?> (<?= e($v['unit']) ?>) — ₱<?= number_format($v['price'], 2) ?> — <?= number_format($v['stock']) ?> in stock
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-text-primary mb-1">Quantity <span class="text-error">*</span></label>
                <input type="number" name="quantity" id="quantity" min="1" step="1" value="1" required
                       class="block w-full rounded-md border border-border px-3 py-2 text-sm bg-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
        </div>

        <!-- Live preview panel -->
        <div id="previewPanel" class="hidden rounded-lg border-2 border-border bg-background p-5 mb-6">
            <p class="text-xs uppercase tracking-wider font-semibold text-text-muted mb-3">Exchange Preview</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-text-muted text-xs">Old Amount</p>
                    <p id="prev_old" class="font-semibold text-text-primary text-base">—</p>
                </div>
                <div>
                    <p class="text-text-muted text-xs">New Amount</p>
                    <p id="prev_new" class="font-semibold text-text-primary text-base">—</p>
                </div>
                <div>
                    <p class="text-text-muted text-xs">Final Qty (adjusted)</p>
                    <p id="prev_qty" class="font-semibold text-text-primary text-base">—</p>
                </div>
                <div>
                    <p class="text-text-muted text-xs">Difference to Collect</p>
                    <p id="prev_diff" class="font-bold text-base">—</p>
                </div>
            </div>
            <p id="prev_note" class="mt-3 text-xs text-text-muted italic"></p>
        </div>

        <!-- Amount Tendered UI Panel -->
        <div id="tenderPanel" class="hidden rounded-lg border-2 border-primary bg-primary/5 p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-center">
                <div>
                    <label for="amount_tendered" class="block text-sm font-bold text-primary mb-1">Amount Tendered <span class="text-error">*</span></label>
                    <input type="number" name="amount_tendered" id="amount_tendered" step="0.01" min="0" 
                           class="block w-full rounded-md border-primary px-3 py-2 text-lg font-bold bg-white focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>
                <div>
                    <p class="text-text-muted text-xs uppercase font-semibold mb-1">Customer Change</p>
                    <p id="prev_change" class="text-3xl font-bold text-success">—</p>
                </div>
            </div>
            <p id="tender_warning" class="hidden text-xs text-error mt-2 font-semibold">Insufficient cash tendered!</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" id="submitBtn"
                    class="inline-flex items-center gap-2 rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50">
                <i data-lucide="arrow-left-right" class="h-4 w-4"></i> Confirm Exchange
            </button>
            <a href="<?= url('/sales/exchange') ?>" class="text-sm text-text-muted hover:text-text-primary">Cancel</a>
        </div>
    </form>
</div>

<script>
(function () {
    const oldTotal   = parseFloat(document.getElementById('old_total').value);
    const selectEl   = document.getElementById('new_variant_id');
    const qtyEl      = document.getElementById('quantity');
    const panel      = document.getElementById('previewPanel');

    function fmt(n) { return '₱' + n.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}); }

    function update() {
        const opt   = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.dataset.price) { panel.classList.add('hidden'); return; }

        const price = parseFloat(opt.dataset.price) || 0;
        let qty   = Math.max(1, parseInt(qtyEl.value) || 1);
        if (price <= 0) { panel.classList.add('hidden'); return; }

        let discount = (qty >= 10) ? (10 * qty) : 0;
        let newTotal = (qty * price) - discount;

        // Auto-bump qty if new total < old total (no-refund rule)
        let adjusted = false;
        while (newTotal < oldTotal) { 
            qty++; 
            discount = (qty >= 10) ? (10 * qty) : 0;
            newTotal = (qty * price) - discount; 
            adjusted = true; 
        }

        document.getElementById('prev_old').textContent  = fmt(oldTotal);
        document.getElementById('prev_new').textContent  = fmt(newTotal);
        document.getElementById('prev_qty').textContent  = qty;

        const diff = Math.max(0, newTotal - oldTotal);
        const diffEl = document.getElementById('prev_diff');
        diffEl.textContent  = fmt(diff);
        diffEl.className    = 'font-bold text-base ' + (diff > 0 ? 'text-error' : 'text-success');

        const noteEl = document.getElementById('prev_note');
        const tenderPanel = document.getElementById('tenderPanel');
        const tenderInput = document.getElementById('amount_tendered');
        const submitBtn = document.getElementById('submitBtn');
        const tenderWarning = document.getElementById('tender_warning');
        
        let requiresTender = diff > 0;
        if (adjusted) {
            noteEl.textContent = 'Quantity was automatically increased from ' +
                (parseInt(qtyEl.value)||1) + ' to ' + qty +
                ' to match the old amount (no cash refund policy).';
        } else if (requiresTender) {
            noteEl.textContent = 'Customer pays ' + fmt(diff) + ' cash difference.';
        } else {
            noteEl.textContent = 'No additional payment required.';
        }
        
        panel.classList.remove('hidden');

        // Render Cash Register Tender Calculator if explicit differences discovered
        if (requiresTender) {
            tenderPanel.classList.remove('hidden');
            tenderInput.setAttribute('required', 'required');
            tenderInput.setAttribute('min', diff);
            
            // Validate Change dynamically upon rendering bounds
            const cash = parseFloat(tenderInput.value) || 0;
            if (cash < diff) {
                document.getElementById('prev_change').textContent = "—";
                document.getElementById('prev_change').classList.replace('text-success', 'text-text-muted');
                tenderWarning.classList.remove('hidden');
                submitBtn.disabled = true;
            } else {
                document.getElementById('prev_change').textContent = fmt(cash - diff);
                document.getElementById('prev_change').classList.replace('text-text-muted', 'text-success');
                tenderWarning.classList.add('hidden');
                submitBtn.disabled = false;
            }
        } else {
            tenderPanel.classList.add('hidden');
            tenderInput.removeAttribute('required');
            submitBtn.disabled = false;
            tenderInput.value = ''; 
        }
    }

    selectEl.addEventListener('change', update);
    qtyEl.addEventListener('input', update);
    document.getElementById('amount_tendered').addEventListener('input', update);
})();
</script>

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
