<?php
declare(strict_types=1);

/**
 * Exemple d'URL :
 * double-click.php?action=double-click&items=6&size=100
 * double-click.php?action=double-clic&items=12&size=120
 */

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'double-click';
$items  = isset($_GET['items']) ? (int) $_GET['items'] : 6;
$size   = isset($_GET['size']) ? (int) $_GET['size'] : 100;

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

$normalizedAction = strtolower($action);
$isDoubleClickExercise = in_array($normalizedAction, ['double-click', 'double-clic', 'double-clic-gauche'], true);

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Double clic (x ' . $items . ')';

if ($isDoubleClickExercise) {
    $exerciseInstruction = 'Placez l’index sur le bouton gauche de la souris, puis faites un double clic sur chaque smiley pour le faire disparaître.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isDoubleClickExercise): ?>
    <div class="exercise-zone" id="exercise-zone">
        <?php for ($i = 1; $i <= $items; $i++): ?>
            <div class="smiley-item" data-item>
                <div class="smiley-helper">Double-cliquez sur moi !</div>

                <button
                    type="button"
                    class="smiley-btn"
                    data-action="double-click"
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

    <div class="controls">
        <a
            class="btn btn-orange"
            href="?action=<?= urlencode($action) ?>&items=<?= (int) $items ?>&size=<?= (int) $size ?>"
        >
            Recommencer
        </a>

        <a
            class="btn btn-green"
            href="?action=double-click&items=12&size=100"
        >
            Exemple suivant
        </a>
    </div>

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

            zone.addEventListener('dblclick', function (event) {
                const button = event.target.closest('.smiley-btn');

                if (!button) {
                    return;
                }

                const item = button.closest('[data-item]');
                if (!item) {
                    return;
                }

                if (item.classList.contains('is-done')) {
                    return;
                }

                item.classList.add('is-done');
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

                event.preventDefault();
            });
        })();
    </script>
<?php else: ?>
    <div class="error-box">
        Action non reconnue.
    </div>
<?php endif;

$mainContent = (string) ob_get_clean();

$sidebarTitle = 'Informations';
$sidebarContent = '
    <p><strong>Action :</strong> ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '</p>
    <p><strong>Nombre d’items :</strong> ' . $items . '</p>
    <p><strong>Taille :</strong> ' . $size . ' px</p>
    <p><strong>But :</strong> faire un double clic gauche.</p>
    <p><strong>Exemple :</strong> ?action=double-click&amp;items=6&amp;size=100</p>
';

require __DIR__ . '/template.php';