<?php 
$title = "Price History";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Audit trail of all price adjustments for rice variants.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/prices') ?>" class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text-primary shadow-sm hover:bg-background focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="arrow-left" class="-ml-1 mr-2 h-5 w-5 text-text-muted"></i>
            Back to Pricing
        </a>
    </div>
</div>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($history)): ?>
                    <?php 
                        $title = "No history found";
                        $message = "There are no recorded price changes yet.";
                        $icon = 'history';
                        require __DIR__ . '/../partials/empty-state.php'; 
                    ?>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Date & Time</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Variant</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Change</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Reason</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Changed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($history as $item): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-text-primary sm:pl-6">
                                    <?= e($item['changed_at']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-text-primary">
                                    <?= e($item['variant_name']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <div class="flex items-center">
                                        <?php if($item['old_price'] !== null): ?>
                                            <span class="text-text-muted line-through mr-2">₱<?= number_format($item['old_price'], 2) ?></span>
                                            <i data-lucide="arrow-right" class="h-4 w-4 text-text-muted mr-2"></i>
                                        <?php endif; ?>
                                        <span class="font-semibold <?= $item['new_price'] > $item['old_price'] ? 'text-error' : 'text-success' ?>">₱<?= number_format($item['new_price'], 2) ?></span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-sm text-text-muted max-w-xs truncate">
                                    <?= e($item['reason']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= e($item['changed_by_name']) ?>
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
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
