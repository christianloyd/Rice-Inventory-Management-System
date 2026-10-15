<!-- Alert Dialog Modal (Hidden by default) -->
<div id="alertDialogModal" style="display: none;" class="fixed z-[60] inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-text-primary bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeAlertModal()"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-surface rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            <div class="bg-surface px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-border">
                <div class="sm:flex sm:items-start">
                    <div id="alertIconContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <i data-lucide="alert-triangle" class="h-6 w-6" id="alertIcon"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-text-primary" id="alertTitle">
                            Alert
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-text-muted whitespace-pre-wrap" id="alertMessage">
                                <!-- Message content -->
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-background px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-border">
                <button type="button" onclick="closeAlertModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.showAlertModal = function(title, message, type = 'error') {
    const modal = document.getElementById('alertDialogModal');
    const titleEl = document.getElementById('alertTitle');
    const msgEl = document.getElementById('alertMessage');
    const iconContainer = document.getElementById('alertIconContainer');
    const icon = document.getElementById('alertIcon');
    
    titleEl.textContent = title;
    msgEl.textContent = message;
    
    // Reset background and text colors
    iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10";
    icon.className = "h-6 w-6";
    
    // Configure based on type
    if (type === 'error') {
        iconContainer.classList.add('bg-status-errorBg');
        icon.classList.add('text-error');
        icon.setAttribute('data-lucide', 'alert-circle');
    } else if (type === 'warning') {
        iconContainer.classList.add('bg-status-warningBg');
        icon.classList.add('text-warning');
        icon.setAttribute('data-lucide', 'alert-triangle');
    } else {
        iconContainer.classList.add('bg-status-infoBg');
        icon.classList.add('text-info');
        icon.setAttribute('data-lucide', 'info');
    }
    
    // Re-initialize icon
    if (window.lucide) {
        lucide.createIcons();
    }
    
    // Show modal
    modal.style.display = 'block';
}

window.closeAlertModal = function() {
    document.getElementById('alertDialogModal').style.display = 'none';
}
</script>
