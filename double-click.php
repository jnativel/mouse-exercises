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
$mode   = isset($_GET['mode']) ? (string) $_GET['mode'] : 'classic';

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
$mode = normalizeExerciseMode($mode);
$chronoDelay = getChronoDelayForAction('double-click', $mode);
$countdownSeconds = $chronoDelay !== null ? $items * $chronoDelay : null;
$countdownDisplay = $countdownSeconds !== null
    ? (string) max(0, (int) round($countdownSeconds))
    : null;
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'double-click', $items, $mode);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
        'mode' => $mode,
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'double-click', $items, $mode);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
        'mode' => $mode,
    ])
    : null;

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Double clic';

if ($isDoubleClickExercise) {
    $exerciseInstruction = 'Placez l’index sur le bouton gauche de la souris, puis faites un double clic sur chaque smiley pour le faire disparaître.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isDoubleClickExercise): ?>
    <div id="double-click-exercise">

    <?php if ($countdownSeconds !== null): ?>
        <div class="status-box completion-hideable">
            Temps : <span id="countdown-value"><?= htmlspecialchars((string) $countdownDisplay, ENT_QUOTES, 'UTF-8') ?></span>s
            <div class="countdown-progress" aria-hidden="true">
                <div class="countdown-progress-bar" id="countdown-progress"></div>
            </div>
        </div>
        <?php endif; ?>

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

    <div class="status-box completion-hideable remaining-box" aria-hidden="true">
        Restants : <span id="remaining-count"><?= (int) $items ?></span> / <?= (int) $items ?>
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
            href="?action=<?= urlencode($action) ?>&items=<?= (int) $items ?>&size=<?= (int) $size ?>&mode=<?= urlencode($mode) ?>"
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
            const nextStepButton = document.getElementById('next-step-button');
            const instruction = document.querySelector('.instruction');
            const countdownValue = document.getElementById('countdown-value');
            const countdownProgress = document.getElementById('countdown-progress');
            const restartButton = exercise.querySelector('.btn-orange');

            if (!zone || !exercise) {
                return;
            }

            let remaining = zone.querySelectorAll('[data-item]').length;
            let isGameOver = false;
            let isCompleted = false;
            let timerId = null;
            let remainingSeconds = <?= $countdownSeconds !== null ? (float) $countdownSeconds : 'null' ?>;
            const initialSeconds = typeof remainingSeconds === 'number' ? remainingSeconds : null;

            function enableNextStep() {
                if (!nextStepButton) {
                    return;
                }

                nextStepButton.classList.remove('is-disabled');
                nextStepButton.removeAttribute('aria-disabled');
                nextStepButton.removeAttribute('tabindex');
            }

            function completeExercise() {
                if (isGameOver || isCompleted) {
                    return;
                }
                isCompleted = true;
                if (timerId !== null) {
                    window.clearInterval(timerId);
                }
                enableNextStep();
                if (instruction) {
                    instruction.textContent = 'Bravo ! Tous les smileys ont été double-cliqués.';
                    instruction.classList.remove('is-timeout-feedback');
                    instruction.classList.add('is-success-feedback');
                }
            }

            function handleGameOver() {
                if (isCompleted || isGameOver) {
                    return;
                }
                isGameOver = true;
                if (timerId !== null) {
                    window.clearInterval(timerId);
                }
                exercise.querySelectorAll('.controls .btn').forEach(function (button) {
                    if (button === restartButton) {
                        return;
                    }
                    button.classList.add('is-disabled');
                    button.setAttribute('aria-disabled', 'true');
                    button.setAttribute('tabindex', '-1');
                });
                if (instruction) {
                    instruction.textContent = 'Temps écoulé. Vous pouvez uniquement recommencer.';
                    instruction.classList.remove('is-success-feedback');
                    instruction.classList.add('is-timeout-feedback');
                }
            }

            function formatSeconds(value) {
                const normalizedValue = Number.isFinite(value) ? Math.max(0, value) : 0;
                return String(Math.round(normalizedValue));
            }

            function updateProgressBar() {
                if (!countdownProgress || typeof initialSeconds !== 'number' || initialSeconds <= 0) {
                    return;
                }

                const ratio = Math.max(0, Math.min(1, remainingSeconds / initialSeconds));
                countdownProgress.style.width = (ratio * 100).toFixed(1) + '%';
            }

            if (typeof remainingSeconds === 'number' && countdownValue) {
                updateProgressBar();
                timerId = window.setInterval(function () {
                    if (isCompleted || isGameOver) {
                        return;
                    }
                    remainingSeconds = Math.max(0, Number((remainingSeconds - 0.1).toFixed(1)));
                    countdownValue.textContent = formatSeconds(remainingSeconds);
                    updateProgressBar();
                    if (remainingSeconds <= 0) {
                        handleGameOver();
                    }
                }, 100);
            }

            zone.addEventListener('dblclick', function (event) {
                if (isGameOver || isCompleted) {
                    return;
                }
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

                if (remaining <= 0) {
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
