<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Portal') ?></title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<header class="header">
    <div class="container">
        <a href="/portal/dashboard" class="logo">📋 PostYourJobHere.com</a>
        <nav class="nav">
            <?php if (isset($_SESSION['tenant_id'])): ?>
                <a href="/portal/dashboard" class="btn btn-ghost">Dashboard</a>
                <a href="/portal/logout" class="btn btn-ghost">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main-content">
    <div class="container">