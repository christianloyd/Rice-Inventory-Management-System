<?php
$title = 'Control Sheet – No. ' . e($controlNumber);
$printStyles = <<<CSS
    /* ── Base ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 11.5pt;
        background: #f5f5f5;
        color: #111;
        padding: 20px;
    }

    /* ── Screen toolbar ── */
    .toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding: 10px 16px;
        background: #1e293b;
        border-radius: 8px;
        color: #fff;
    }
    .toolbar a { color: #94a3b8; text-decoration: none; font-size: 0.85rem; }
    .toolbar a:hover { color: #fff; }
    .toolbar .sep { color: #475569; }
    .toolbar-title { font-size: 0.95rem; font-weight: 600; flex: 1; }
    .btn-print {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 18px; background: #0ea5e9; color: #fff;
        border: none; border-radius: 6px; font-size: 0.9rem; font-weight: 600;
        cursor: pointer; transition: background 0.15s;
    }
    .btn-print:hover { background: #0284c7; }

    /* ── Page ── */
    .page { max-width: 720px; margin: 0 auto; display: flex; flex-direction: column; gap: 0; }

    /* ── Single copy block ── */
    .receipt-copy {
        background: #fff; border: 1px solid #ccc;
        padding: 22px 26px 18px;
        page-break-inside: avoid; break-inside: avoid;
        position: relative;
    }

    /* Copy badge */
    .copy-label {
        position: absolute; top: 10px; right: 14px;
        font-size: 7.5pt; font-weight: 700;
        letter-spacing: 0.06em; text-transform: uppercase;
        color: #555; border: 1px solid #aaa; padding: 2px 7px; border-radius: 3px;
    }

    /* ── Cut-here divider ── */
    .cut-line { display: flex; align-items: center; gap: 8px; padding: 6px 0; color: #888; font-size: 8.5pt; user-select: none; }
    .cut-rule { flex: 1; border-top: 2px dashed #aaa; }
    .cut-text  { white-space: nowrap; font-style: italic; }

    /* ── Header ── */
    .biz-name { font-size: 14pt; font-weight: 700; text-align: center; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 3px; }
    .biz-addr { font-size: 8.5pt; text-align: center; color: #444; margin-bottom: 12px; }

    /* ── Title row ── */
    .title-row { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 2px solid #111; padding-bottom: 3px; margin-bottom: 10px; }
    .sheet-title { font-size: 12pt; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; letter-spacing: 0.05em; }
    .date-field { font-size: 9.5pt; }

    /* ── Field rows ── */
    .field-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px; }
    .field-label { font-size: 9.5pt; font-weight: 700; white-space: nowrap; min-width: 70px; }
    .field-value { flex: 1; border-bottom: 1px solid #555; font-size: 9.5pt; padding-bottom: 1px; min-height: 16px; }

    /* ── Items table ── */
    .items-table { width: 100%; border-collapse: collapse; margin: 10px 0 8px; font-size: 9.5pt; }
    .items-table thead th { text-align: left; font-weight: 700; border-bottom: 1px solid #555; padding: 2px 4px; }
    .items-table thead th:last-child,
    .items-table thead th:nth-child(3) { text-align: right; }
    .items-table tbody td { padding: 2px 4px; vertical-align: top; }
    .items-table tbody td:first-child { color: #555; }
    .items-table tbody td:last-child  { text-align: right; font-weight: 600; }
    .items-table tbody td:nth-child(3) { text-align: right; }
    .items-table tbody tr:last-child td { border-bottom: 1px solid #333; }

    /* ── Total ── */
    .total-row { display: flex; justify-content: flex-end; gap: 24px; font-size: 9.5pt; font-weight: 700; margin-bottom: 14px; }

    /* ── Bottom row ── */
    .bottom-row { display: flex; align-items: flex-end; justify-content: space-between; margin-top: 8px; }
    .ctrl-no-label { font-size: 7.5pt; text-transform: uppercase; color: #888; letter-spacing: 0.06em; margin-bottom: 2px; }
    .ctrl-no { font-size: 14pt; font-weight: 900; color: #b91c1c; letter-spacing: 0.08em; font-family: 'Courier New', Courier, monospace; }
    .sig-block { text-align: center; min-width: 160px; }
    .sig-line { border-top: 1.5px solid #333; width: 160px; margin: 0 auto 3px; }
    .sig-label { font-size: 7.5pt; color: #555; text-transform: uppercase; letter-spacing: 0.05em; }
    .sig-name  { font-size: 9pt; font-weight: 700; color: #111; margin-top: 1px; }

    /* ── Print ── */
    @media print {
        @page { margin: 8mm 10mm; size: portrait; }
        body { background: white !important; padding: 0; }
        .toolbar { display: none !important; }
        .page { max-width: 100%; }
        .receipt-copy { border: none; padding: 14px 18px 12px; }
        .cut-line { color: #999; }
    }
CSS;

ob_start();
?>

<!-- Screen-only toolbar -->
<div class="toolbar">
    <a href="<?= url('/sales/' . $sale['id']) ?>">← Back to Invoice</a>
    <span class="sep">|</span>
    <span class="toolbar-title">Control Sheet — No. <?= e($controlNumber) ?> | Txn: <?= e($sale['transaction_no']) ?></span>
    <button class="btn-print" onclick="window.print()">🖨 Print (2 Copies)</button>
</div>

<div class="page">

<?php
$copies = ['CASHIER COPY', 'CUSTOMER COPY'];
foreach ($copies as $copyIdx => $copyLabel):
    $isLast = ($copyIdx === count($copies) - 1);
?>

    <!-- ══ <?= $copyLabel ?> ══ -->
    <div class="receipt-copy">
        <div class="copy-label"><?= $copyLabel ?></div>

        <!-- Business Header -->
        <div class="biz-name">MAE CONSUMER GOODS TRADING</div>
        <div class="biz-addr">Bagong Buhay, Balangasan Dist., Pagadian City</div>
        <div class="biz-addr"><?= e(\App\Models\Setting::get('store_contact', '')) ?></div>

        <!-- Title Row -->
        <div class="title-row">
            <div>
                <span class="sheet-title">CONTROL SHEET</span>
            </div>
            <div style="text-align:right;">
                <span class="date-field">Date:&nbsp;<strong><?= date('m/d/Y', strtotime($sale['sale_date'] ?? $sale['created_at'])) ?></strong></span>
                <?php if (!empty($sale['sale_date']) && date('Y-m-d', strtotime($sale['sale_date'])) !== date('Y-m-d', strtotime($sale['created_at']))): ?>
                    <br><span class="date-field" style="font-size:7.5pt; color:#666;">(Recorded on <?= date('m/d/Y h:i A', strtotime($sale['created_at'])) ?>)</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customer Fields -->
        <div class="field-row">
            <span class="field-label">Name:</span>
            <span class="field-value"><?= e($sale['customer_name'] ?: 'Walk-in Customer') ?></span>
        </div>
        <div class="field-row">
            <span class="field-label">Address:</span>
            <span class="field-value">&nbsp;</span>
        </div>

        <!-- Item List -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:22px">#</th>
                    <th>Item / Description</th>
                    <th style="width:70px">Price</th>
                    <th style="width:50px">Qty</th>
                    <th style="width:82px">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $idx => $item): ?>
                <tr>
                    <td><?= $idx + 1 ?></td>
                    <td>
                        <?= e($item['variant_name']) ?><?= !empty($item['variant_unit']) ? ' (' . e($item['variant_unit']) . ')' : '' ?>
                        <?php if (in_array($item['remark'], ['tbt', 'utang']) && (float)$item['quantity_taken'] < (float)$item['quantity']): ?>
                            <br><span style="font-size: 7.5pt; color: #555;">[Released: <?= number_format($item['quantity_taken']) ?> | TBT: <?= number_format($item['quantity'] - $item['quantity_taken']) ?>]</span>
                        <?php endif; ?>
                    </td>
                    <td>&#8369;<?= number_format($item['unit_price'], 2) ?></td>
                    <td><?= number_format($item['quantity']) ?></td>
                    <td>&#8369;<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php
                // Pad to at least 5 rows so the form doesn't look empty on sparse orders
                for ($p = count($items); $p < 5; $p++): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td style="border-bottom:1px solid #e5e5e5">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Total -->
        <?php $receiptSubtotal = $sale['total_amount'] + ($sale['discount'] ?? 0); ?>
        <?php if (!empty($sale['discount']) && $sale['discount'] > 0): ?>
        <div class="total-row" style="margin-bottom:2px; font-weight: normal;">
            <span>SUBTOTAL:</span>
            <span>&#8369;<?= number_format($receiptSubtotal, 2) ?></span>
        </div>
        <div class="total-row" style="margin-bottom:2px; font-weight: normal;">
            <span>DISCOUNT:</span>
            <span>-&#8369;<?= number_format($sale['discount'], 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="total-row">
            <span>TOTAL:</span>
            <span>&#8369;<?= number_format($sale['total_amount'], 2) ?></span>
        </div>

        <?php if (($sale['status'] ?? '') === 'exchanged'): ?>
        <?php
            // Fetch the original sale that was exchanged into this one
            $origStmt = \App\Support\Database::getInstance()->prepare(
                "SELECT transaction_no, control_number, total_amount FROM sales WHERE exchanged_to_sale_id = ? LIMIT 1"
            );
            $origStmt->execute([$sale['id']]);
            $origForReceipt = $origStmt->fetch();
        ?>
        <?php if ($origForReceipt): ?>
        <div style="border:2px dashed #b91c1c;padding:8px 10px;margin:8px 0;font-size:9pt;">
            <div style="font-weight:900;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;color:#b91c1c;">★ EXCHANGE TRANSACTION ★</div>
            <div>Old Receipt No.: <strong><?= e($origForReceipt['transaction_no']) ?></strong><?= $origForReceipt['control_number'] ? ' (Control No. ' . e($origForReceipt['control_number']) . ')' : '' ?></div>
            <div>Old Amount:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8369;<?= number_format($origForReceipt['total_amount'], 2) ?></div>
            <div>New Amount:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8369;<?= number_format($sale['total_amount'], 2) ?></div>
            <div style="margin-top:4px;border-top:1px solid #b91c1c;padding-top:4px;font-weight:700;">
                Amount Paid (Difference):&nbsp;
                &#8369;<?= number_format($sale['amount_paid_difference'] ?? 0, 2) ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php 
        $hasUtangHistory = false;
        if (($sale['payment_method'] ?? '') === 'cheque') {
            $hasUtangHistory = true;
        } else {
            foreach ($items as $itm) {
                if (($itm['remark'] ?? '') === 'utang') {
                    $hasUtangHistory = true;
                    break;
                }
            }
        }
        ?>

        <?php if ($hasUtangHistory): ?>
        <?php $isFullyPaid = ($sale['payment_status'] ?? '') === 'paid'; ?>
        <?php $isCheque    = ($sale['payment_method'] ?? '') === 'cheque'; ?>
        <div style="border:2px dashed <?= $isCheque ? '#7c3aed' : ($isFullyPaid ? '#16a34a' : '#b91c1c') ?>;padding:8px 10px;margin:8px 0;font-size:9pt;<?= $isFullyPaid && !$isCheque ? 'background-color:#ebfdf0;' : ($isCheque ? 'background-color:#f5f3ff;' : '') ?>">
            <?php if ($isCheque): ?>
                <div style="font-weight:900;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;color:#7c3aed;">★ CHEQUE PAYMENT ★</div>
                <div>Cheque No.:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?= e($sale['cheque_number'] ?? 'N/A') ?></strong></div>
                <div>Clearance Date:&nbsp;&nbsp;<?= !empty($sale['cheque_date']) ? date('m/d/Y', strtotime($sale['cheque_date'])) : 'N/A' ?></div>
                <div style="margin-bottom:4px"></div>
            <?php elseif ($isFullyPaid): ?>
                <div style="font-weight:900;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;color:#16a34a;text-align:center;">★ FULLY PAID ★</div>
            <?php else: ?>
                <div style="font-weight:900;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;color:#b91c1c;">★ UTANG (CREDIT) ★</div>
            <?php endif; ?>
            <div>Total Amount:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8369;<?= number_format($sale['total_amount'], 2) ?></div>
            <div>Amount Paid:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8369;<?= number_format($sale['amount_tendered'], 2) ?></div>
            <?php if (!$isFullyPaid && !$isCheque): ?>
            <div style="margin-top:4px;border-top:1px solid #b91c1c;padding-top:4px;font-weight:700;">
                Remaining Balance:&nbsp;
                &#8369;<?= number_format(max(0, $sale['total_amount'] - $sale['amount_tendered']), 2) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Control Number (left) + Signature (right) -->
        <div class="bottom-row">
            <div>
                <div class="ctrl-no-label">Control No.</div>
                <div class="ctrl-no">No. <?= e($controlNumber) ?></div>
                <div style="font-size: 8pt; color: #555; margin-top: 4px;"><strong><?= e($sale['transaction_no']) ?></strong></div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Signature / Received by</div>
                <div class="sig-name"><?= e($printedBy) ?></div>
            </div>
        </div>
    </div><!-- /.receipt-copy -->

    <?php if (!$isLast): ?>
    <div class="cut-line">
        <div class="cut-rule"></div>
        <span class="cut-text">&#9986;&nbsp; cut here</span>
        <div class="cut-rule"></div>
    </div>
    <?php endif; ?>

<?php endforeach; ?>

</div><!-- /.page -->

<?php if (isset($_GET['autoprint'])): ?>
<script>
window.onload = function() {
    // Delay slightly to ensure fonts/styles are fully rendered
    setTimeout(function() {
        window.print();
    }, 300);
};
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/print.php';
?>
