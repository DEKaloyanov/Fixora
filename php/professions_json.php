<?php
header('Content-Type: application/json; charset=utf-8');

// Взимаме и плоската карта, и групите.
// professions.php връща $professions (плосък); ще достъпим и $profession_groups, ако е дефиниран.
$professions = include __DIR__ . '/professions.php';

// За да имаме $profession_groups, правим второ include без overwrite, ако променливата още не е в скопа.
if (!isset($profession_groups)) {
    // Втори include ще върне пак $professions, но най-важното — ще дефинира $profession_groups
    include __DIR__ . '/professions.php';
}

$grouped = isset($_GET['grouped']) && (int)$_GET['grouped'] === 1;

if ($grouped && isset($profession_groups) && is_array($profession_groups)) {
    $out = [];
    foreach ($profession_groups as $groupName => $items) {
        $bucket = ['group' => $groupName, 'items' => []];
        foreach ($items as $key => $label) {
            $bucket['items'][] = ['key' => $key, 'label' => $label];
        }
        $out[] = $bucket;
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

// Плосък
$out = [];
foreach ($professions as $key => $label) {
    $out[] = ['key' => $key, 'label' => $label];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
