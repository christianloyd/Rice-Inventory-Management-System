<?php 
$title = "System Activity Logs";
ob_start(); 
?>

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Track administrative actions and system modifications across all modules.</p>
    </div>
</div>

<!-- Filters -->
<div class="mt-6 flex flex-wrap gap-2 mb-4">
    <a href="<?= url('/audit-logs') ?>" class="<?= empty($currentModule) ? 'bg-primary text-white' : 'bg-surface text-text-muted border-border hover:bg-background' ?> px-3 py-1.5 rounded-md border text-sm font-medium transition-colors">
        All Logs
    </a>
    <a href="<?= url('/audit-logs?module=variants') ?>" class="<?= $currentModule === 'variants' ? 'bg-primary text-white' : 'bg-surface text-text-muted border-border hover:bg-background' ?> px-3 py-1.5 rounded-md border text-sm font-medium transition-colors">
        Rice Variants
    </a>
    <a href="<?= url('/audit-logs?module=pricing') ?>" class="<?= $currentModule === 'pricing' ? 'bg-primary text-white' : 'bg-surface text-text-muted border-border hover:bg-background' ?> px-3 py-1.5 rounded-md border text-sm font-medium transition-colors">
        Pricing
    </a>
    <a href="<?= url('/audit-logs?module=stock') ?>" class="<?= $currentModule === 'stock' ? 'bg-primary text-white' : 'bg-surface text-text-muted border-border hover:bg-background' ?> px-3 py-1.5 rounded-md border text-sm font-medium transition-colors">
        Stock Handling
    </a>
    <a href="<?= url('/audit-logs?module=sales') ?>" class="<?= $currentModule === 'sales' ? 'bg-primary text-white' : 'bg-surface text-text-muted border-border hover:bg-background' ?> px-3 py-1.5 rounded-md border text-sm font-medium transition-colors">
        Sales Operations
    </a>
</div>

<div class="mt-4 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <?php if (empty($logs)): ?>
                    <?php 
                        $title = "No logs found";
                        $message = "There are no audit logs matching your criteria.";
                        $icon = 'shield-alert';
                        require __DIR__ . '/../partials/empty-state.php'; 
                    ?>
                <?php else: ?>
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Timestamp</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">User</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Module / Action</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Record ID</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Details JSON</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm text-text-muted sm:pl-6">
                                    <?= e($log['created_at']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-text-primary">
                                    <?= e($log['user_name']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm">
                                    <span class="inline-flex rounded text-xs font-semibold uppercase tracking-wider text-primary mr-1 border border-primary/20 px-1 bg-primary/5">
                                        <?= e($log['module']) ?>
                                    </span>
                                    <span class="font-medium text-text-muted"><?= e($log['action']) ?></span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm font-mono text-text-muted">
                                    <?= e($log['record_id']) ?>
                                </td>
                                <td class="px-3 py-3 text-sm text-text-muted font-mono text-xs max-w-xs truncate" title="<?= e($log['new_value'] ?? '') ?>">
                                    <?= !empty($log['new_value']) ? e($log['new_value']) : '<span class="text-text-muted italic">—</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
