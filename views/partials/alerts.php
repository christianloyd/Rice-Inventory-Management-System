<?php if (isset($_SESSION['flash'])): ?>
    <?php 
    $flash = $_SESSION['flash']; 
    unset($_SESSION['flash']); 
    $isError = $flash['type'] === 'error';
    $bgColor = $isError ? 'bg-status-errorBg' : 'bg-status-successBg';
    $borderColor = $isError ? 'border-error' : 'border-success';
    $textColor = $isError ? 'text-error' : 'text-success';
    $icon = $isError ? 'alert-triangle' : 'check-circle-2';
    ?>
    <div class="fixed top-6 right-6 z-50 w-full max-w-sm rounded-md shadow-xl <?= $bgColor ?> border <?= $borderColor ?> <?= $textColor ?> p-4 transition-all duration-500 ease-in-out transform translate-x-12 opacity-0" id="pageAlert">
        <div class="flex text-sm font-medium">
            <i data-lucide="<?= $icon ?>" class="h-5 w-5 mr-3 flex-shrink-0"></i>
            <?= e($flash['message']) ?>
            <button onclick="document.getElementById('pageAlert').remove()" class="ml-auto text-current opacity-75 hover:opacity-100">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pageAlert = document.getElementById('pageAlert');
            if (pageAlert) {
                // Slide In to left
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        pageAlert.classList.remove('translate-x-12', 'opacity-0');
                    });
                });
                
                // Slide Out to right Auto-hide
                setTimeout(() => {
                    pageAlert.classList.add('translate-x-12', 'opacity-0');
                    setTimeout(() => pageAlert.remove(), 500); 
                }, 2000);
            }
        });
    </script>
<?php endif; ?>
