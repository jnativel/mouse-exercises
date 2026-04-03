<?php
declare(strict_types=1);

/**
 * Template principal pour les exercices souris.
 *
 * Variables attendues :
 * - $pageTitle (string)
 * - $exerciseTitle (string)
 * - $exerciseInstruction (string)
 * - $sidebarTitle (string)
 * - $sidebarContent (string HTML)
 * - $mainContent (string HTML)
 */

$pageTitle = $pageTitle ?? 'Exercices souris';
$exerciseTitle = $exerciseTitle ?? 'Exercice';
$exerciseInstruction = $exerciseInstruction ?? '';
$sidebarTitle = $sidebarTitle ?? 'Informations';
$sidebarContent = $sidebarContent ?? '';
$mainContent = $mainContent ?? '';

require_once __DIR__ . '/exercise-menu.php';
$exerciseMenu = renderExerciseMenu();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
</header>

<div class="page-wrapper">
    <main class="main-content">
        <section class="card">
            <h1 class="exercise-title">
                <?= htmlspecialchars($exerciseTitle, ENT_QUOTES, 'UTF-8') ?>
            </h1>
        </section>

        <section class="card">
            <p class="instruction">
                <?= htmlspecialchars($exerciseInstruction, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </section>

        <section class="card">
            <?= $mainContent ?>
        </section>
    </main>

    <aside class="sidebar">
        <h2><?= htmlspecialchars($sidebarTitle, ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="sidebar-box">
            <?= $sidebarContent ?>
        </div>

        <h2 class="sidebar-menu-title">Exercices</h2>
        <div class="sidebar-box">
            <?= $exerciseMenu ?>
        </div>
    </aside>
</div>

<footer class="footer">
    Exercices souris pour l’apprentissage
</footer>

</body>
</html>