<?php if (!defined('APP_NAME')) require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>
<div class="app-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1><?= htmlspecialchars($pageTitle ?? '') ?></h1>
        </div>
        <div class="page-body">
