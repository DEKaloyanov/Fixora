<?php
header('Content-Type: application/json; charset=utf-8');

$professions = include __DIR__ . '/professions.php';     // flat: ключ => етикет
require_once __DIR__ . '/professions_tree.php';          // $PROFESSIONS_TREE + helper

// Ако е подадено children_of=<mainKey> -> върни листовете
if (isset($_GET['children_of'])) {
    $main = $_GET['children_of'];
    $children = get_profession_children($main);
    $out = [];
    foreach ($children as $k) {
        $out[] = [
            'key'   => $k,
            'label' => $professions[$k] ?? ucfirst((string)$k),
        ];
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

// Иначе: върни само топ-ниво категориите
$out = [];
foreach ($PROFESSIONS_TREE as $key => $node) {
    $out[] = [
        'key'   => $key,
        'label' => $node['label'] ?? $key,
        'count' => isset($node['children']) && is_array($node['children']) ? count($node['children']) : 0
    ];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
