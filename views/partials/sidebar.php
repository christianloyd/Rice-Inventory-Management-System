<!-- views/partials/sidebar.php -->
<div class="flex flex-col w-64 border-r border-border bg-surface pt-5 pb-4 overflow-y-auto">
    <div class="flex flex-col items-center flex-shrink-0 px-4 pb-4 border-b border-border">
        <img src="<?= url('/assets/img/logo.png') ?>" alt="Logo" class="h-16 w-16 object-contain mb-2">
        <span class="text-sm font-extrabold text-primary leading-tight text-center"><?= e(\App\Models\Setting::get('app_name', 'MAE CONSUMER')) ?></span>
    </div>
    <div class="mt-5 flex-grow flex flex-col">
        <nav class="flex-1 px-2 space-y-1 bg-surface" aria-label="Sidebar">
            <?php
            $role = $_SESSION['role'] ?? '';
            $nav = [
                ['name' => 'Dashboard', 'href' => \url('/dashboard'), 'icon' => 'layout-dashboard', 'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Sales',       'href' => \url('/sales'),       'icon' => 'shopping-cart', 'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Deliveries',  'href' => \url('/deliveries'),  'icon' => 'truck',         'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Inventory',   'href' => \url('/inventory'),   'icon' => 'boxes',         'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Stock-In',      'href' => \url('/stock-ins'),      'icon' => 'package-plus',   'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Stock Returns', 'href' => \url('/stock-returns'),  'icon' => 'corner-up-left', 'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'TBT Monitor', 'href' => \url('/tbt'), 'icon' => 'clock', 'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Utang Monitor', 'href' => \url('/utang'), 'icon' => 'calendar-days', 'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Rice & Prices', 'href' => \url('/catalog'), 'icon' => 'wheat', 'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Reports', 'href' => \url('/reports'), 'icon' => 'file-bar-chart-2', 'roles' => ['admin', 'staff', 'viewer']],
                ['name' => 'Expenses', 'href' => \url('/expenses'), 'icon' => 'receipt', 'roles' => ['admin', 'staff']],
                ['name' => 'Audit Logs', 'href' => \url('/audit-logs'), 'icon' => 'history', 'roles' => ['admin']],
                ['name' => 'Users', 'href' => \url('/users'), 'icon' => 'users', 'roles' => ['admin']],
                ['name' => 'Settings', 'href' => \url('/settings'), 'icon' => 'settings', 'roles' => ['admin']],
            ];
            $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            
            foreach ($nav as $item): 
                if (!in_array($role, $item['roles'])) continue;
                $active = strpos($currentPath, parse_url($item['href'], PHP_URL_PATH)) !== false;
            ?>
                <a href="<?= $item['href'] ?>" class="<?= $active ? 'bg-background text-primary' : 'text-text-primary hover:bg-background hover:text-primary' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i data-lucide="<?= $item['icon'] ?>" class="<?= $active ? 'text-primary' : 'text-text-muted group-hover:text-primary' ?> mr-3 flex-shrink-0 h-5 w-5"></i>
                    <?= $item['name'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    
    <div class="flex-shrink-0 flex border-t border-border p-4 bg-background/50">
        <div class="flex-shrink-0 w-full group block">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-text-primary">
                        <?= e($_SESSION['full_name'] ?? 'User') ?>
                    </p>
                    <p class="text-xs font-medium text-text-muted uppercase tracking-wider">
                        <?= e($_SESSION['role'] ?? '') ?>
                    </p>
                </div>
                <button type="button" onclick="showConfirmModal('<?= url('/logout') ?>', 'Sign Out', 'Are you sure you want to sign out of the application?', 'warning')" class="inline p-2 text-text-muted hover:text-error transition-colors" title="Logout">
                    <i data-lucide="log-out" class="h-5 w-5"></i>
                </button>
            </div>
        </div>
    </div>
</div>
