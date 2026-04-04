<?php
declare(strict_types=1);

/**
 * Paramètres URL attendus :
 * ?action=click-left&items=6&size=100
 * ou
 * ?action=clic-gauche&items=6&size=100
 */

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'click-left';
$items  = isset($_GET['items']) ? (int) $_GET['items'] : 6;
$size   = isset($_GET['size']) ? (int) $_GET['size'] : 100;

// Sécurisation simple
if ($items < 1) {
    $items = 1;
}
if ($items > 100) {
    $items = 100;
}

if ($size < 40) {
    $size = 40;
}
if ($size > 200) {
    $size = 200;
}

// Alias d'actions acceptés
$normalizedAction = strtolower($action);
$isLeftClickExercise = in_array($normalizedAction, ['click-left', 'clic-gauche', 'clic-gauche'], true);

// Textes affichés
$pageTitle = 'Exercices souris';

if ($isLeftClickExercise) {
    $exerciseTitle = 'Clic gauche';
    $exerciseInstruction = 'Placez l’index sur le bouton gauche de la souris, puis cliquez sur chaque smiley pour le faire disparaître.';
} else {
    $exerciseTitle = 'Exercice inconnu';
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #4caf50;
            color: #222;
        }

        .site-header {
            background: #1e88e5;
            color: #fff;
            padding: 18px 20px;
            text-align: center;
            font-size: 32px;
            font-weight: bold;
        }

        .page-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px 40px;
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        .main-content {
            flex: 1;
            min-width: 0;
        }

        .sidebar {
            width: 320px;
            background: #8d6e63;
            color: #fff;
            border: 2px solid rgba(0, 0, 0, 0.15);
            padding: 20px;
        }

        .sidebar h2 {
            margin-top: 0;
            text-align: center;
            font-size: 30px;
            font-weight: normal;
        }

        .sidebar-box {
            background: rgba(255, 255, 255, 0.75);
            color: #333;
            border: 1px solid rgba(0, 0, 0, 0.2);
            padding: 15px;
        }

        .sidebar-box p {
            margin: 0 0 12px;
            font-size: 20px;
            line-height: 1.4;
        }

        .card {
            background: #f4f4f4;
            border: 1px solid #cfcfcf;
            margin-bottom: 20px;
            padding: 22px;
        }

        .exercise-title {
            text-align: center;
            font-size: 38px;
            color: #2e7d32;
            font-weight: bold;
            margin: 0;
        }

        .instruction {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            line-height: 1.5;
            margin: 0;
        }

        .exercise-zone {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            justify-items: center;
            align-items: center;
            margin-top: 10px;
        }

        .smiley-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            user-select: none;
        }

        .action-label {
            background: #4caf50;
            color: #fff;
            border: 2px solid #2e7d32;
            border-radius: 6px;
            padding: 10px 18px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            width: 100%;
            max-width: 220px;
        }

        .smiley-btn {
            border: none;
            background: transparent;
            padding: 0;
            margin: 0;
            cursor: pointer;
            line-height: 1;
            transition: transform 0.1s ease;
        }

        .smiley-btn:hover {
            transform: scale(1.05);
        }

        .smiley-btn:focus {
            outline: 4px solid #1e88e5;
            outline-offset: 6px;
            border-radius: 50%;
        }

        .smiley {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #ff9a9a 0%, #ff0000 70%);
            box-shadow: inset 0 6px 10px rgba(255,255,255,0.5), 0 8px 10px rgba(0,0,0,0.2);
            position: relative;
        }

        .smiley-face {
            position: relative;
            width: 70%;
            height: 70%;
        }

        .eye {
            position: absolute;
            top: 24%;
            width: 18%;
            height: 18%;
            background: #fff;
            border-radius: 50%;
            border: 2px solid rgba(0,0,0,0.15);
        }

        .eye.left {
            left: 18%;
        }

        .eye.right {
            right: 18%;
        }

        .eye::after {
            content: '';
            position: absolute;
            width: 35%;
            height: 35%;
            background: #111;
            border-radius: 50%;
            top: 38%;
            left: 38%;
        }

        .mouth {
            position: absolute;
            left: 50%;
            bottom: 18%;
            width: 42%;
            height: 22%;
            border-bottom: 4px solid #111;
            border-radius: 0 0 50px 50px;
            transform: translateX(-50%);
        }

        .controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            padding-top: 10px;
        }

        .btn {
            display: inline-block;
            border: none;
            padding: 14px 22px;
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            border-radius: 6px;
        }

        .btn-orange {
            background: #ff6f2c;
            color: #fff;
        }

        .btn-green {
            background: #4caf50;
            color: #fff;
        }

        .status-box {
            margin-top: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .success-message {
            display: none;
            margin-top: 20px;
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            color: #2e7d32;
        }

        .footer {
            background: #795548;
            color: #fff;
            text-align: center;
            padding: 18px;
            font-size: 18px;
        }

        @media (max-width: 1024px) {
            .page-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .site-header {
                font-size: 24px;
            }

            .exercise-title {
                font-size: 30px;
            }

            .instruction {
                font-size: 22px;
            }

            .action-label,
            .btn,
            .status-box {
                font-size: 20px;
            }
        }
    </style>
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
                (x <?= (int) $items ?>)
            </h1>
        </section>

        <section class="card">
            <p class="instruction">
                <?= htmlspecialchars($exerciseInstruction, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </section>

        <section class="card">
            <?php if ($isLeftClickExercise): ?>
                <div class="exercise-zone" id="exercise-zone">
                    <?php for ($i = 1; $i <= $items; $i++): ?>
                        <div class="smiley-item" data-item>
                            <div class="action-label">Cliquez sur moi !</div>

                            <button
                                type="button"
                                class="smiley-btn"
                                data-action="click-left"
                                aria-label="Smiley <?= $i ?>"
                            >
                                    <span
                                        class="smiley"
                                        style="width: <?= (int) $size ?>px; height: <?= (int) $size ?>px;"
                                    >
                                        <span class="smiley-face">
                                            <span class="eye left"></span>
                                            <span class="eye right"></span>
                                            <span class="mouth"></span>
                                        </span>
                                    </span>
                            </button>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="status-box">
                    Restants : <span id="remaining-count"><?= (int) $items ?></span> / <?= (int) $items ?>
                </div>

                <div class="success-message" id="success-message">
                    Bravo ! Tous les smileys ont été supprimés.
                </div>
            <?php else: ?>
                <div class="status-box">
                    Action non reconnue.
                </div>
            <?php endif; ?>
        </section>

        <section class="card">
            <div class="controls">
                <a
                    class="btn btn-orange"
                    href="?action=<?= urlencode($action) ?>&items=<?= (int) $items ?>&size=<?= (int) $size ?>"
                >
                    Recommencer
                </a>

                <a
                    class="btn btn-green"
                    href="?action=click-left&items=12&size=100"
                >
                    Exemple suivant
                </a>
            </div>
        </section>
    </main>

    <aside class="sidebar">
        <h2>Informations</h2>
        <div class="sidebar-box">
            <p><strong>Action :</strong> <?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Nombre d’items :</strong> <?= (int) $items ?></p>
            <p><strong>Taille :</strong> <?= (int) $size ?> px</p>
            <p><strong>But :</strong> cliquer avec le bouton gauche.</p>
        </div>
    </aside>
</div>

<footer class="footer">
    Exercice de souris pour apprentissage
</footer>

<script>
    (function () {
        'use strict';

        const zone = document.getElementById('exercise-zone');
        const remainingCount = document.getElementById('remaining-count');
        const successMessage = document.getElementById('success-message');

        if (!zone) {
            return;
        }

        let remaining = zone.querySelectorAll('[data-item]').length;

        zone.addEventListener('click', function (event) {
            const button = event.target.closest('.smiley-btn');

            if (!button) {
                return;
            }

            const item = button.closest('[data-item]');
            if (!item) {
                return;
            }

            item.remove();
            remaining--;

            if (remainingCount) {
                remainingCount.textContent = String(remaining);
            }

            if (remaining <= 0 && successMessage) {
                successMessage.style.display = 'block';
            }
        });

        zone.addEventListener('contextmenu', function (event) {
            const button = event.target.closest('.smiley-btn');

            if (!button) {
                return;
            }

            // Empêche le clic droit de supprimer l’élément
            event.preventDefault();
        });
    })();
</script>

</body>
</html>