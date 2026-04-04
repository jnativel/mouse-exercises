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
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'drag-drop', $items);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'drag-drop', $items);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
    ])
    : null;

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Glisser / déposer (x ' . $items . ')';

if ($isDragDropExercise) {
    $exerciseInstruction = 'Index sur le bouton gauche de la souris, cliquez sur le smiley, gardez le clic appuyé, glissez vers la droite, puis relâchez dans la zone.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isDragDropExercise): ?>
    <div id="dragdrop-exercise">
        <?php for ($i = 1; $i <= $items; $i++): ?>
            <div class="dragdrop-row" data-row>
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

        <div class="status-box">
            Restants : <span id="remaining-count"><?= (int) $items ?></span> / <?= (int) $items ?>
        </div>

        <div class="menu-note" id="drag-note">
            Astuce : cliquez, gardez appuyé, déplacez, puis relâchez.
        </div>

        <div class="success-message" id="success-message">
            Bravo ! Tous les smileys ont été déplacés.
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
    </div>

    <script>
        (function () {
            'use strict';

            const exercise = document.getElementById('dragdrop-exercise');
            const remainingCount = document.getElementById('remaining-count');
            const successMessage = document.getElementById('success-message');
            const dragNote = document.getElementById('drag-note');

            if (!exercise) {
                return;
            }

            let draggedId = null;
            let remaining = exercise.querySelectorAll('[data-row]').length;

            exercise.querySelectorAll('.drag-smiley').forEach(function (dragItem) {
                dragItem.addEventListener('dragstart', function (event) {
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

                    if (remaining <= 0 && successMessage) {
                        successMessage.style.display = 'block';
                        if (dragNote) {
                            dragNote.textContent = 'Exercice terminé.';
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

