<?php
declare(strict_types=1);

/**
 * Exemple d'URL :
 * right-click.php?action=right-click&items=6&size=100
 * right-click.php?action=clic-droit&items=12&size=120
 */

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'right-click';
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
$isRightClickExercise = in_array($normalizedAction, ['right-click', 'clic-droit', 'click-right', 'clique-droit'], true);
require_once __DIR__ . '/exercise-menu.php';
$mode = normalizeExerciseMode($mode);
$chronoDelay = getChronoDelayForAction('right-click', $mode);
$countdownSeconds = $chronoDelay !== null ? $items * $chronoDelay : null;
$countdownDisplay = $countdownSeconds !== null
    ? (string) max(0, (int) round($countdownSeconds))
    : null;
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'right-click', $items, $mode);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
        'mode' => $mode,
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'right-click', $items, $mode);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
        'mode' => $mode,
    ])
    : null;
$showPreviousStepButton = $mode === 'classic';

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Clic droit';

if ($isRightClickExercise) {
    $exerciseInstruction = 'Faites un clic droit sur un smiley. Un menu va apparaître. Cliquez ensuite sur “Supprimer”.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isRightClickExercise): ?>
    <div id="right-click-exercise">

    <?php if ($countdownSeconds !== null): ?>
        <div class="status-box completion-hideable">
            Temps : <span id="countdown-value"><?= htmlspecialchars((string) $countdownDisplay, ENT_QUOTES, 'UTF-8') ?></span>s
            <div class="countdown-progress" aria-hidden="true">
                <div class="countdown-progress-bar" id="countdown-progress"></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="menu-note completion-hideable" id="menu-note">
            Astuce : faites d’abord un clic droit sur le smiley.
        </div>

    <div class="exercise-zone completion-hideable" id="exercise-zone">
        <?php for ($i = 1; $i <= $items; $i++): ?>
            <div class="smiley-item context-target" data-item>
                <div class="smiley-helper">Supprimez-moi !</div>

                <button
                    type="button"
                    class="smiley-btn"
                    data-action="right-click"
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

                <div class="fake-context-menu" hidden>
                    <button type="button" class="fake-context-item" disabled>Nouveau</button>
                    <button type="button" class="fake-context-item" disabled>Couper</button>
                    <button type="button" class="fake-context-item" disabled>Copier</button>
                    <button type="button" class="fake-context-item" disabled>Renommer</button>
                    <button type="button" class="fake-context-item is-delete" data-delete>Supprimer</button>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="status-box completion-hideable remaining-box" aria-hidden="true">
        Restants : <span id="remaining-count"><?= (int) $items ?></span> / <?= (int) $items ?>
    </div>

    <div class="controls">
        <?php if ($showPreviousStepButton && $previousHref !== null): ?>
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
                class="btn btn-green is-hidden"
                id="next-step-button"
                href="<?= htmlspecialchars($nextHref, ENT_QUOTES, 'UTF-8') ?>"
            >
                Étape suivante
            </a>
        <?php endif; ?>
    </div>


    </div>

    <script>
        (function () {
            'use strict';

            const exercise = document.getElementById('right-click-exercise');
            const zone = document.getElementById('exercise-zone');
            const remainingCount = document.getElementById('remaining-count');
            const menuNote = document.getElementById('menu-note');
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

                nextStepButton.classList.remove('is-hidden');
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
                    instruction.textContent = 'Bravo ! Tous les smileys ont été supprimés.';
                    instruction.classList.remove('is-timeout-feedback');
                    instruction.classList.add('is-success-feedback');
                }
            }

            function closeAllMenus() {
                zone.querySelectorAll('.fake-context-menu').forEach(function (menu) {
                    menu.hidden = true;
                });
            }

            function handleGameOver() {
                if (isCompleted || isGameOver) {
                    return;
                }
                isGameOver = true;
                if (timerId !== null) {
                    window.clearInterval(timerId);
                }
                closeAllMenus();
                exercise.querySelectorAll('.controls .btn').forEach(function (button) {
                    if (button === restartButton) {
                        return;
                    }
                    button.classList.add('is-hidden');
                    button.setAttribute('aria-hidden', 'true');
                });
                if (instruction) {
                    instruction.textContent = 'Temps écoulé. Vous pouvez recommencer.';
                    instruction.classList.remove('is-success-feedback');
                    instruction.classList.add('is-timeout-feedback');
                }
                if (menuNote) {
                    menuNote.textContent = 'Temps écoulé.';
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

            function positionMenuAtClick(menu, event) {
                menu.hidden = false;

                const menuWidth = menu.offsetWidth;
                const menuHeight = menu.offsetHeight;
                const margin = 4;

                const maxX = Math.max(margin, window.innerWidth - menuWidth - margin);
                const maxY = Math.max(margin, window.innerHeight - menuHeight - margin);

                const left = Math.max(margin, Math.min(event.clientX, maxX));
                const top = Math.max(margin, Math.min(event.clientY, maxY));

                menu.style.left = left + 'px';
                menu.style.top = top + 'px';
            }

            document.addEventListener('contextmenu', function (event) {
                if (zone.contains(event.target)) {
                    event.preventDefault();
                }
            }, true);

            zone.addEventListener('contextmenu', function (event) {
                if (isGameOver || isCompleted) {
                    return;
                }
                event.preventDefault();

                const button = event.target.closest('.smiley-btn');

                if (!button) {
                    closeAllMenus();
                    return;
                }

                const item = button.closest('[data-item]');
                if (!item || item.classList.contains('is-done')) {
                    return;
                }

                const menu = item.querySelector('.fake-context-menu');
                if (!menu) {
                    return;
                }

                const wasHidden = menu.hidden;
                closeAllMenus();
                if (!wasHidden) {
                    menu.hidden = true;
                } else {
                    positionMenuAtClick(menu, event);
                }

                if (menuNote) {
                    menuNote.textContent = 'Cliquez maintenant sur “Supprimer”.';
                }
            });

            zone.addEventListener('click', function (event) {
                if (isGameOver || isCompleted) {
                    return;
                }
                const deleteButton = event.target.closest('[data-delete]');

                if (deleteButton) {
                    const item = deleteButton.closest('[data-item]');
                    if (!item || item.classList.contains('is-done')) {
                        return;
                    }

                    item.classList.add('is-done');
                    const menu = item.querySelector('.fake-context-menu');
                    if (menu) {
                        menu.hidden = true;
                    }

                    remaining--;

                    if (remainingCount) {
                        remainingCount.textContent = String(remaining);
                    }

                    if (menuNote && remaining > 0) {
                        menuNote.textContent = 'Très bien. Recommencez : clic droit puis “Supprimer”.';
                    }

                    if (remaining <= 0) {
                        completeExercise();
                        if (menuNote) {
                            menuNote.textContent = 'Exercice terminé. Vous pouvez passer à l’étape suivante.';
                        }
                    }

                    return;
                }

                const clickedMenu = event.target.closest('.fake-context-menu');
                const clickedButton = event.target.closest('.smiley-btn');

                if (!clickedMenu && !clickedButton) {
                    closeAllMenus();
                }
            });

            document.addEventListener('click', function (event) {
                if (!zone.contains(event.target)) {
                    closeAllMenus();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeAllMenus();
                }
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
