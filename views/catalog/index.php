<?php 
$title = "Rice & Prices";
ob_start(); 
?>

<!-- Header -->
<div class="sm:flex sm:items-center font-sans text-text-primary">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-text-muted font-sans">Varieties you sell and their current selling price.</p>
    </div>
    <div class="mt-4 flex sm:mt-0 sm:ml-16 sm:flex-none items-center gap-3">
        <div class="relative rounded-md shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" onkeyup="filterCatalog()" class="block w-full rounded-md border-gray-300 pl-10 focus:border-amber-800 focus:ring-amber-800 sm:text-sm h-10 transition-colors" placeholder="Search variations...">
        </div>
        <button type="button" onclick="openAddVarietyModal()" class="inline-flex items-center justify-center rounded-md border border-transparent bg-amber-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-900 focus:ring-offset-2 sm:w-auto font-sans shadow-amber-900/20 h-10">
            <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
            Add Variety
        </button>
    </div>
</div>

<!-- Tabs -->
<div class="mt-6 font-sans">
    <div class="sm:hidden">
        <label for="tabs" class="sr-only">Select a tab</label>
        <select id="tabs" name="tabs" class="block w-full rounded-md border-border py-2 pl-3 pr-10 text-base focus:border-amber-800 focus:outline-none focus:ring-amber-800 sm:text-sm" onchange="switchTab(this.value)">
            <option value="varieties" selected>Varieties</option>
            <option value="history">Price history</option>
        </select>
    </div>
    <div class="hidden sm:block">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="switchTab('varieties')" id="tab-varieties" class="border-amber-800 text-amber-800 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Varieties
                </button>
                <button onclick="switchTab('history')" id="tab-history" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Price history
                </button>
            </nav>
        </div>
    </div>
</div>

<!-- Tab Content: Varieties -->
<div id="content-varieties" class="mt-8 font-sans">
    <?php if (empty($variants)): ?>
        <div class="text-center py-8 text-gray-500 border border-gray-200 rounded-lg bg-white">No rice variants available.</div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm bg-white" id="varietiesTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-left font-semibold text-gray-900">Name / Status</th>
                        <th scope="col" class="py-3 px-4 text-left font-semibold text-gray-900">Details</th>
                        <th scope="col" class="py-3 px-4 text-right font-semibold text-gray-900">Current Price</th>
                        <th scope="col" class="py-3 px-4 text-right font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white" id="varietiesTableBody">
                    <?php foreach ($variants as $index => $v): ?>
                        <tr class="hover:bg-gray-50 transition-colors variety-row" style="display: none;" data-index="<?= $index ?>">
                            <td class="whitespace-nowrap py-4 px-4 align-top">
                                <div class="font-bold text-gray-900 text-base mb-1"><?= e($v['name']) ?></div>
                                <?php if ($v['status'] === 'active'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 border border-green-200">Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 align-top text-gray-600">
                                <div>
                                    Unit: <span class="font-medium"><?= e($v['unit']) ?></span> &middot; Min Stock: <span class="font-medium"><?= (float)$v['minimum_stock'] ?></span>
                                    <?php if (empty($v['allow_wholesale_discount'])): ?>
                                        &middot; <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-red-50 text-red-700 border border-red-200">No Auto-Discount</span>
                                    <?php else: ?>
                                        &middot; <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">₱10 Discount (10+)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-gray-500 mt-1"><?= e($v['description']) ?: 'No description' ?></div>
                            </td>
                            <td class="whitespace-nowrap py-4 px-4 text-right align-top">
                                <div class="text-lg font-medium font-sans text-gray-900">
                                    <?= $v['current_price'] ? '₱' . number_format($v['current_price'], 2) : '<span class="text-gray-400 text-sm font-sans italic">Not set</span>' ?>
                                </div>
                            </td>
                            <td class="whitespace-nowrap py-4 px-4 text-right align-top">
                                <div class="flex items-center justify-end gap-1">
                                    <button title="Set price" onclick="openSetPriceModal(<?= $v['id'] ?>, '<?= htmlspecialchars($v['name'], ENT_QUOTES) ?>', <?= (float)$v['current_price'] ?>, '<?= e($v['unit']) ?>')" class="p-2 text-gray-400 hover:text-amber-800 transition-colors rounded hover:bg-amber-50">
                                        <i data-lucide="tag" class="w-5 h-5"></i>
                                    </button>
                                    <button title="Edit variety" onclick="openEditVarietyModal(<?= $v['id'] ?>, '<?= htmlspecialchars($v['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($v['description'], ENT_QUOTES) ?>', <?= (float)$v['minimum_stock'] ?>, '<?= e($v['unit']) ?>', <?= (int)($v['allow_wholesale_discount'] ?? 1) ?>)" class="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded hover:bg-blue-50">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </button>
                                    <form action="<?= url("/catalog/variants/{$v['id']}/toggle") ?>" method="POST" class="inline">
                                        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                                        <button type="submit" title="<?= $v['status']==='active' ? 'Disable' : 'Enable' ?>" class="p-2 text-gray-400 hover:text-<?= $v['status']==='active' ? 'red' : 'green' ?>-600 transition-colors rounded hover:bg-<?= $v['status']==='active' ? 'red' : 'green' ?>-50">
                                            <i data-lucide="power" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="mt-4 flex items-center justify-between hidden" id="paginationControls">
            <p class="text-sm text-gray-500" id="paginationShowingText"></p>
            <nav class="flex items-center gap-1" aria-label="Pagination" id="paginationNumbers">
                <!-- JS injected page buttons here -->
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Tab Content: Price History -->
<div id="content-history" class="mt-8 font-sans hidden">
    <?php if (empty($history)): ?>
        <div class="text-center py-8 text-gray-500 border border-gray-200 rounded-lg bg-white">No price history available.</div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm bg-white" id="historyTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-left font-semibold text-gray-900">Variety</th>
                        <th scope="col" class="py-3 px-4 text-left font-semibold text-gray-900">Details</th>
                        <th scope="col" class="py-3 px-4 text-right font-semibold text-gray-900">Price Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white" id="historyTableBody">
                    <?php foreach ($history as $index => $h): ?>
                        <tr class="hover:bg-gray-50 transition-colors history-row" style="display: none;" data-index="<?= $index ?>">
                            <td class="whitespace-nowrap py-4 px-4 align-top font-bold text-gray-900 text-base">
                                <?= e($h['variant_name']) ?><?= !empty($h['variant_unit']) ? ' (' . e($h['variant_unit']) . ')' : '' ?>
                            </td>
                            <td class="py-4 px-4 align-top text-gray-600">
                                <div class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Effective <?= date('M j, Y', strtotime($h['effective_date'])) ?></div>
                                <div><span class="font-medium"><?= e($h['changed_by_name'] ?? 'System') ?></span> &middot; <?= e($h['reason']) ?></div>
                            </td>
                            <td class="whitespace-nowrap py-4 px-4 text-right align-top">
                                <div class="flex items-center justify-end gap-3 text-sm font-sans">
                                    <span class="text-gray-500 line-through"><?= $h['old_price'] ? '₱' . number_format($h['old_price'], 2) : '—' ?></span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 text-gray-400"></i>
                                    <span class="font-bold text-gray-900 text-lg">₱<?= number_format($h['new_price'], 2) ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- History Pagination Controls -->
        <div class="mt-4 flex items-center justify-between hidden" id="historyPaginationControls">
            <p class="text-sm text-gray-500" id="historyPaginationShowingText"></p>
            <nav class="flex items-center gap-1" aria-label="Pagination" id="historyPaginationNumbers">
                <!-- JS injected page buttons here -->
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Set Price Modal -->
<div id="setPriceModal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto font-sans text-gray-900">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-[20px] bg-[#fdfaf5] border border-[#f0ebd8] px-4 pt-5 pb-4 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <!-- Close Button -->
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" onclick="document.getElementById('setPriceModal').classList.add('hidden')" class="bg-transparent text-gray-400 hover:text-gray-600 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
                
                <h3 class="text-2xl font-sans font-semibold text-gray-900 mb-1" id="setPriceModalTitle">Set price · Premium Rice</h3>
                <p class="text-sm text-gray-500 mb-6" id="setPriceModalCurrent">Current: ₱2,850.00 per sack</p>

                <form id="setPriceForm" method="POST" action="">
                    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">New price (₱)</label>
                            <input type="number" step="1" min="0" name="new_price" id="spNewPrice" required class="block w-full rounded-xl border-gray-300 bg-white shadow-sm focus:border-amber-800 focus:ring-amber-800 text-sm px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Effective date</label>
                            <!-- Note: The system doesn't explicitly store effective dates independently of created_at yet, so this implies the current timestamp in backend, but visually meets UI design -->
                            <input type="date" value="<?= date('Y-m-d') ?>" readonly class="block w-full rounded-xl border-gray-200 bg-gray-100 shadow-sm text-gray-500 text-sm px-3 py-2 border cursor-not-allowed">
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm text-gray-700 mb-1">Reason</label>
                        <input type="text" name="reason" placeholder="e.g. Supplier price increase" required class="block w-full rounded-xl border-gray-300 bg-white shadow-sm focus:border-amber-800 focus:ring-amber-800 text-sm px-3 py-2 border">
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" onclick="document.getElementById('setPriceModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-transparent hover:text-gray-900 focus:outline-none">Cancel</button>
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-[#605139] px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#473b28] focus:outline-none focus:ring-2 focus:ring-amber-900">Update price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Variety Modal (Standard Style) -->
<div id="addVarietyModal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form action="<?= url('/catalog/variants') ?>" method="POST">
                    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 font-sans">New rice variety</h3>
                    <div class="grid grid-cols-1 gap-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2 bg-[#fdfaf5]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2 bg-[#fdfaf5]">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit</label>
                                <select name="unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2 bg-[#fdfaf5]">
                                    <option value="25kg">25 kg</option><option value="10kg">10 kg</option><option value="5kg">5 kg</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Minimum stock</label>
                                <input type="number" step="1" name="minimum_stock" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2 bg-[#fdfaf5]">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Starting price (₱ per unit)</label>
                            <input type="number" step="1" min="0" name="initial_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2 bg-[#fdfaf5]">
                        </div>
                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" id="addAllowDiscount" name="allow_wholesale_discount" value="1" checked class="h-4 w-4 rounded border-gray-300 text-amber-800 focus:ring-amber-800">
                            <label for="addAllowDiscount" class="text-sm font-medium text-gray-700 cursor-pointer">Allow Automatic Wholesale Discount (₱10 off for 10+ sacks)</label>
                        </div>
                    </div>
                    <div class="mt-5 sm:flex sm:flex-row-reverse border-t border-gray-200 pt-4 mt-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl border border-transparent bg-[#766345] px-6 py-2 text-base font-medium text-white shadow-sm hover:bg-[#5b4c34] sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                        <button type="button" onclick="document.getElementById('addVarietyModal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-md bg-transparent px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Variety Modal (Standard Style) -->
<div id="editVarietyModal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form id="editVarietyForm" method="POST" action="">
                    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 font-sans">Edit Variety</h3>
                    <div class="grid grid-cols-1 gap-y-4">
                        <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" id="evName" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Description</label><input type="text" name="description" id="evDesc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-gray-700">Minimum Stock</label><input type="number" step="0.01" name="minimum_stock" id="evMinStock" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2"></div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit</label>
                                <select name="unit" id="evUnit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-800 focus:ring-amber-800 sm:text-sm border px-3 py-2">
                                    <option value="25kg">25 kg</option><option value="10kg">10 kg</option><option value="5kg">5 kg</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" id="evAllowDiscount" name="allow_wholesale_discount" value="1" class="h-4 w-4 rounded border-gray-300 text-amber-800 focus:ring-amber-800">
                            <label for="evAllowDiscount" class="text-sm font-medium text-gray-700 cursor-pointer">Allow Automatic Wholesale Discount (₱10 off for 10+ sacks)</label>
                        </div>
                    </div>
                    <div class="mt-5 sm:flex sm:flex-row-reverse"><button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-amber-800 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-amber-900 sm:ml-3 sm:w-auto sm:text-sm">Save Changes</button><button type="button" onclick="document.getElementById('editVarietyModal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    // Hide all contents
    document.getElementById('content-varieties').classList.add('hidden');
    document.getElementById('content-history').classList.add('hidden');
    
    // Reset desktop tab styles
    document.getElementById('tab-varieties').className = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200';
    document.getElementById('tab-history').className = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200';
    
    // Show selected and update styles
    if (tab === 'varieties') {
        document.getElementById('content-varieties').classList.remove('hidden');
        document.getElementById('tab-varieties').className = 'border-amber-800 text-amber-800 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200';
        document.getElementById('tabs').value = 'varieties';
    } else {
        document.getElementById('content-history').classList.remove('hidden');
        document.getElementById('tab-history').className = 'border-amber-800 text-amber-800 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200';
        document.getElementById('tabs').value = 'history';
    }
}

// Pagination Logic
const itemsPerPage = 5;
let currentPage = 1;

let searchTerm = '';

function filterCatalog() {
    searchTerm = document.getElementById('searchInput').value.toLowerCase();
    currentPage = 1;
    currentHistoryPage = 1;
    initPagination();
    initHistoryPagination();
}

function initPagination() {
    const allRows = document.querySelectorAll('.variety-row');
    const matchedRows = [];
    allRows.forEach(row => {
        row.style.display = 'none'; // hide immediately
        const text = row.innerText.toLowerCase();
        if(text.includes(searchTerm)) {
            matchedRows.push(row);
        }
    });

    const totalRows = matchedRows.length;
    
    if (totalRows === 0) {
        document.getElementById('paginationControls').classList.add('hidden');
        return;
    }
    
    document.getElementById('paginationControls').classList.remove('hidden');
    const totalPages = Math.ceil(totalRows / itemsPerPage);
    
    let paginationHtml = '';
    
    // Prev
    if (currentPage > 1) {
        paginationHtml += `<button onclick="prevPage()" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors"><i data-lucide="chevron-left" class="h-4 w-4"></i> Prev</button>`;
    } else {
        paginationHtml += `<span class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-400 opacity-50 cursor-not-allowed"><i data-lucide="chevron-left" class="h-4 w-4"></i> Prev</span>`;
    }
    
    // Pages
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            paginationHtml += `<span class="inline-flex items-center rounded-md border border-primary bg-primary px-3 py-1.5 text-sm font-semibold text-white">${i}</span>`;
        } else {
            paginationHtml += `<button onclick="goToPage(${i})" class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">${i}</button>`;
        }
    }
    
    // Next
    if (currentPage < totalPages) {
        paginationHtml += `<button onclick="nextPage()" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">Next <i data-lucide="chevron-right" class="h-4 w-4"></i></button>`;
    } else {
        paginationHtml += `<span class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-400 opacity-50 cursor-not-allowed">Next <i data-lucide="chevron-right" class="h-4 w-4"></i></span>`;
    }
    
    document.getElementById('paginationNumbers').innerHTML = paginationHtml;
    lucide.createIcons();
    
    // Show correct rows
    const startIdx = (currentPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;
    
    matchedRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        }
    });
    
    // Update labels
    document.getElementById('paginationShowingText').innerHTML = `Showing <span class="font-medium text-gray-900">${totalRows === 0 ? 0 : startIdx + 1}</span>–<span class="font-medium text-gray-900">${Math.min(endIdx, totalRows)}</span> of <span class="font-medium text-gray-900">${totalRows}</span> variants`;
}

function goToPage(page) {
    const rows = document.querySelectorAll('.variety-row');
    const totalPages = Math.ceil(rows.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    initPagination();
}

function prevPage() {
    goToPage(currentPage - 1);
}

function nextPage() {
    goToPage(currentPage + 1);
}

document.addEventListener('DOMContentLoaded', () => {
    initPagination();
    initHistoryPagination();
});

// History Pagination Logic
let currentHistoryPage = 1;

function initHistoryPagination() {
    const allRows = document.querySelectorAll('.history-row');
    const matchedRows = [];
    allRows.forEach(row => {
        row.style.display = 'none'; // hide immediately
        const text = row.innerText.toLowerCase();
        if(text.includes(searchTerm)) {
            matchedRows.push(row);
        }
    });

    const totalRows = matchedRows.length;
    
    if (totalRows === 0) {
        document.getElementById('historyPaginationControls').classList.add('hidden');
        return;
    }
    
    document.getElementById('historyPaginationControls').classList.remove('hidden');
    const totalPages = Math.ceil(totalRows / itemsPerPage);
    
    let paginationHtml = '';
    
    // Prev
    if (currentHistoryPage > 1) {
        paginationHtml += `<button onclick="prevHistoryPage()" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors"><i data-lucide="chevron-left" class="h-4 w-4"></i> Prev</button>`;
    } else {
        paginationHtml += `<span class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-400 opacity-50 cursor-not-allowed"><i data-lucide="chevron-left" class="h-4 w-4"></i> Prev</span>`;
    }
    
    // Pages
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentHistoryPage) {
            paginationHtml += `<span class="inline-flex items-center rounded-md border border-primary bg-primary px-3 py-1.5 text-sm font-semibold text-white">${i}</span>`;
        } else {
            paginationHtml += `<button onclick="goToHistoryPage(${i})" class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">${i}</button>`;
        }
    }
    
    // Next
    if (currentHistoryPage < totalPages) {
        paginationHtml += `<button onclick="nextHistoryPage()" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">Next <i data-lucide="chevron-right" class="h-4 w-4"></i></button>`;
    } else {
        paginationHtml += `<span class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-400 opacity-50 cursor-not-allowed">Next <i data-lucide="chevron-right" class="h-4 w-4"></i></span>`;
    }
    
    document.getElementById('historyPaginationNumbers').innerHTML = paginationHtml;
    lucide.createIcons();
    
    const startIdx = (currentHistoryPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;
    
    matchedRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        }
    });
    
    document.getElementById('historyPaginationShowingText').innerHTML = `Showing <span class="font-medium text-gray-900">${totalRows === 0 ? 0 : startIdx + 1}</span>–<span class="font-medium text-gray-900">${Math.min(endIdx, totalRows)}</span> of <span class="font-medium text-gray-900">${totalRows}</span> records`;
}

function goToHistoryPage(page) {
    const rows = document.querySelectorAll('.history-row');
    const totalPages = Math.ceil(rows.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentHistoryPage = page;
    initHistoryPagination();
}

function prevHistoryPage() {
    goToHistoryPage(currentHistoryPage - 1);
}

function nextHistoryPage() {
    goToHistoryPage(currentHistoryPage + 1);
}

function openSetPriceModal(id, name, currentPrice, unit) {
    document.getElementById('setPriceForm').action = `<?= url('/catalog/prices/') ?>${id}`;
    document.getElementById('setPriceModalTitle').innerText = `Set price · ${name}`;
    document.getElementById('setPriceModalCurrent').innerText = `Current: ${currentPrice ? '₱' + Math.round(currentPrice) : 'Not set'} per ${unit}`;
    
    // Pre-fill input if there's a current price to make minor changes easier
    if(currentPrice > 0) {
        document.getElementById('spNewPrice').value = currentPrice;
    } else {
        document.getElementById('spNewPrice').value = '';
    }
    document.getElementById('setPriceModal').classList.remove('hidden');
}

function openAddVarietyModal() {
    document.getElementById('addVarietyModal').classList.remove('hidden');
}

function openEditVarietyModal(id, name, desc, minStock, unit, allowDiscount = 1) {
    document.getElementById('editVarietyForm').action = `<?= url('/catalog/variants/') ?>${id}`;
    document.getElementById('evName').value = name;
    document.getElementById('evDesc').value = desc;
    document.getElementById('evMinStock').value = minStock;
    document.getElementById('evUnit').value = unit;
    document.getElementById('evAllowDiscount').checked = Boolean(Number(allowDiscount));
    document.getElementById('editVarietyModal').classList.remove('hidden');
}
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
