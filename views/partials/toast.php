<!-- Toast Container -->
<div aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50">
    <div class="w-full flex flex-col items-center space-y-4 sm:items-end" id="toastContainer">
        <!-- JS will render toasts here -->
    </div>
</div>

<template id="toastTemplate">
    <div class="max-w-sm w-full bg-surface shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transition-all duration-300 transform scale-95 opacity-0">
        <div class="p-4 border-l-4">
            <div class="flex items-start">
                <div class="flex-shrink-0 pt-0.5">
                    <i data-lucide="info" class="h-5 w-5 toast-icon"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium text-text-primary toast-title"></p>
                    <p class="mt-1 text-sm text-text-muted toast-msg"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="bg-surface rounded-md inline-flex text-text-muted hover:text-text-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary toast-close">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
