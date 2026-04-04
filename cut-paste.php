<?php
declare(strict_types=1);

/**
 * Exemple d'URL :
 * cut-paste.php?action=cut-paste&items=2&size=100
 * cut-paste.php?action=couper-coller&items=8&size=120
 */

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'cut-paste';
$items  = isset($_GET['items']) ? (int) $_GET['items'] : 2;
$size   = isset($_GET['size']) ? (int) $_GET['size'] : 100;
$mode   = isset($_GET['mode']) ? (string) $_GET['mode'] : 'classic';

if ($items < 1) {
    $items = 1;
}
if ($items > 24) {
    $items = 24;
}

if ($size < 40) {
    $size = 40;
}
if ($size > 200) {
    $size = 200;
}

$normalizedAction = strtolower($action);
$isCutPasteExercise = in_array($normalizedAction, ['cut-paste', 'couper-coller'], true);
require_once __DIR__ . '/exercise-menu.php';
$mode = normalizeExerciseMode($mode);
$chronoDelay = getChronoDelayForAction('cut-paste', $mode);
$countdownSeconds = $chronoDelay !== null ? $items * $chronoDelay : null;
$countdownDisplay = $countdownSeconds !== null
    ? (string) max(0, (int) round($countdownSeconds))
    : null;
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'cut-paste', $items, $mode);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
        'mode' => $mode,
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'cut-paste', $items, $mode);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
        'mode' => $mode,
    ])
    : null;

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Couper / coller';

if ($isCutPasteExercise) {
    $exerciseInstruction = 'Clic droit sur le smiley, choisissez “Couper”. Puis faites un clic droit sur “Collez-moi !” et choisissez “Coller”.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isCutPasteExercise): ?>
    <div class="copy-paste-wrapper" id="cut-paste-exercise">
        <?php if ($countdownSeconds !== null): ?>
            <div class="status-box completion-hideable">
                Temps : <span id="countdown-value"><?= htmlspecialchars((string) $countdownDisplay, ENT_QUOTES, 'UTF-8') ?></span>s
                <div class="countdown-progress" aria-hidden="true">
                    <div class="countdown-progress-bar" id="countdown-progress"></div>
                </div>
            </div>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $items; $i++): ?>
            <div class="copy-paste-row completion-hideable" data-row data-id="item-<?= $i ?>">
                <div class="copy-paste-side copy-origin" data-copy-origin>
                    <div class="copy-paste-label">Coupez-moi !</div>

                    <div class="copy-paste-target">
                        <button
                            type="button"
                            class="smiley-btn"
                            data-cut-source="item-<?= $i ?>"
                            aria-label="Smiley à couper <?= $i ?>"
                        >
                            <span
                                class="smiley is-purple"
                                style="width: <?= (int) $size ?>px; height: <?= (int) $size ?>px;"
                            >
                                <span class="smiley-face">
                                    <span class="eye left"></span>
                                    <span class="eye right"></span>
                                    <span class="mouth" style="border-bottom:none;border-top:4px solid #111;border-radius:50px 50px 0 0;bottom:28%;"></span>
                                </span>
                            </span>
                        </button>

                        <div class="copy-menu" data-copy-menu hidden>
                            <button type="button" class="copy-menu-item" disabled>Nouveau</button>
                            <button type="button" class="copy-menu-item" data-copy-command="cut">Couper</button>
                            <button type="button" class="copy-menu-item" disabled>Copier</button>
                            <button type="button" class="copy-menu-item" disabled>Renommer</button>
                            <button type="button" class="copy-menu-item is-separator-top" disabled>Supprimer</button>
                        </div>
                    </div>
                </div>

                <div class="copy-paste-side copy-destination" data-copy-destination>
                    <div class="copy-paste-label">Collez-moi !</div>

                    <div class="copy-paste-target">
                        <div class="paste-slot" data-paste-slot></div>

                        <div class="copy-menu" data-paste-menu hidden>
                            <button type="button" class="copy-menu-item" disabled>Annuler</button>
                            <button type="button" class="copy-menu-item is-disabled" data-paste-command="paste">Coller</button>
                            <button type="button" class="copy-menu-item" disabled>Propriétés</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>

        <div class="status-box completion-hideable remaining-box" aria-hidden="true">
            Restants : <span id="remaining-count"><?= (int) $items ?></span> / <?= (int) $items ?>
        </div>
        <div class="menu-note completion-hideable" id="copy-note">
            Astuce : clic droit sur le smiley, choisissez “Couper”, puis clic droit sur “Collez-moi !” et choisissez “Coller”.
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

            const exercise = document.getElementById('cut-paste-exercise');
            const remainingCount = document.getElementById('remaining-count');
            const copyNote = document.getElementById('copy-note');
            const nextStepButton = document.getElementById('next-step-button');
            const instruction = document.querySelector('.instruction');
            const countdownValue = document.getElementById('countdown-value');
            const countdownProgress = document.getElementById('countdown-progress');
            const restartButton = exercise.querySelector('.btn-orange');

            if (!exercise) {
                return;
            }

            let cutId = null;
            let remaining = exercise.querySelectorAll('[data-row]').length;
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
                    instruction.textContent = 'Bravo ! Tous les smileys ont été coupés puis collés.';
                    instruction.classList.remove('is-timeout-feedback');
                    instruction.classList.add('is-success-feedback');
                }
            }

            function clearCutPreview() {
                exercise.querySelectorAll('[data-cut-source].is-cut').forEach(function (button) {
                    button.classList.remove('is-cut');
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
                    button.classList.add('is-disabled');
                    button.setAttribute('aria-disabled', 'true');
                    button.setAttribute('tabindex', '-1');
                });
                if (instruction) {
                    instruction.textContent = 'Temps écoulé. Vous pouvez uniquement recommencer.';
                    instruction.classList.remove('is-success-feedback');
                    instruction.classList.add('is-timeout-feedback');
                }
                if (copyNote) {
                    copyNote.textContent = 'Temps écoulé.';
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

            function closeAllMenus() {
                exercise.querySelectorAll('[data-copy-menu], [data-paste-menu]').forEach(function (menu) {
                    menu.hidden = true;
                });
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
                if (exercise.contains(event.target)) {
                    event.preventDefault();
                }
            }, true);

            function canPasteInRow(row) {
                if (!row || !cutId) {
                    return false;
                }

                const expectedId = row.getAttribute('data-id');
                if (!expectedId || expectedId !== cutId) {
                    return false;
                }

                const origin = row.querySelector('[data-copy-origin]');
                return !origin || !origin.classList.contains('is-done');
            }

            function updatePasteMenus() {
                exercise.querySelectorAll('[data-paste-command="paste"]').forEach(function (button) {
                    const row = button.closest('[data-row]');
                    if (canPasteInRow(row)) {
                        button.classList.remove('is-disabled');
                        button.disabled = false;
                    } else {
                        button.classList.add('is-disabled');
                        button.disabled = true;
                    }
                });
            }

            exercise.addEventListener('contextmenu', function (event) {
                const sourceButton = event.target.closest('[data-cut-source]');
                const pasteArea = event.target.closest('[data-copy-destination] .copy-paste-target, [data-paste-slot]');

                event.preventDefault();
                if (isGameOver || isCompleted) {
                    return;
                }

                if (!sourceButton && !pasteArea) {
                    closeAllMenus();
                    return;
                }

                closeAllMenus();

                if (sourceButton) {
                    const row = sourceButton.closest('[data-row]');
                    if (!row) {
                        return;
                    }

                    const origin = row.querySelector('[data-copy-origin]');
                    if (!origin || origin.classList.contains('is-done')) {
                        return;
                    }

                    const menu = row.querySelector('[data-copy-menu]');
                    if (menu) {
                        positionMenuAtClick(menu, event);
                    }

                    if (copyNote) {
                        copyNote.textContent = 'Cliquez maintenant sur “Couper”.';
                    }

                    return;
                }

                const target = event.target.closest('[data-copy-destination]');
                if (!target) {
                    return;
                }

                const menu = target.querySelector('[data-paste-menu]');
                if (!menu) {
                    return;
                }

                updatePasteMenus();
                positionMenuAtClick(menu, event);

                if (copyNote) {
                    copyNote.textContent = cutId
                        ? 'Cliquez maintenant sur “Coller”.'
                        : 'Il faut d’abord couper un smiley.';
                }
            });

            exercise.addEventListener('click', function (event) {
                if (isGameOver || isCompleted) {
                    return;
                }
                const cutButton = event.target.closest('[data-copy-command="cut"]');
                if (cutButton) {
                    const row = cutButton.closest('[data-row]');
                    if (!row) {
                        return;
                    }

                    const source = row.querySelector('[data-cut-source]');
                    const origin = row.querySelector('[data-copy-origin]');
                    const menu = row.querySelector('[data-copy-menu]');

                    if (!source || !origin || origin.classList.contains('is-done')) {
                        return;
                    }

                    clearCutPreview();
                    cutId = source.getAttribute('data-cut-source');
                    source.classList.add('is-cut');

                    if (menu) {
                        menu.hidden = true;
                    }

                    updatePasteMenus();

                    if (copyNote) {
                        copyNote.textContent = 'Très bien. Faites maintenant un clic droit sur “Collez-moi !”.';
                    }

                    return;
                }

                const pasteButton = event.target.closest('[data-paste-command="paste"]');
                if (pasteButton) {
                    if (!cutId) {
                        return;
                    }

                    const row = pasteButton.closest('[data-row]');
                    if (!row) {
                        return;
                    }

                    const expectedId = row.getAttribute('data-id');
                    const slot = row.querySelector('[data-paste-slot]');
                    const pasteMenu = row.querySelector('[data-paste-menu]');
                    const origin = row.querySelector('[data-copy-origin]');
                    const source = row.querySelector('[data-cut-source]');

                    if (!expectedId || !slot || !origin || !source || origin.classList.contains('is-done')) {
                        return;
                    }

                    if (cutId !== expectedId) {
                        if (pasteMenu) {
                            pasteMenu.hidden = true;
                        }
                        if (copyNote) {
                            copyNote.textContent = 'Ce n’est pas le bon smiley pour cette ligne. Recommencez.';
                        }
                        return;
                    }

                    slot.innerHTML = '';
                    slot.appendChild(source);
                    source.removeAttribute('data-cut-source');
                    source.classList.remove('is-cut');
                    source.disabled = true;
                    source.setAttribute('aria-hidden', 'true');
                    slot.classList.add('is-filled');

                    origin.classList.add('is-done');

                    if (pasteMenu) {
                        pasteMenu.hidden = true;
                    }

                    cutId = null;
                    updatePasteMenus();
                    remaining--;

                    if (remainingCount) {
                        remainingCount.textContent = String(remaining);
                    }

                    if (copyNote && remaining > 0) {
                        copyNote.textContent = 'Très bien. Passez à la ligne suivante.';
                    }

                    if (remaining <= 0) {
                        completeExercise();
                        if (copyNote) {
                            copyNote.textContent = 'Exercice terminé. Vous pouvez passer à l’étape suivante.';
                        }
                    }

                    return;
                }

                if (!event.target.closest('.copy-menu') && !event.target.closest('[data-cut-source]') && !event.target.closest('[data-copy-destination]')) {
                    closeAllMenus();
                }
            });

            document.addEventListener('click', function (event) {
                if (!exercise.contains(event.target)) {
                    closeAllMenus();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeAllMenus();
                }
            });

            updatePasteMenus();
        })();
    </script>
<?php else: ?>
    <div class="error-box">
        Action non reconnue.
    </div>
<?php endif;

$mainContent = (string) ob_get_clean();

require __DIR__ . '/template.php';
