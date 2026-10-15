<?php 
$title = "Expenses";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-text-muted">Manage out-of-pocket expenses and daily operating costs.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button type="button" onclick="document.getElementById('addExpenseModal').classList.remove('hidden')" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
            Add Expense
        </button>
    </div>
</div>

<!-- Filters -->
<div class="mt-8 bg-surface shadow rounded-lg border border-border p-4">
    <form action="" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
        <div>
            <label for="start_date" class="block text-sm font-medium text-text-primary">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="<?= e($startDate) ?>" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-text-primary">End Date</label>
            <input type="date" name="end_date" id="end_date" value="<?= e($endDate) ?>" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
        </div>
        <div class="flex items-center">
            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none">
                Filter
            </button>
        </div>
        <div class="ml-auto">
            <p class="text-sm font-medium text-text-muted uppercase tracking-wider">Total Output (Period)</p>
            <p class="text-2xl font-bold text-red-600">₱<?= number_format($totalExpenses, 2) ?></p>
        </div>
    </form>
</div>

<!-- Expense Table -->
<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Description</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Category</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Logged By</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-text-primary">Amount</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php if(empty($expenses)): ?>
                            <tr><td colspan="6" class="py-4 text-center text-sm text-text-muted">No expenses recorded for this period.</td></tr>
                        <?php else: ?>
                            <?php foreach($expenses as $ex): ?>
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-text-primary sm:pl-6"><?= date('M j, Y', strtotime($ex['expense_date'])) ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary font-medium">
                                        <?= e($ex['description']) ?>
                                        <?php if (!empty($ex['rice_variant_id'])): ?>
                                        <div class="text-xs text-text-muted mt-1">
                                            Konsumo: <?= number_format($ex['rice_qty'], 2) ?> × <?= e($ex['rice_name']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted"><?= e($ex['category']) ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-text-primary"><?= e($ex['creator_name']) ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-red-600 text-right">₱<?= number_format($ex['amount'], 2) ?></td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <form action="<?= url("/expenses/{$ex['id']}/delete") ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                            <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                                            <button type="submit" class="text-error hover:text-red-800">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div id="addExpenseModal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block">
                    <button type="button" onclick="document.getElementById('addExpenseModal').classList.add('hidden')" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg font-medium leading-6 text-text-primary" id="modal-title">Record an Expense</h3>
                        <div class="mt-4">
                            <form action="<?= url('/expenses') ?>" method="POST">
                                <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                                <div class="grid grid-cols-1 gap-y-4">
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-text-primary">Description</label>
                                        <input type="text" name="description" id="description" required class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-text-primary">Category</label>
                                        <select name="category" id="category" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
                                            <option value="Utilities">Utilities</option>
                                            <option value="Load">Load</option>
                                            <option value="Food/Meal">Food/Meal</option>
                                            <option value="Repackaging">Repackaging</option>
                                            <option value="Transportation">Transportation</option>
                                            <option value="Konsumo">Konsumo</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div id="konsumo-fields" class="hidden grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="rice_variant_id" class="block text-sm font-medium text-text-primary">Rice Variant</label>
                                            <select name="rice_variant_id" id="rice_variant_id" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
                                                <option value="">Select Variant...</option>
                                                <?php foreach ($variants ?? [] as $v): ?>
                                                <option value="<?= $v['id'] ?>"><?= e($v['name']) ?><?= !empty($v['unit']) ? ' (' . e($v['unit']) . ')' : '' ?> (<?= number_format($v['current_stock_cache'] ?? $v['stock'] ?? 0) ?> left)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="rice_qty" class="block text-sm font-medium text-text-primary">Quantity Taken</label>
                                            <input type="number" step="0.01" min="0.01" name="rice_qty" id="rice_qty" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
                                        </div>
                                    </div>
                                    <div id="amount-field-container">
                                        <label for="amount" class="block text-sm font-medium text-text-primary">Amount (₱)</label>
                                        <input type="number" step="0.01" min="0" name="amount" id="amount" required class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="expense_date" class="block text-sm font-medium text-text-primary">Date</label>
                                        <input type="date" name="expense_date" id="expense_date" value="<?= date('Y-m-d') ?>" required class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-primary focus:ring-primary sm:text-sm border px-3 py-2">
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save Expense</button>
                                    <button type="button" onclick="document.getElementById('addExpenseModal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-md border border-border bg-white px-4 py-2 text-base font-medium text-text-primary shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('category').addEventListener('change', function() {
    const kFields = document.getElementById('konsumo-fields');
    const aField = document.getElementById('amount-field-container');
    const amtInput = document.getElementById('amount');
    
    if (this.value === 'Konsumo') {
        kFields.classList.remove('hidden');
        aField.classList.add('hidden');
        amtInput.removeAttribute('required');
        amtInput.value = '0';
    } else {
        kFields.classList.add('hidden');
        aField.classList.remove('hidden');
        amtInput.setAttribute('required', 'required');
    }
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
