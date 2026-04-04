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
 * Configuration du mode chrono.
 *
 * @return array<int, array{label: string, file: string, action: string, size: int, stages: int[], delay: array<int, int|float>}>
 */
function getExerciseMenuChronoItems(): array
{
    return [
        ['label' => 'Clic gauche', 'file' => 'click-left.php', 'action' => 'click-left', 'size' => 100, 'stages' => [4], 'delay' => [1, 1.4, 1.8]],
        ['label' => 'Double clic', 'file' => 'double-click.php', 'action' => 'double-click', 'size' => 100, 'stages' => [12], 'delay' => [2, 3, 4]],
        ['label' => 'Clic droit', 'file' => 'right-click.php', 'action' => 'right-click', 'size' => 100, 'stages' => [12], 'delay' => [2, 3, 4]],
        ['label' => 'Glisser déposer', 'file' => 'drag-drop.php', 'action' => 'drag-drop', 'size' => 100, 'stages' => [4], 'delay' => [2, 3, 4]],
        ['label' => 'Copier coller', 'file' => 'copy-paste.php', 'action' => 'copy-paste', 'size' => 100, 'stages' => [4], 'delay' => [2, 3, 4]],
        ['label' => 'Couper coller', 'file' => 'cut-paste.php', 'action' => 'cut-paste', 'size' => 100, 'stages' => [4], 'delay' => [2, 3, 4]],
    ];
}

/**
 * Retourne les identifiants de mode supportés.
 *
 * @return array<int, string>
 */
function getSupportedExerciseModes(): array
{
    return ['classic', 'chrono-expert', 'chrono-normal', 'chrono-beginner'];
}

/**
 * Retourne un mode valide.
 */
function normalizeExerciseMode(?string $mode): string
{
    $mode = is_string($mode) ? strtolower(trim($mode)) : '';

    if (in_array($mode, getSupportedExerciseModes(), true)) {
        return $mode;
    }

    return 'classic';
}

/**
 * Retourne le délai par action (en secondes) pour un mode chrono.
 */
function getChronoDelayForAction(string $action, string $mode): ?float
{
    $mode = normalizeExerciseMode($mode);
    if ($mode === 'classic') {
        return null;
    }

    $delayIndexByMode = [
        'chrono-expert' => 0,
        'chrono-normal' => 1,
        'chrono-beginner' => 2,
    ];

    $delayIndex = $delayIndexByMode[$mode] ?? null;
    if (!is_int($delayIndex)) {
        return null;
    }

    foreach (getExerciseMenuChronoItems() as $item) {
        if (strtolower($item['action']) !== strtolower(trim($action))) {
            continue;
        }

        $value = $item['delay'][$delayIndex] ?? null;
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        return null;
    }

    return null;
}

/**
 * Libellé lisible du mode.
 */
function getExerciseModeLabel(string $mode): string
{
    $mode = normalizeExerciseMode($mode);

    return match ($mode) {
        'chrono-expert' => 'Chrono Expert',
        'chrono-normal' => 'Chrono Normal',
        'chrono-beginner' => 'Chrono Débutant',
        default => 'Classique',
    };
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
    ?int $currentItems = null,
    ?string $currentMode = null
): string {
    $currentScript = $currentScript ?? basename((string) ($_SERVER['PHP_SELF'] ?? ''));
    $normalizedCurrentAction = is_string($currentAction) ? strtolower(trim($currentAction)) : null;
    $currentMode = normalizeExerciseMode($currentMode);

    $sourceItems = $currentMode === 'classic'
        ? getExerciseMenuItems()
        : getExerciseMenuChronoItems();

    $html = '<nav class="exercise-menu" aria-label="Choisir un exercice">';
    $html .= '<ul class="exercise-menu-list">';

    foreach ($sourceItems as $item) {
        $firstStageItems = (int) ($item['stages'][0] ?? 1);
        $query = http_build_query([
            'action' => $item['action'],
            'items' => $firstStageItems,
            'size' => $item['size'],
            'mode' => $currentMode,
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
    $html .= '<div class="exercise-mode-list" aria-label="Choisir un mode">';

    $modeLabels = [
        'classic' => 'Classique',
        'chrono-beginner' => 'Chrono Débutant',
        'chrono-normal' => 'Chrono Normal',
        'chrono-expert' => 'Chrono Expert',
    ];

    foreach ($modeLabels as $modeKey => $modeLabel) {
        $modeItems = $modeKey === 'classic'
            ? getExerciseMenuItems()
            : getExerciseMenuChronoItems();

        $targetItem = null;
        foreach ($modeItems as $modeItem) {
            $matchesCurrentScript = strtolower($modeItem['file']) === strtolower($currentScript);
            $matchesCurrentAction = $normalizedCurrentAction !== null
                && strtolower($modeItem['action']) === $normalizedCurrentAction;

            if ($matchesCurrentScript || $matchesCurrentAction) {
                $targetItem = $modeItem;
                break;
            }
        }

        if ($targetItem === null) {
            $targetItem = $modeItems[0] ?? null;
        }

        if ($targetItem !== null) {
            $modeHref = $targetItem['file'] . '?' . http_build_query([
                'action' => $targetItem['action'],
                'items' => (int) ($targetItem['stages'][0] ?? 1),
                'size' => $targetItem['size'],
                'mode' => $modeKey,
            ]);
        } else {
            $modeHref = $currentScript . '?' . http_build_query([
                'mode' => $modeKey,
            ]);
        }

        $isModeActive = $currentMode === $modeKey;
        $html .= '<a class="exercise-mode-link' . ($isModeActive ? ' is-active' : '') . '" href="' . htmlspecialchars($modeHref, ENT_QUOTES, 'UTF-8') . '">';
        $html .= htmlspecialchars($modeLabel, ENT_QUOTES, 'UTF-8');
        $html .= '</a>';
    }

    $html .= '</div>';
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
