<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Print') ?></title>
    <link href="<?= url('/assets/css/app.css') ?>" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <?php if (!empty($printStyles)): ?>
    <style><?= $printStyles ?></style>
    <?php endif; ?>
</head>
<body class="bg-white">
<?= $content ?>
</body>
</html>
