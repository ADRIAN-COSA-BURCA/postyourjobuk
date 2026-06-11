<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="/assets/css/main.css?v=2">
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main class="page-wrapper">
        <?= $view_content ?? '' ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>