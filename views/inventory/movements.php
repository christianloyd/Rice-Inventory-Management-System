<?php 
$title = "Movement Ledger";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">
            <?php if($variant): ?>
                Viewing stock ledger for <strong><?= e($variant) ?></strong>.
            <?php else: ?>
                Viewing global stock ledger for all variants.
            <?php endif; ?>
        </p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/inventory') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="arrow-left" class="-ml-1 mr-2 h-5 w-5 text-text-muted"></i>
            Inventory Overview
        </a>
    </div>
</div>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($movements)): ?>
                    <?php 
                        $title = "No ledger entries";
                        $message = "There are no stock movements recorded for this filter yet.";
                        $icon = 'activity';
                        require __DIR__ . '/../partials/empty-state.php'; 
                    ?>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Date</th>
                            <?php if(!$variant): ?>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Variant</th>
                            <?php endif; ?>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Type / Ref</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Before</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Change</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">After</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Actor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($movements as $m): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-text-muted sm:pl-6">
                                    <?= e($m['created_at']) ?>
                                </td>
                                <?php if(!$variant): ?>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-text-primary">
                                        <a href="<?= url('/inventory/'.$m['rice_variant_id'].'/movements') ?>" class="hover:underline"><?= e($m['variant_name']) ?></a>
                                    </td>
                                <?php endif; ?>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <?php 
                                        $typeLabel = match(strtoupper($m['type'] ?? '')) {
                                            'STOCK_IN'          => '<span class="text-success font-medium">Stock-In</span>',
                                            'SALE'              => '<span class="text-error font-medium">Sale / Out</span>',
                                            'ADJUSTMENT_IN'     => '<span class="text-warning font-medium">Adj. In</span>',
                                            'ADJUSTMENT_OUT'    => '<span class="text-warning font-medium">Adj. Out</span>',
                                            'STOCK_IN_REVERSAL' => strtoupper($m['reference_type'] ?? '') === 'STOCK_IN_RETURN'
                                                ? '<span class="text-error font-medium">Return to Supplier</span>'
                                                : '<span class="text-error font-medium">Stock-In Void</span>',
                                            'SALE_REVERSAL'     => '<span class="text-success font-medium">Sale Reversal</span>',
                                            default             => '<span class="text-text-muted">' . e($m['type'] ?? '—') . '</span>',
                                        };
                                    ?>
                                    <?= $typeLabel ?>
                                    <div class="text-xs text-text-muted mt-0.5">Ref: <?= e(strtoupper($m['reference_type']) . " #" . $m['reference_id']) ?></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted text-right">
                                    <?= number_format($m['previous_stock']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-right <?= $m['quantity_change'] > 0 ? 'text-success' : 'text-error' ?>">
                                    <?= $m['quantity_change'] > 0 ? '+' : '' ?><?= number_format($m['quantity_change']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-text-primary text-right">
                                    <?= number_format($m['new_stock']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= e($m['user_name']) ?>
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

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
