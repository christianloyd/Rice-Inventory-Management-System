<!-- views/partials/header.php -->
<div class="relative z-10 flex-shrink-0 flex h-16 bg-white border-b border-border shadow-sm">
    <div class="flex-1 px-4 flex justify-between sm:px-6 lg:px-8">
        <div class="flex-1 flex flex-col justify-center">
            <?php if (isset($title)): ?>
                <h1 class="text-2xl font-bold text-text-primary tracking-tight"><?= e($title) ?></h1>
            <?php endif; ?>
        </div>
        <div class="ml-4 flex items-center md:ml-6 space-x-5">
            
            <button class="p-2 rounded-full text-text-muted hover:text-text-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary relative transition-colors">
                <span class="sr-only">View notifications</span>
                <i data-lucide="bell" class="h-6 w-6 relative top-[1px]"></i>
                <!-- Notification badge -->
                <span class="absolute top-1 right-1 block w-2.5 h-2.5 bg-error rounded-full ring-2 ring-white hidden" id="notification-badge"></span>
            </button>
            
            <div class="flex items-center space-x-3 border-l border-border pl-5">
                <div class="h-9 w-9 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold shadow-sm" title="<?= e($_SESSION['full_name'] ?? 'User') ?>">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                </div>
            </div>
        </div>
    </div>
</div>
