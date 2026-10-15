<?php
$title = "Stock Deliveries";
ob_start();
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-text-muted">A list of all recent stock delivery logs.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/deliveries/new') ?>" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="plus" class="-ml-1 mr-2 h-5 w-5"></i>
            Record Delivery
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="mt-4 rounded-md bg-red-50 p-4">
        <div class="flex">
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800"><?= e($_SESSION['error']) ?></h3>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="mt-4 rounded-md bg-green-50 p-4">
        <div class="flex">
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800"><?= e($_SESSION['success']) ?></h3>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Variant</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Qty (Sacks)</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Ref / Notes</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($deliveries as $item): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-text-primary sm:pl-6">
                                    <?= e($item['stock_in_date']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-text-primary">
                                    <?= e($item['variant_name']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <?= number_format($item['quantity']) ?>
                                </td>
                                <td class="px-3 py-4 text-sm text-text-muted">
                                    <?= $item['reference_note'] ? e($item['reference_note']) : '-' ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= e($item['recorder_name']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($deliveries)): ?>
                            <tr>
                                <td colspan="5" class="py-4 text-center text-sm text-text-muted">No stock deliveries logged yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
