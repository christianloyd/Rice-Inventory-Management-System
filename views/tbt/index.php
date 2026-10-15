<?php
$title = "TBT Monitoring";
$currentStatus = $filters['status'] ?? 'pending_pickup';
$search = $filters['search'] ?? '';
ob_start();
?>

<div class="sm:flex sm:items-center justify-between">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-text-muted">Track and release To Be Taken (TBT) sacks owed to customers.</p>
    </div>
</div>

<div class="mt-6 flex flex-col md:flex-row gap-4 justify-between items-center bg-surface p-4 rounded-lg shadow-sm">
    <!-- Filter Tabs -->
    <div class="flex space-x-1 border-b border-border bg-gray-50/50 p-1 rounded-md">
        <a href="<?= url('/tbt?status=pending_pickup' . ($search ? '&search='.urlencode($search) : '')) ?>" 
           class="<?= $currentStatus === 'pending_pickup' ? 'bg-white shadow-sm text-primary' : 'text-text-muted hover:text-text-primary hover:bg-white/50' ?> rounded-md px-3 py-1.5 text-sm font-medium transition-all">
            <i data-lucide="clock" class="inline-block h-4 w-4 mr-1 mb-0.5"></i> Pending
        </a>
        <a href="<?= url('/tbt?status=released' . ($search ? '&search='.urlencode($search) : '')) ?>" 
           class="<?= $currentStatus === 'released' ? 'bg-white shadow-sm text-primary' : 'text-text-muted hover:text-text-primary hover:bg-white/50' ?> rounded-md px-3 py-1.5 text-sm font-medium transition-all">
            <i data-lucide="check-circle" class="inline-block h-4 w-4 mr-1 mb-0.5"></i> Released
        </a>
        <a href="<?= url('/tbt?status=all' . ($search ? '&search='.urlencode($search) : '')) ?>" 
           class="<?= $currentStatus === 'all' ? 'bg-white shadow-sm text-primary' : 'text-text-muted hover:text-text-primary hover:bg-white/50' ?> rounded-md px-3 py-1.5 text-sm font-medium transition-all">
            <i data-lucide="layers" class="inline-block h-4 w-4 mr-1 mb-0.5"></i> All
        </a>
    </div>

    <!-- Search -->
    <form action="<?= url('/tbt') ?>" method="GET" class="flex w-full md:w-auto mt-2 md:mt-0">
        <?php if($currentStatus): ?>
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
        <?php endif; ?>
        <div class="relative flex-grow focus-within:z-10">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i data-lucide="search" class="h-4 w-4 text-text-muted"></i>
            </div>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search customer name..." class="block w-full rounded-none rounded-l-md border-border pl-10 focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border shadow-sm">
        </div>
        <button type="submit" class="relative -ml-px inline-flex items-center space-x-2 rounded-r-md border border-border bg-gray-50 px-4 py-2 text-sm font-medium text-text-secondary hover:bg-gray-100 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            Search
        </button>
    </form>
</div>

<div id="alertContainer" class="mt-4 hidden"></div>

<div class="mt-6 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Customer</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Variant</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Reserved</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Released</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Still Pending</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Date Purchased</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Date Released</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface" id="tbtTableBody">
                        <?php foreach($records as $rec): ?>
                            <tr id="row-<?= $rec['id'] ?>">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-medium text-text-primary"><?= e($rec['customer_name']) ?></div>
                                    <div class="text-text-muted text-xs"><?= e($rec['contact_number'] ?: 'No contact') ?></div>
                                    <?php if(!empty($rec['sale_remark']) && $rec['sale_remark'] === 'utang'): ?>
                                        <span class="inline-flex mt-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Utang-linked</span>
                                    <?php else: ?>
                                        <span class="inline-flex mt-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">TBT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <?= e($rec['variant_name']) ?><?= !empty($rec['variant_unit']) ? ' (' . e($rec['variant_unit']) . ')' : '' ?>
                                </td>
                                <!-- Reserved -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-text-primary">
                                    <?= number_format((float)($rec['quantity_reserved'] ?: $rec['quantity']), 0) ?>
                                </td>
                                <!-- Released so far -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-green-700">
                                    <?= number_format((float)($rec['quantity_released'] ?? 0), 0) ?>
                                </td>
                                <!-- Still pending -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold <?= (float)($rec['quantity_still_pending'] ?? 0) > 0 ? 'text-yellow-700' : 'text-text-muted' ?>">
                                    <?= number_format((float)($rec['quantity_still_pending'] ?? 0), 0) ?>
                                    <?php if($rec['status'] === 'pending_pickup' && $rec['days_pending'] > 7): ?>
                                        <div class="text-xs text-error font-bold"><?= $rec['days_pending'] ?> days pending!</div>
                                    <?php elseif($rec['status'] === 'pending_pickup'): ?>
                                        <div class="text-xs text-text-muted"><?= $rec['days_pending'] ?> day(s)</div>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <?php 
                                        $baseDate = $rec['sale_date'] ?: date('Y-m-d', strtotime($rec['recorded_at']));
                                        $baseTime = !empty($rec['sale_created_at']) ? date('h:i A', strtotime($rec['sale_created_at'])) : date('h:i A', strtotime($rec['recorded_at']));
                                    ?>
                                    <div><?= e($baseDate . ' ' . $baseTime) ?></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary">
                                    <?php if(!empty($rec['released_at'])): ?>
                                        <div><?= e(date('Y-m-d', strtotime($rec['released_at']))) ?></div>
                                        <div class="text-text-muted text-xs"><?= e(date('h:i A', strtotime($rec['released_at']))) ?></div>
                                    <?php else: ?>
                                        <span class="text-text-muted text-xs italic">Not released</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <?php if($rec['status'] === 'released'): ?>
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Fully Released</span>
                                    <?php elseif($rec['status'] === 'cancelled'): ?>
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Voided</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <?php if($rec['status'] === 'pending_pickup'): ?>
                                        <button onclick="openReleaseModal(<?= $rec['id'] ?>, <?= (float)($rec['quantity_still_pending'] ?? $rec['quantity']) ?>)" 
                                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                            <i data-lucide="check" class="h-4 w-4 mr-1"></i> Release
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($records)): ?>
                            <tr><td colspan="8" class="py-4 text-center text-sm text-text-muted">No TBT records found for this view.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form id="csrfForm" class="hidden">
    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
</form>

<!-- Partial Release Modal -->
<div id="releaseModal" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
                <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                        <i data-lucide="package-minus" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Release TBT Item</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">Enter the quantity to release to the customer now. The maximum you can release is <span id="modalMaxQty" class="font-bold text-gray-800"></span>.</p>
                            <label for="releaseQty" class="block text-sm font-medium leading-6 text-gray-900 mb-1 text-left">Quantity Taken Now</label>
                            <input type="number" step="0.01" min="0.01" id="releaseQty" class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <input type="hidden" id="releaseTbtId">
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="button" onclick="submitRelease()" id="btnSubmitRelease" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:col-start-2">
                        Release
                    </button>
                    <button type="button" onclick="closeReleaseModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>const AppUrl = "<?= rtrim(url(''), '/') ?>";</script>
<script src="<?= url('/assets/js/tbt-monitor.js') ?>?v=<?= time() ?>"></script>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
