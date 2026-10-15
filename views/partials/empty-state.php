<div class="text-center py-12">
    <i data-lucide="<?= $icon ?? 'folder-open' ?>" class="mx-auto h-12 w-12 text-border"></i>
    <h3 class="mt-2 text-sm font-medium text-text-primary"><?= $title ?? 'No items found' ?></h3>
    <p class="mt-1 text-sm text-text-muted"><?= $message ?? 'Get started by creating a new record.' ?></p>
    <?php if (isset($actionUrl) && isset($actionText)): ?>
        <div class="mt-6">
            <a href="<?= $actionUrl ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i data-lucide="plus" class="-ml-1 mr-2 h-5 w-5"></i>
                <?= $actionText ?>
            </a>
        </div>
    <?php endif; ?>
</div>
