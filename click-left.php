<?php
declare(strict_types=1);

/**
 * Exemple d'URL :
 * click-left.php?action=click-left&items=6&size=100
 * click-left.php?action=clic-gauche&items=12&size=120
 */

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'click-left';
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
$isLeftClickExercise = in_array($normalizedAction, ['click-left', 'clic-gauche', 'clique-gauche'], true);
require_once __DIR__ . '/exercise-menu.php';
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'click-left', $items);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'click-left', $items);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
    ])
    : null;

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Clic gauche (x ' . $items . ')';

if ($isLeftClickExercise) {
    $exerciseInstruction = 'Placez l’index sur le bouton gauche de la souris, puis cliquez sur chaque smiley pour le faire disparaître.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isLeftClickExercise): ?>
    <div class="exercise-zone" id="exercise-zone">
        <?php for ($i = 1; $i <= $items; $i++): ?>
            <div class="smiley-item" data-item>
                <div class="smiley-helper">Supprimez-moi !</div>

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

    <div class="controls">
        <?php if ($previousHref !== null): ?>
            <a
                class="btn btn-green"
                href="<?= htmlspecialchars($previousHref, ENT_QUOTES, 'UTF-8') ?>"
            >
                Étape précédente
            </a>
        <?php endif; ?>

        <a
            class="btn btn-orange"
            href="?action=<?= urlencode($action) ?>&items=<?= (int) $items ?>&size=<?= (int) $size ?>"
        >
            Recommencer
        </a>

        <?php if ($nextHref !== null): ?>
            <a
                class="btn btn-green"
                href="<?= htmlspecialchars($nextHref, ENT_QUOTES, 'UTF-8') ?>"
            >
                Étape suivante
            </a>
        <?php endif; ?>
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

            zone.addEventListener('click', function (event) {
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

require __DIR__ . '/template.php';

