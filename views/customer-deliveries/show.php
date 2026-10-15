<?php
$title = 'Delivery Details – ' . e($delivery['receipt_no']);
$d     = $delivery;

$isCancelled = ($d['sale_status'] ?? '') === 'cancelled';
$isUtang     = $d['payment_type'] === 'utang';
$isOwner     = $d['payment_type'] === 'owner';
$isPaid      = $d['pay_status']   === 'paid';

ob_start();
?>

<!-- Header / Page Title -->
<div class="sm:flex sm:items-center no-print">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">
            Viewing delivery transaction <span class="font-mono font-medium text-text-primary"><?= e($d['receipt_no']) ?></span>
        </p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex items-center gap-3">
        <?php if (!$isCancelled): ?>
        <a href="<?= url('/deliveries/' . $d['id'] . '/receipt') ?>" target="_blank"
           class="inline-flex items-center justify-center rounded-md border border-transparent bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none">
            <i data-lucide="printer" class="-ml-1 mr-2 h-5 w-5"></i>
            Print Receipt
        </a>
        <?php endif; ?>
        <a href="<?= url('/deliveries') ?>"
           class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none">
            <i data-lucide="arrow-left" class="-ml-1 mr-2 h-4 w-4"></i>
            Back to Deliveries
        </a>
    </div>
</div>

<?php if ($isCancelled): ?>
<div class="mt-4 rounded-md bg-red-50 border border-red-200 p-4 flex items-center gap-3">
    <i data-lucide="ban" class="h-5 w-5 text-error flex-shrink-0"></i>
    <p class="text-sm font-medium text-error">This delivery has been voided / cancelled.</p>
</div>
<?php endif; ?>

<div class="mt-6 bg-surface shadow overflow-hidden sm:rounded-lg border border-border">

    <!-- ── Delivery Header ─────────────────────────────────────────── -->
    <div class="px-4 py-6 border-b border-border sm:px-6">
        <div class="flex justify-between items-start flex-wrap gap-4">
            <div>
                <h2 class="text-xl font-bold text-primary"><?= e(\App\Models\Setting::get('app_name', 'MAE CONSUMER GOODS TRADING')) ?></h2>
                <p class="mt-0.5 text-sm text-text-muted">Bagong Buhay, Balangasan Dist., Pagadian City</p>
                <?php $contact = \App\Models\Setting::get('store_contact', ''); if ($contact): ?>
                <p class="text-xs text-text-muted"><?= e($contact) ?></p>
                <?php endif; ?>

                <div class="mt-4">
                    <p class="text-sm font-medium text-text-primary">Delivered To:</p>
                    <p class="text-sm text-text-muted"><?= e($d['customer_name'] ?: 'Not Specified') ?></p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-text-muted mb-1">Receipt No: <span class="font-mono font-medium text-text-primary"><?= e($d['receipt_no']) ?></span></p>
                <p class="text-sm text-text-muted mb-1">Delivery Date: <span class="font-medium text-text-primary"><?= date('F j, Y', strtotime($d['delivery_date'])) ?></span></p>
                <p class="text-sm text-text-muted mb-1">Recorded: <span class="font-medium text-text-primary"><?= date('M j, Y g:i A', strtotime($d['created_at'])) ?></span></p>
                <p class="text-sm text-text-muted">By: <span class="font-medium text-text-primary"><?= e($d['recorder_name'] ?? '—') ?></span></p>

                <div class="mt-3">
                    <?php if ($isCancelled): ?>
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">Voided</span>
                    <?php elseif ($isOwner): ?>
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">Owner Transfer</span>
                    <?php elseif ($isUtang && $isPaid): ?>
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">Utang · Paid</span>
                    <?php elseif ($isUtang): ?>
                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">Utang · Outstanding</span>
                    <?php else: ?>
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">Cash · Paid</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Items Table ─────────────────────────────────────────────── -->
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-sm font-semibold text-text-primary mb-3 uppercase tracking-wide">Items Delivered</h3>
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-background">
                <tr>
                    <th scope="col" class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide sm:pl-0">#</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Item / Variant</th>
                    <th scope="col" class="px-3 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Unit Price</th>
                    <th scope="col" class="px-3 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Discount/sk</th>
                    <th scope="col" class="px-3 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Qty</th>
                    <th scope="col" class="py-3 pl-3 pr-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wide sm:pr-0">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-surface">
                <?php foreach ($d['items'] as $idx => $item):
                    $disc     = (float)($item['discount_per_sack'] ?? 0);
                    $netPrice = max(0, $item['unit_price'] - $disc);
                ?>
                <tr>
                    <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm text-text-muted sm:pl-0"><?= $idx + 1 ?></td>
                    <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-text-primary"><?= e($item['variant_name']) ?></td>
                    <td class="whitespace-nowrap px-3 py-3 text-sm text-text-primary text-right">
                        <?= $isOwner ? '—' : '₱' . number_format($item['unit_price'], 2) ?>
                    </td>
                    <td class="whitespace-nowrap px-3 py-3 text-sm text-right">
                        <?php if ($disc > 0 && !$isOwner): ?>
                            <span class="text-amber-600">−₱<?= number_format($disc, 2) ?></span>
                        <?php else: ?>
                            <span class="text-text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="whitespace-nowrap px-3 py-3 text-sm text-text-primary text-right"><?= number_format($item['quantity']) ?></td>
                    <td class="whitespace-nowrap py-3 pl-3 pr-4 text-sm font-semibold text-text-primary text-right sm:pr-0">
                        <?= $isOwner ? '—' : '₱' . number_format($item['subtotal'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Totals ──────────────────────────────────────────────────── -->
    <div class="px-4 py-5 sm:px-6 border-t border-border bg-background flex flex-col items-end">
        <dl class="space-y-3 text-sm text-text-muted min-w-[300px]">
            <?php
            $globalDiscount = 0;
            foreach ($d['items'] as $it) { $globalDiscount += (($it['discount_per_sack'] ?? 0) * $it['quantity']); }
            $grossTotal = $d['total_amount'] + $globalDiscount;
            ?>

            <?php if ($isOwner): ?>
                <div class="flex justify-between pt-3 border-t border-border mt-3 text-base font-semibold text-amber-600">
                    <dt>Status</dt>
                    <dd class="text-right">Internal Transfer</dd>
                </div>
            <?php else: ?>

                <?php if ($globalDiscount > 0): ?>
                <div class="flex justify-between">
                    <dt>Gross Total</dt>
                    <dd class="text-text-primary text-right font-medium">₱<?= number_format($grossTotal, 2) ?></dd>
                </div>
                <div class="flex justify-between text-error">
                    <dt>Total Discount</dt>
                    <dd class="text-right font-medium">−₱<?= number_format($globalDiscount, 2) ?></dd>
                </div>
                <?php endif; ?>

                <div class="flex justify-between pt-3 border-t border-border mt-3 text-base font-semibold text-text-primary">
                    <dt>Net Total</dt>
                    <dd class="text-right">₱<?= number_format($d['total_amount'], 2) ?></dd>
                </div>

                <?php if ($isUtang): ?>
                <div class="flex justify-between">
                    <dt>Amount Paid</dt>
                    <dd class="text-text-primary text-right">₱<?= number_format($d['amount_paid'], 2) ?></dd>
                </div>
                <div class="flex justify-between pt-3 border-t border-border mt-3 <?= $isPaid ? 'text-success' : 'text-error' ?> font-semibold">
                    <dt>Remaining Balance</dt>
                    <dd class="text-right">₱<?= number_format($d['balance'], 2) ?></dd>
                </div>
                <?php else: ?>
                <div class="flex justify-between">
                    <dt>Cash Tendered</dt>
                    <dd class="text-text-primary text-right">₱<?= number_format($d['amount_paid'], 2) ?></dd>
                </div>
                <div class="flex justify-between pt-3 border-t border-border mt-3 text-success font-semibold">
                    <dt>Change</dt>
                    <dd class="text-right">₱<?= number_format(max(0, $d['amount_paid'] - $d['total_amount']), 2) ?></dd>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        </dl>
    </div>

    <!-- ── Payment Type Info / Credit Box ──────────────────────────── -->
    <?php if ($isUtang && !$isCancelled): ?>
    <div class="px-4 py-5 sm:px-6 border-t border-border">
        <div class="rounded-lg border-2 <?= $isPaid ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50' ?> p-4">
            <p class="font-bold uppercase tracking-wide text-sm mb-3 <?= $isPaid ? 'text-green-700' : 'text-red-700' ?>">
                ★ <?= $isPaid ? 'Utang · Fully Settled' : 'Utang Delivery (Credit Outstanding)' ?> ★
            </p>
            <div class="grid grid-cols-3 gap-2 text-sm">
                <div>
                    <p class="text-xs text-text-muted uppercase tracking-wide">Total Due</p>
                    <p class="font-semibold text-text-primary">₱<?= number_format($d['total_amount'], 2) ?></p>
                </div>
                <div>
                    <p class="text-xs text-text-muted uppercase tracking-wide">Amount Paid</p>
                    <p class="font-semibold text-text-primary">₱<?= number_format($d['amount_paid'], 2) ?></p>
                </div>
                <div>
                    <p class="text-xs text-text-muted uppercase tracking-wide">Balance</p>
                    <p class="font-semibold <?= $isPaid ? 'text-success' : 'text-error' ?>">₱<?= number_format($d['balance'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Notes ───────────────────────────────────────────────────── -->
    <?php if (!empty($d['notes'])): ?>
    <div class="px-4 py-4 sm:px-6 border-t border-border bg-surface text-sm text-text-muted">
        <strong>Notes:</strong><br>
        <?= nl2br(e($d['notes'])) ?>
    </div>
    <?php endif; ?>

</div><!-- /.card -->

<?php
$content = ob_get_clean();
require base_path('views/layouts/app.php');
?>
