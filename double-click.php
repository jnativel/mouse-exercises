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
require_once __DIR__ . '/exercise-menu.php';
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'double-click', $items);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'double-click', $items);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
    ])
    : null;

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Double clic (x ' . $items . ')';

if ($isDoubleClickExercise) {
    $exerciseInstruction = 'Placez l’index sur le bouton gauche de la souris, puis faites un double clic sur chaque smiley pour le faire disparaître.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isDoubleClickExercise): ?>
    <div id="double-click-exercise">
    <div class="exercise-zone completion-hideable" id="exercise-zone">
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

    <div class="status-box completion-hideable">
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
                class="btn btn-green is-disabled"
                id="next-step-button"
                href="<?= htmlspecialchars($nextHref, ENT_QUOTES, 'UTF-8') ?>"
                aria-disabled="true"
                tabindex="-1"
            >
                Étape suivante
            </a>
        <?php endif; ?>
    </div>
    </div>

    <script>
        (function () {
            'use strict';

            const exercise = document.getElementById('double-click-exercise');
            const zone = document.getElementById('exercise-zone');
            const remainingCount = document.getElementById('remaining-count');
            const successMessage = document.getElementById('success-message');
            const nextStepButton = document.getElementById('next-step-button');

            if (!zone || !exercise) {
                return;
            }

            let remaining = zone.querySelectorAll('[data-item]').length;

            function enableNextStep() {
                if (!nextStepButton) {
                    return;
                }

                nextStepButton.classList.remove('is-disabled');
                nextStepButton.removeAttribute('aria-disabled');
                nextStepButton.removeAttribute('tabindex');
            }

            function completeExercise() {
                exercise.classList.add('exercise-complete');
                enableNextStep();
            }

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
                    completeExercise();
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
