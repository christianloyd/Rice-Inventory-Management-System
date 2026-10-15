<?php $title = 'Global Settings'; ?>
<?php ob_start(); ?>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Global Settings</h1>
            <p class="text-sm text-text-muted mt-1">Manage application-wide preferences, store details, and receipt defaults.</p>
        </div>
        <i data-lucide="settings-2" class="h-8 w-8 text-primary opacity-60"></i>
    </div>

    <!-- Settings Form -->
    <form method="POST" action="<?= url('/settings') ?>" class="space-y-6">
        <input type="hidden" name="_csrf" value="<?= e(\App\Middleware\CsrfMiddleware::generateToken()) ?>">

        <!-- Application Branding -->
        <div class="bg-surface rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-bg">
                <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                    <i data-lucide="layout-dashboard" class="h-4 w-4 text-primary"></i>
                    Application
                </h2>
            </div>
            <div class="px-6 py-5 space-y-5">
                <div>
                    <label for="app_name" class="block text-sm font-medium text-text-primary mb-1">System Name</label>
                    <input type="text" id="app_name" name="app_name" value="<?= e($settings['app_name'] ?? 'RISMS') ?>"
                        class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <p class="text-xs text-text-muted mt-1">Displayed in the sidebar header and browser tab title.</p>
                </div>
            </div>
        </div>

        <!-- Store Details -->
        <div class="bg-surface rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-bg">
                <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                    <i data-lucide="store" class="h-4 w-4 text-primary"></i>
                    Store Details
                </h2>
                <p class="text-xs text-text-muted mt-0.5">These values appear on printed receipts and invoices.</p>
            </div>
            <div class="px-6 py-5 space-y-5">
                <div>
                    <label for="store_name" class="block text-sm font-medium text-text-primary mb-1">Store Name</label>
                    <input type="text" id="store_name" name="store_name" value="<?= e($settings['store_name'] ?? '') ?>"
                        class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <div>
                    <label for="store_address" class="block text-sm font-medium text-text-primary mb-1">Store Address</label>
                    <input type="text" id="store_address" name="store_address" value="<?= e($settings['store_address'] ?? '') ?>"
                        placeholder="e.g. 123 Main St, Barangay Poblacion, Quezon City"
                        class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <div>
                    <label for="store_contact" class="block text-sm font-medium text-text-primary mb-1">Contact Number</label>
                    <input type="text" id="store_contact" name="store_contact" value="<?= e($settings['store_contact'] ?? '') ?>"
                        placeholder="e.g. +63 912 345 6789"
                        class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
            </div>
        </div>

        <!-- Receipt Customization -->
        <div class="bg-surface rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-bg">
                <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                    <i data-lucide="receipt" class="h-4 w-4 text-primary"></i>
                    Receipt & Invoice
                </h2>
            </div>
            <div class="px-6 py-5 space-y-5">
                <div>
                    <label for="receipt_footer" class="block text-sm font-medium text-text-primary mb-1">Receipt Footer Note</label>
                    <textarea id="receipt_footer" name="receipt_footer" rows="3"
                        class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none"
                        placeholder="e.g. Thank you for your business!"><?= e($settings['receipt_footer'] ?? '') ?></textarea>
                    <p class="text-xs text-text-muted mt-1">Printed at the bottom of every transaction invoice.</p>
                </div>
            </div>
        </div>

        <!-- Control Sheet Number -->
        <div class="bg-surface rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-bg">
                <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                    <i data-lucide="hash" class="h-4 w-4 text-primary"></i>
                    Control Sheet
                </h2>
                <p class="text-xs text-text-muted mt-0.5">Sequential number printed on Control Sheet receipts. Set this to match your physical pad's current position.</p>
            </div>
            <div class="px-6 py-5">
                <div>
                    <label for="next_control_number" class="block text-sm font-medium text-text-primary mb-1">Next Control Number</label>
                    <input type="number" id="next_control_number" name="next_control_number"
                        min="1" step="1"
                        value="<?= e($settings['next_control_number'] ?? '1') ?>"
                        class="w-40 rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary font-mono focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <p class="text-xs text-text-muted mt-1">The number will be zero-padded to 6 digits on the printed receipt (e.g. <code>015151</code>). Changing this only affects <em>new</em> unprinted receipts.</p>
                </div>
            </div>
        </div>

        <!-- Operational Toggles -->
        <div class="bg-surface rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-bg">
                <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                    <i data-lucide="sliders-horizontal" class="h-4 w-4 text-primary"></i>
                    Operational
                </h2>
            </div>
            <div class="px-6 py-5">
                <label class="flex items-start gap-3 cursor-pointer">
                    <div class="mt-0.5">
                        <input type="hidden" name="low_stock_notify" value="0">
                        <input type="checkbox" id="low_stock_notify" name="low_stock_notify" value="1"
                            <?= ($settings['low_stock_notify'] ?? '1') === '1' ? 'checked' : '' ?>
                            class="h-4 w-4 rounded border-border text-primary focus:ring-primary/40">
                    </div>
                    <div>
                        <span class="text-sm font-medium text-text-primary">Show Low-Stock Warnings on Dashboard</span>
                        <p class="text-xs text-text-muted mt-0.5">Highlights variants below their minimum stock threshold in the dashboard widget.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-primary-hover transition-colors focus:outline-none focus:ring-2 focus:ring-primary/50">
                <i data-lucide="save" class="h-4 w-4"></i>
                Save Settings
            </button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../layouts/app.php'; ?>
