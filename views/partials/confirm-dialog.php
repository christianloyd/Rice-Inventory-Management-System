<!-- Confirm Dialog Modal (Hidden by default) -->
<div id="confirmModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-text-primary bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeConfirmModal()"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-surface rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-surface px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-border">
                <div class="sm:flex sm:items-start">
                    <div id="confirmIconContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-status-warningBg sm:mx-0 sm:h-10 sm:w-10">
                        <i data-lucide="alert-triangle" class="h-6 w-6 text-warning" id="confirmIcon"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-text-primary" id="confirmTitle">
                            Confirm Action
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-text-muted" id="confirmMessage">
                                Are you sure you want to proceed?
                            </p>
                        </div>
                        <form id="confirmForm" method="POST" class="mt-4">
                            <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                            <!-- Dynamic inputs can be added here -->
                            <div id="confirmExtraInputs"></div>
                            
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button type="submit" id="confirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-error text-base font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error sm:ml-3 sm:w-auto sm:text-sm">
                                    Confirm
                                </button>
                                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-border shadow-sm px-4 py-2 bg-surface text-base font-medium text-text-primary hover:bg-background focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showConfirmModal(url, title, message, type = 'danger', extraHtml = '') {
    const modal = document.getElementById('confirmModal');
    const form = document.getElementById('confirmForm');
    const titleEl = document.getElementById('confirmTitle');
    const msgEl = document.getElementById('confirmMessage');
    const btn = document.getElementById('confirmBtn');
    const iconContainer = document.getElementById('confirmIconContainer');
    const icon = document.getElementById('confirmIcon');
    const extraInputs = document.getElementById('confirmExtraInputs');
    
    form.action = url;
    titleEl.textContent = title;
    msgEl.textContent = message;
    extraInputs.innerHTML = extraHtml;
    
    // Reset classes
    btn.className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm";
    iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10";
    
    if (type === 'danger') {
        btn.classList.add('bg-error', 'hover:opacity-90', 'focus:ring-error');
        iconContainer.classList.add('bg-status-errorBg');
        icon.classList.add('text-error');
    } else if (type === 'warning') {
        btn.classList.add('bg-warning', 'hover:opacity-90', 'focus:ring-warning');
        iconContainer.classList.add('bg-status-warningBg');
        icon.classList.add('text-warning');
    } else {
        btn.classList.add('bg-primary', 'hover:bg-primary-hover', 'focus:ring-primary');
        iconContainer.classList.add('bg-status-infoBg');
        icon.classList.add('text-info');
        icon.setAttribute('data-lucide', 'info');
        lucide.createIcons(); // refresh
    }
    
    modal.style.display = 'block';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
</script>
