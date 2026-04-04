<?php
declare(strict_types=1);

/**
 * Template principal pour les exercices souris.
 *
 * Variables attendues :
 * - $pageTitle (string)
 * - $exerciseTitle (string)
 * - $exerciseInstruction (string)
 * - $mainContent (string HTML)
 */

$pageTitle = $pageTitle ?? 'Exercices souris';
$exerciseTitle = $exerciseTitle ?? 'Exercice';
$exerciseInstruction = $exerciseInstruction ?? '';
$mainContent = $mainContent ?? '';

require_once __DIR__ . '/exercise-menu.php';
$currentScript = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
$currentAction = isset($action) ? (string) $action : null;
$currentItems = isset($items) ? (int) $items : null;
$currentMode = isset($mode) ? (string) $mode : null;
$currentMode = normalizeExerciseMode($currentMode);
$isChronoMode = $currentMode !== 'classic';
$exerciseMenu = renderExerciseMenu($currentScript, $currentAction, $currentItems, $currentMode);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= $isChronoMode ? 'mode-chrono' : 'mode-classic' ?>">

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

        <section class="card" id="exercise-instruction-card">
            <p class="instruction">
                <?= htmlspecialchars($exerciseInstruction, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </section>

        <section class="card feedback-card is-hidden" id="exercise-feedback-card" aria-live="polite">
            <p class="feedback-message" id="exercise-feedback-message"></p>
        </section>

        <section class="card">
            <?= $mainContent ?>
        </section>
    </main>

    <aside class="sidebar">
        <h2>Exercices</h2>
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
