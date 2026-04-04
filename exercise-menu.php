<?php
declare(strict_types=1);

/**
 * Configuration centralisée du menu des exercices.
 * Chaque entrée représente un type d'exercice, avec ses étapes internes.
 *
 * @return array<int, array{label: string, file: string, action: string, size: int, stages: int[]}>
 */
function getExerciseMenuItems(): array
{
    return [
        ['label' => 'Clic gauche', 'file' => 'click-left.php', 'action' => 'click-left', 'size' => 100, 'stages' => [4, 12]],
        ['label' => 'Double clic', 'file' => 'double-click.php', 'action' => 'double-click', 'size' => 100, 'stages' => [4, 12]],
        ['label' => 'Clic droit', 'file' => 'right-click.php', 'action' => 'right-click', 'size' => 100, 'stages' => [4, 12]],
        ['label' => 'Glisser déposer', 'file' => 'drag-drop.php', 'action' => 'drag-drop', 'size' => 100, 'stages' => [2, 8]],
        ['label' => 'Copier coller', 'file' => 'copy-paste.php', 'action' => 'copy-paste', 'size' => 100, 'stages' => [2, 8]],
        ['label' => 'Couper coller', 'file' => 'cut-paste.php', 'action' => 'cut-paste', 'size' => 100, 'stages' => [2, 8]],
    ];
}

/**
 * Déplie le menu en séquence d'étapes pour la navigation précédente/suivante.
 *
 * @return array<int, array{label: string, file: string, action: string, items: int, size: int}>
 */
function getExerciseStepSequence(): array
{
    $steps = [];

    foreach (getExerciseMenuItems() as $item) {
        foreach ($item['stages'] as $stageItems) {
            $steps[] = [
                'label' => $item['label'],
                'file' => $item['file'],
                'action' => $item['action'],
                'items' => $stageItems,
                'size' => $item['size'],
            ];
        }
    }

    return $steps;
}

/**
 * Génère le menu HTML des exercices.
 */
function renderExerciseMenu(
    ?string $currentScript = null,
    ?string $currentAction = null,
    ?int $currentItems = null
): string {
    $currentScript = $currentScript ?? basename((string) ($_SERVER['PHP_SELF'] ?? ''));
    $normalizedCurrentAction = is_string($currentAction) ? strtolower(trim($currentAction)) : null;

    $html = '<nav class="exercise-menu" aria-label="Choisir un exercice">';
    $html .= '<ul class="exercise-menu-list">';

    foreach (getExerciseMenuItems() as $item) {
        $firstStageItems = (int) ($item['stages'][0] ?? 1);
        $query = http_build_query([
            'action' => $item['action'],
            'items' => $firstStageItems,
            'size' => $item['size'],
        ]);

        $href = $item['file'] . '?' . $query;
        $isActive = $currentScript === $item['file']
            && $normalizedCurrentAction === strtolower($item['action']);

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
 * Retourne l'index de l'étape courante dans la séquence pédagogique.
 */
function getCurrentExerciseMenuIndex(string $currentScript, string $currentAction, int $currentItems): ?int
{
    $menuItems = getExerciseStepSequence();
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
 * Retourne l'exercice précédent défini dans la séquence.
 *
 * @return array{label: string, file: string, action: string, items: int, size: int}|null
 */
function getPreviousExerciseMenuItem(string $currentScript, string $currentAction, int $currentItems): ?array
{
    $menuItems = getExerciseStepSequence();
    $currentIndex = getCurrentExerciseMenuIndex($currentScript, $currentAction, $currentItems);

    if ($currentIndex === null || $currentIndex <= 0) {
        return null;
    }

    return $menuItems[$currentIndex - 1];
}

/**
 * Retourne le prochain exercice défini dans la séquence.
 *
 * @return array{label: string, file: string, action: string, items: int, size: int}|null
 */
function getNextExerciseMenuItem(string $currentScript, string $currentAction, int $currentItems): ?array
{
    $menuItems = getExerciseStepSequence();
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
