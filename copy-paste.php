<?php
declare(strict_types=1);

/**
 * Exemple d'URL :
 * copy-paste.php?action=copy-paste&items=3&size=100
 * copy-paste.php?action=copier-coller&items=5&size=120
 */

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'copy-paste';
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
$isCopyPasteExercise = in_array($normalizedAction, ['copy-paste', 'copier-coller', 'copy-paste-right-click'], true);
require_once __DIR__ . '/exercise-menu.php';
$previousExercise = getPreviousExerciseMenuItem(basename(__FILE__), 'copy-paste', $items);
$previousHref = $previousExercise
    ? $previousExercise['file'] . '?' . http_build_query([
        'action' => $previousExercise['action'],
        'items' => $previousExercise['items'],
        'size' => $previousExercise['size'],
    ])
    : null;
$nextExercise = getNextExerciseMenuItem(basename(__FILE__), 'copy-paste', $items);
$nextHref = $nextExercise
    ? $nextExercise['file'] . '?' . http_build_query([
        'action' => $nextExercise['action'],
        'items' => $nextExercise['items'],
        'size' => $nextExercise['size'],
    ])
    : null;

$pageTitle = 'Exercices souris';
$exerciseTitle = 'Copier / coller (x ' . $items . ')';

if ($isCopyPasteExercise) {
    $exerciseInstruction = 'Majeur sur le bouton droit de la souris, allez sur la cible, clic droit et descendez sur “Copier”. Puis cliquez sur “Collez-moi !”, faites un clic droit et choisissez “Coller”.';
} else {
    $exerciseInstruction = 'L’action demandée n’est pas reconnue.';
}

ob_start();

if ($isCopyPasteExercise): ?>
    <div class="copy-paste-wrapper" id="copy-paste-exercise">
        <?php for ($i = 1; $i <= $items; $i++): ?>
            <div class="copy-paste-row" data-row data-id="item-<?= $i ?>">
                <div class="copy-paste-side copy-origin" data-copy-origin>
                    <div class="copy-paste-label">Copiez-moi !</div>

                    <div class="copy-paste-target">
                        <button
                            type="button"
                            class="smiley-btn"
                            data-copy-source="item-<?= $i ?>"
                            aria-label="Smiley à copier <?= $i ?>"
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
                            <button type="button" class="copy-menu-item" disabled>Couper</button>
                            <button type="button" class="copy-menu-item" data-copy-command="copy">Copier</button>
                            <button type="button" class="copy-menu-item" disabled>Envoyer pâte</button>
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
                            <button type="button" class="copy-menu-item" disabled>Envoyer pâte</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>

        <div class="status-box">
            Restants : <span id="remaining-count"><?= (int) $items ?></span> / <?= (int) $items ?>
        </div>

        <div class="menu-note" id="copy-note">
            Astuce : clic droit sur le smiley, choisissez “Copier”, puis clic droit sur “Collez-moi !” et choisissez “Coller”.
        </div>

        <div class="success-message" id="success-message">
            Bravo ! Tous les smileys ont été copiés puis collés.
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

            const exercise = document.getElementById('copy-paste-exercise');
            const remainingCount = document.getElementById('remaining-count');
            const successMessage = document.getElementById('success-message');
            const copyNote = document.getElementById('copy-note');

            if (!exercise) {
                return;
            }

            let copiedId = null;
            let remaining = exercise.querySelectorAll('[data-row]').length;

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

            function updatePasteMenus() {
                exercise.querySelectorAll('[data-paste-command="paste"]').forEach(function (button) {
                    if (copiedId) {
                        button.classList.remove('is-disabled');
                        button.disabled = false;
                    } else {
                        button.classList.add('is-disabled');
                        button.disabled = true;
                    }
                });
            }

            exercise.addEventListener('contextmenu', function (event) {
                const sourceButton = event.target.closest('[data-copy-source]');
                const pasteArea = event.target.closest('[data-copy-destination] .copy-paste-target, [data-paste-slot]');

                event.preventDefault();

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
                        copyNote.textContent = 'Cliquez maintenant sur “Copier”.';
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
                    copyNote.textContent = copiedId
                        ? 'Cliquez maintenant sur “Coller”.'
                        : 'Il faut d’abord copier un smiley.';
                }
            });

            exercise.addEventListener('click', function (event) {
                const copyButton = event.target.closest('[data-copy-command="copy"]');
                if (copyButton) {
                    const row = copyButton.closest('[data-row]');
                    if (!row) {
                        return;
                    }

                    const source = row.querySelector('[data-copy-source]');
                    const origin = row.querySelector('[data-copy-origin]');
                    const menu = row.querySelector('[data-copy-menu]');

                    if (!source || !origin || origin.classList.contains('is-done')) {
                        return;
                    }

                    copiedId = source.getAttribute('data-copy-source');
                    exercise.querySelectorAll('[data-copy-origin]').forEach(function (item) {
                        item.classList.remove('is-copied');
                    });
                    origin.classList.add('is-copied');

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
                    if (!copiedId) {
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
                    const source = row.querySelector('[data-copy-source]');

                    if (!expectedId || !slot || !origin || !source || origin.classList.contains('is-done')) {
                        return;
                    }

                    if (copiedId !== expectedId) {
                        if (pasteMenu) {
                            pasteMenu.hidden = true;
                        }
                        if (copyNote) {
                            copyNote.textContent = 'Ce n’est pas le bon smiley pour cette ligne. Recommencez.';
                        }
                        return;
                    }

                    slot.innerHTML = '';
                    const clone = source.cloneNode(true);
                    clone.removeAttribute('data-copy-source');
                    clone.disabled = true;
                    clone.setAttribute('aria-hidden', 'true');
                    slot.appendChild(clone);
                    slot.classList.add('is-filled');

                    origin.classList.remove('is-copied');
                    origin.classList.add('is-done');

                    if (pasteMenu) {
                        pasteMenu.hidden = true;
                    }

                    copiedId = null;
                    updatePasteMenus();
                    remaining--;

                    if (remainingCount) {
                        remainingCount.textContent = String(remaining);
                    }

                    if (copyNote && remaining > 0) {
                        copyNote.textContent = 'Très bien. Passez à la ligne suivante.';
                    }

                    if (remaining <= 0 && successMessage) {
                        successMessage.style.display = 'block';
                        if (copyNote) {
                            copyNote.textContent = 'Exercice terminé.';
                        }
                    }

                    return;
                }

                if (!event.target.closest('.copy-menu') && !event.target.closest('[data-copy-source]') && !event.target.closest('[data-copy-destination]')) {
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
