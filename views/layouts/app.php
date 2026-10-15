<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($title ?? 'MAE CONSUMER') . ' — ' . e(\App\Models\Setting::get('app_name', 'MAE CONSUMER')) ?></title>
    <link rel="icon" type="image/png" href="<?= url('/assets/img/logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('/assets/css/app.css') ?>" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body, .font-sans { font-family: 'Poppins', sans-serif !important; }
        /* Base animations for modals and toasts */
        .fade-enter { opacity: 0; }
        .fade-enter-active { opacity: 1; transition: opacity 200ms ease-in; }
        .fade-exit { opacity: 1; }
        .fade-exit-active { opacity: 0; transition: opacity 200ms ease-out; }
    </style>
</head>
<body class="bg-background min-h-screen font-sans text-text-primary antialiased">
    
    <div class="h-screen flex overflow-hidden print:h-auto print:overflow-visible">
        <div class="print:hidden flex h-full">
            <?php require __DIR__ . '/../partials/sidebar.php'; ?>
        </div>
        
        <div class="flex flex-col w-0 flex-1 overflow-hidden print:w-full print:overflow-visible text-text-primary">
            <div class="print:hidden">
                <?php require __DIR__ . '/../partials/header.php'; ?>
            </div>
            
            <main class="flex-1 relative overflow-y-auto focus:outline-none print:overflow-visible text-text-primary">
                <div class="py-6 print:py-0">
                    
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-6">
                        <?php require __DIR__ . '/../partials/alerts.php'; ?>
                        <?= $content ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Global Partials -->
    <?php require __DIR__ . '/../partials/toast.php'; ?>
    <?php require __DIR__ . '/../partials/confirm-dialog.php'; ?>
    <?php require __DIR__ . '/../partials/alert-modal.php'; ?>

    <script src="<?= url('/assets/js/app.js') ?>"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
