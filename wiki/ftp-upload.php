<?php
define('SECURE_ACCESS', true);

// Настройки статьи
$category = "Веб-хостинг";
$articleTitle = "Як завантажити сайт через FTP";
$articleDesc = "Покрокова інструкція по завантаженню вашого сайту через FTP клієнт.";
$lastUpdated = "08.08.2025"; // можно автоматизировать через filemtime

$pageTitle = "$articleTitle | База знань StormHosting UA";

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="/assets/css/pages/wiki.css">

<section class="wiki-article">
    <div class="container">
        
        <!-- Хлебные крошки -->
        <nav class="breadcrumbs">
            <a href="/pages/info/faq.php">📚 База знань</a> › 
            <a href="#"><?= htmlspecialchars($category) ?></a> › 
            <span><?= htmlspecialchars($articleTitle) ?></span>
        </nav>

        <!-- Заголовок -->
        <h1><?= htmlspecialchars($articleTitle) ?></h1>
        <p class="desc"><?= htmlspecialchars($articleDesc) ?></p>
        <p class="last-update">Оновлено: <?= $lastUpdated ?></p>

        <hr>

        <!-- Контент -->
        <h2>Крок 1: Завантаження FTP клієнта</h2>
        <p>Для початку роботи з FTP вам потрібно встановити FTP-клієнт, наприклад <strong>FileZilla</strong>.</p>

        <div class="note">
            <strong>Примітка:</strong> Завантажуйте лише з офіційних джерел.
        </div>

        <h2>Крок 2: Підключення до сервера</h2>
        <ul>
            <li>Відкрийте FTP-клієнт</li>
            <li>Введіть адресу сервера, логін і пароль</li>
            <li>Натисніть «Підключитися»</li>
        </ul>

        <div class="warning">
            <strong>Увага!</strong> Не використовуйте загальнодоступні комп’ютери для збереження паролів.
        </div>

        <h2>Крок 3: Завантаження файлів</h2>
        <p>Перетягніть файли у папку <code>public_html</code> на сервері.</p>

        <!-- Кнопка назад -->
        <a href="/pages/info/faq.php" class="btn-back">← Повернутись до бази знань</a>

    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
