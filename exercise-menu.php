<?php
declare(strict_types=1);

/**
 * Configuration centralisée du menu des exercices.
 *
 * @return array<int, array{label: string, file: string, action: string, items: int, size: int}>
 */
function getExerciseMenuItems(): array
{
    return [
        ['label' => 'Clic gauche (x 04)', 'file' => 'click-left.php', 'action' => 'click-left', 'items' => 4, 'size' => 100],
        ['label' => 'Clic gauche (x 12)', 'file' => 'click-left.php', 'action' => 'click-left', 'items' => 12, 'size' => 100],
        ['label' => 'Double clic (x 04)', 'file' => 'double-click.php', 'action' => 'double-click', 'items' => 4, 'size' => 100],
        ['label' => 'Double clic (x 12)', 'file' => 'double-click.php', 'action' => 'double-click', 'items' => 12, 'size' => 100],
        ['label' => 'Clic droit (x 04)', 'file' => 'right-click.php', 'action' => 'right-click', 'items' => 4, 'size' => 100],
        ['label' => 'Clic droit (x 12)', 'file' => 'right-click.php', 'action' => 'right-click', 'items' => 12, 'size' => 100],
        ['label' => 'Glisser déposer (x 02)', 'file' => 'drag-drop.php', 'action' => 'drag-drop', 'items' => 2, 'size' => 100],
        ['label' => 'Glisser déposer (x 08)', 'file' => 'drag-drop.php', 'action' => 'drag-drop', 'items' => 8, 'size' => 100],
        ['label' => 'Copier coller (x 02)', 'file' => 'copy-paste.php', 'action' => 'copy-paste', 'items' => 2, 'size' => 100],
        ['label' => 'Copier coller (x 08)', 'file' => 'copy-paste.php', 'action' => 'copy-paste', 'items' => 8, 'size' => 100],
    ];
}

/**
 * Génère le menu HTML des exercices.
 */
function renderExerciseMenu(
    ?string $currentScript = null,
    ?string $currentAction = null,
    ?int $currentItems = null
): string
{
    $currentScript = $currentScript ?? basename((string) ($_SERVER['PHP_SELF'] ?? ''));
    $normalizedCurrentAction = is_string($currentAction) ? strtolower(trim($currentAction)) : null;

    $html = '<nav class="exercise-menu" aria-label="Choisir un exercice">';
    $html .= '<ul class="exercise-menu-list">';

    foreach (getExerciseMenuItems() as $item) {
        $query = http_build_query([
            'action' => $item['action'],
            'items' => $item['items'],
            'size' => $item['size'],
        ]);

        $href = $item['file'] . '?' . $query;
        $isActive = $currentScript === $item['file']
            && $normalizedCurrentAction === strtolower($item['action'])
            && $currentItems === $item['items'];

        $html .= '<li class="exercise-menu-item">';
        $html .= '<a class="exercise-menu-link' . ($isActive ? ' is-active' : '') . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
        $html .= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
        $html .= '</a>';
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</nav>';

    return $html;
}

/**
 * Retourne l'index de l'exercice courant dans le menu.
 */
function getCurrentExerciseMenuIndex(string $currentScript, string $currentAction, int $currentItems): ?int
{
    $menuItems = getExerciseMenuItems();
    $normalizedCurrentAction = strtolower(trim($currentAction));

    foreach ($menuItems as $index => $item) {
        if (
            $currentScript === $item['file']
            && $normalizedCurrentAction === strtolower($item['action'])
            && $currentItems === $item['items']
        ) {
            return $index;
        }
    }

    return null;
}

/**
 * Retourne l'exercice précédent défini dans le menu.
 *
 * @return array{label: string, file: string, action: string, items: int, size: int}|null
 */
function getPreviousExerciseMenuItem(string $currentScript, string $currentAction, int $currentItems): ?array
{
    $menuItems = getExerciseMenuItems();
    $currentIndex = getCurrentExerciseMenuIndex($currentScript, $currentAction, $currentItems);

    if ($currentIndex === null || $currentIndex <= 0) {
        return null;
    }

    return $menuItems[$currentIndex - 1];
}

/**
 * Retourne le prochain exercice défini dans le menu.
 *
 * @return array{label: string, file: string, action: string, items: int, size: int}|null
 */
function getNextExerciseMenuItem(string $currentScript, string $currentAction, int $currentItems): ?array
{
    $menuItems = getExerciseMenuItems();
    $currentIndex = getCurrentExerciseMenuIndex($currentScript, $currentAction, $currentItems);

    if ($currentIndex === null) {
        return null;
    }

    $nextIndex = $currentIndex + 1;
    if (!isset($menuItems[$nextIndex])) {
        return null;
    }

    return $menuItems[$nextIndex];
}
