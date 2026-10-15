<?php
$title = "Utang Monitoring";
$currentStatus = $filters['status'] ?? 'outstanding';
$search = $filters['search'] ?? '';
ob_start();
?>

<div class="sm:flex sm:items-center justify-between">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-text-muted">Track and manage Utang (Credit) sales and partial payments.</p>
    </div>
</div>

<div class="mt-6 flex flex-col md:flex-row gap-4 justify-between items-center bg-surface p-4 rounded-lg shadow-sm">
    <!-- Filter Tabs -->
    <div class="flex space-x-1 border-b border-border bg-gray-50/50 p-1 rounded-md">
        <a href="<?= url('/utang?status=outstanding' . ($search ? '&search='.urlencode($search) : '')) ?>" 
           class="<?= $currentStatus === 'outstanding' ? 'bg-white shadow-sm text-primary' : 'text-text-muted hover:text-text-primary hover:bg-white/50' ?> rounded-md px-3 py-1.5 text-sm font-medium transition-all">
            <i data-lucide="clock" class="inline-block h-4 w-4 mr-1 mb-0.5"></i> Outstanding
        </a>
        <a href="<?= url('/utang?status=paid' . ($search ? '&search='.urlencode($search) : '')) ?>" 
           class="<?= $currentStatus === 'paid' ? 'bg-white shadow-sm text-primary' : 'text-text-muted hover:text-text-primary hover:bg-white/50' ?> rounded-md px-3 py-1.5 text-sm font-medium transition-all">
            <i data-lucide="check-circle" class="inline-block h-4 w-4 mr-1 mb-0.5"></i> Fully Paid
        </a>
        <a href="<?= url('/utang' . ($search ? '?search='.urlencode($search) : '')) ?>" 
           class="<?= empty($currentStatus) ? 'bg-white shadow-sm text-primary' : 'text-text-muted hover:text-text-primary hover:bg-white/50' ?> rounded-md px-3 py-1.5 text-sm font-medium transition-all">
            <i data-lucide="layers" class="inline-block h-4 w-4 mr-1 mb-0.5"></i> All
        </a>
    </div>

    <!-- Search -->
    <form action="<?= url('/utang') ?>" method="GET" class="flex w-full md:w-auto mt-2 md:mt-0">
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
    
    <a href="<?= url('/utang/print') ?>?status=<?= e($currentStatus) ?><?= $search ? '&search='.urlencode($search) : '' ?>" target="_blank" class="inline-flex items-center space-x-2 rounded-md border border-transparent bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800 focus:outline-none transition-colors mt-2 md:mt-0">
        <i data-lucide="printer" class="h-4 w-4"></i>
        <span>Print</span>
    </a>
</div>

<div id="alertContainer" class="mt-4 hidden"></div>

<div class="mt-6 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Customer / Invoice</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Variant(s)</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Initial Total</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Paid Amount</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Balance</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface" id="utangTableBody">
                        <?php foreach($records as $rec): ?>
                            <tr id="row-<?= $rec['id'] ?>">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-medium text-text-primary"><?= e($rec['customer_name']) ?></div>
                                    <div class="text-text-muted text-xs font-mono"><?= e($rec['transaction_no']) ?></div>
                                    <div class="text-text-muted text-xs"><?= date('M j, Y h:i A', strtotime($rec['created_at'])) ?></div>
                                </td>
                                <td class="px-3 py-4 text-sm text-text-primary min-w-[200px]">
                                    <?= e($rec['variants_summary']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-text-primary font-semibold">
                                    ₱<?= number_format($rec['total_amount'], 2) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-success font-semibold" id="paid-<?= $rec['id'] ?>">
                                    ₱<?= number_format($rec['paid_amount'], 2) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-error font-bold" id="balance-<?= $rec['id'] ?>">
                                    ₱<?= number_format(max(0, $rec['balance_remaining']), 2) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm" id="status-<?= $rec['id'] ?>">
                                    <?php if($rec['payment_status'] === 'paid'): ?>
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Fully Paid</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Outstanding</span>
                                    <?php endif; ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6" id="action-<?= $rec['id'] ?>">
                                    <?php if($rec['payment_status'] === 'outstanding'): ?>
                                        <button onclick="openPaymentModal(<?= $rec['id'] ?>, <?= max(0, $rec['balance_remaining']) ?>)" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                            <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Add Payment
                                        </button>
                                        <a href="<?= url('/sales/'.$rec['id'].'/receipt') ?>" target="_blank" class="text-text-muted hover:text-primary ml-2"><i data-lucide="printer" class="h-5 w-5"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($records)): ?>
                            <tr><td colspan="7" class="py-4 text-center text-sm text-text-muted">No Utang records found for this view.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?>
            <div class="mt-4 flex items-center justify-between border-t border-border bg-surface px-4 py-3 sm:px-6 rounded-lg shadow-sm">
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-text-muted">
                            Showing page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $pages ?></span>
                            (Total: <?= $total ?> records)
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <?php
                                $qs = '';
                                if ($search) $qs .= '&search=' . urlencode($search);
                                if ($currentStatus) $qs .= '&status=' . urlencode($currentStatus);
                            ?>
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?><?= $qs ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-text-muted ring-1 ring-inset ring-border hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                    <span class="sr-only">Previous</span>
                                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($page < $pages): ?>
                                <a href="?page=<?= $page + 1 ?><?= $qs ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-text-muted ring-1 ring-inset ring-border hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                    <span class="sr-only">Next</span>
                                    <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div id="paymentModal" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                <form id="paymentForm">
                    <div class="bg-surface px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="banknote" class="h-6 w-6 text-green-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-text-primary" id="modal-title">Record Partial Payment</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-text-muted mb-4">Enter the amount received from the customer for this credit sale.</p>
                                    <div class="mb-4">
                                        <p class="text-xs text-text-muted uppercase tracking-wider">Remaining Balance</p>
                                        <p class="text-lg font-bold text-error" id="modalRemainingBalance">₱0.00</p>
                                    </div>
                                    <div>
                                        <label for="paymentAmount" class="block text-sm font-medium text-text-primary">Payment Amount (₱)</label>
                                        <input type="number" step="0.01" min="0.01" id="paymentAmount" required
                                               class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm px-3 py-2 border">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" id="btnSubmitPayment" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">Confirm Payment</button>
                        <button type="button" onclick="closePaymentModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-surface px-3 py-2 text-sm font-semibold text-text-primary shadow-sm ring-1 ring-inset ring-border hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form id="csrfForm" class="hidden">
    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
</form>

<script>const AppUrl = "<?= rtrim(url(''), '/') ?>";</script>
<script src="<?= url('/assets/js/utang-monitor.js') ?>?v=<?= time() ?>"></script>

<?php 
$content = ob_get_clean();
$appConfig = require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../layouts/app.php'; 
?>
