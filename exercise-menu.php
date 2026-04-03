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
function renderExerciseMenu(?string $currentScript = null): string
{
    $currentScript = $currentScript ?? basename((string) ($_SERVER['PHP_SELF'] ?? ''));

    $html = '<nav class="exercise-menu" aria-label="Choisir un exercice">';
    $html .= '<ul class="exercise-menu-list">';

    foreach (getExerciseMenuItems() as $item) {
        $query = http_build_query([
            'action' => $item['action'],
            'items' => $item['items'],
            'size' => $item['size'],
        ]);

        $href = $item['file'] . '?' . $query;
        $isActive = $currentScript === $item['file'];

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
