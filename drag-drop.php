<?php
declare(strict_types=1);

/**
 * Exemple d'URL :
 * drag-drop.php?action=drag-drop&items=3&size=100
 * drag-drop.php?action=glisser-deposer&items=5&size=120
 */

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'drag-drop';
$items  = isset($_GET['items']) ? (int) $_GET['items'] : 3;
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
$isDragDropExercise = in_array($normalizedAction, ['drag-drop', 'glisser-deposer', 'glisser-déposer', 'drag-and-drop'], true);
require_once __DIR__ . '/exercise-menu.php';
$mode = normalizeExerciseMode($mode);
$chronoDelay = getChronoDelayForAction('drag-drop', $mode);
$countdownSeconds = $chronoDelay !== null ? $items * $chronoDelay : null;
$countdownDisplay = $countdownSeconds !== null
    ? (string) max(0, (int) round($countdownSeconds))
    : null;
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'drag-drop', $items);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
        'mode' => $mode,
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'drag-drop', $items);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
        'mode' => $mode,
    ])
    : null;

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Glisser / déposer (x ' . $items . ') — Mode ' . getExerciseModeLabel($mode);

if ($isDragDropExercise) {
    $exerciseInstruction = 'Index sur le bouton gauche de la souris, cliquez sur le smiley, gardez le clic appuyé, glissez vers la droite, puis relâchez dans la zone.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isDragDropExercise): ?>
    <div id="dragdrop-exercise">
        <?php for ($i = 1; $i <= $items; $i++): ?>
            <div class="dragdrop-row completion-hideable" data-row>
                <div class="dragdrop-side">
                    <div class="dragdrop-label">Glissez-moi !</div>

                    <div class="drag-smiley-origin">
                        <button
                            type="button"
                            class="smiley-btn drag-smiley"
                            draggable="true"
                            data-drag-id="smiley-<?= $i ?>"
                            aria-label="Smiley à glisser <?= $i ?>"
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
                    </div>
                </div>

                <div class="dragdrop-side">
                    <div class="dragdrop-label">Déposez-moi !</div>

                    <div class="drop-zone" data-drop-id="smiley-<?= $i ?>">
                        <div class="drop-zone-text">Déposez ici</div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>

        <div class="status-box completion-hideable">
            Restants : <span id="remaining-count"><?= (int) $items ?></span> / <?= (int) $items ?>
        </div>
        <?php if ($countdownSeconds !== null): ?>
        <div class="status-box completion-hideable">
            Temps : <span id="countdown-value"><?= htmlspecialchars((string) $countdownDisplay, ENT_QUOTES, 'UTF-8') ?></span>s
            <div class="countdown-progress" aria-hidden="true">
                <div class="countdown-progress-bar" id="countdown-progress"></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="menu-note completion-hideable" id="drag-note">
            Astuce : cliquez, gardez appuyé, déplacez, puis relâchez.
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

            const exercise = document.getElementById('dragdrop-exercise');
            const remainingCount = document.getElementById('remaining-count');
            const dragNote = document.getElementById('drag-note');
            const nextStepButton = document.getElementById('next-step-button');
            const instruction = document.querySelector('.instruction');
            const countdownValue = document.getElementById('countdown-value');
            const countdownProgress = document.getElementById('countdown-progress');
            const restartButton = exercise.querySelector('.btn-orange');

            if (!exercise) {
                return;
            }

            let draggedId = null;
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
                    instruction.textContent = 'Bravo ! Tous les smileys ont été déplacés.';
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
                }
                if (dragNote) {
                    dragNote.textContent = 'Temps écoulé.';
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

            exercise.querySelectorAll('.drag-smiley').forEach(function (dragItem) {
                dragItem.addEventListener('dragstart', function (event) {
                    if (isGameOver || isCompleted) {
                        event.preventDefault();
                        return;
                    }
                    draggedId = dragItem.getAttribute('data-drag-id');

                    if (event.dataTransfer) {
                        event.dataTransfer.setData('text/plain', draggedId);
                        event.dataTransfer.effectAllowed = 'move';
                    }
                });

                dragItem.addEventListener('dragend', function () {
                    exercise.querySelectorAll('.drop-zone').forEach(function (zone) {
                        zone.classList.remove('is-hover');
                    });
                });
            });

            exercise.querySelectorAll('.drop-zone').forEach(function (dropZone) {
                dropZone.addEventListener('dragover', function (event) {
                    if (isGameOver || isCompleted) {
                        return;
                    }
                    event.preventDefault();
                    dropZone.classList.add('is-hover');

                    if (event.dataTransfer) {
                        event.dataTransfer.dropEffect = 'move';
                    }
                });

                dropZone.addEventListener('dragleave', function () {
                    dropZone.classList.remove('is-hover');
                });

                dropZone.addEventListener('drop', function (event) {
                    if (isGameOver || isCompleted) {
                        return;
                    }
                    event.preventDefault();
                    dropZone.classList.remove('is-hover');

                    const expectedId = dropZone.getAttribute('data-drop-id');
                    const droppedId = draggedId || (event.dataTransfer ? event.dataTransfer.getData('text/plain') : '');

                    if (!expectedId || !droppedId || expectedId !== droppedId) {
                        if (dragNote) {
                            dragNote.textContent = 'Ce n’est pas la bonne zone. Essayez encore.';
                        }
                        return;
                    }

                    const source = exercise.querySelector('[data-drag-id="' + droppedId + '"]');
                    const row = dropZone.closest('[data-row]');

                    if (!source || !row || row.classList.contains('is-done')) {
                        return;
                    }

                    row.classList.add('is-done');
                    dropZone.classList.add('is-done');
                    dropZone.innerHTML = '';

                    const clone = source.cloneNode(true);
                    clone.removeAttribute('draggable');
                    clone.classList.remove('drag-smiley');
                    clone.disabled = true;
                    clone.setAttribute('aria-hidden', 'true');
                    dropZone.appendChild(clone);

                    remaining--;

                    if (remainingCount) {
                        remainingCount.textContent = String(remaining);
                    }

                    if (dragNote && remaining > 0) {
                        dragNote.textContent = 'Très bien. Recommencez avec le smiley suivant.';
                    }

                    if (remaining <= 0) {
                        completeExercise();
                        if (dragNote) {
                            dragNote.textContent = 'Exercice terminé. Vous pouvez passer à l’étape suivante.';
                        }
                    }
                });
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
