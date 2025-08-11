<?php
header('Content-Type: application/json; charset=utf-8');
$prof = include __DIR__ . '/professions.php';
if (!is_array($prof)) { $prof = []; }
$out = [];
foreach ($prof as $key => $label) {
    $out[] = ['key' => (string)$key, 'label' => (string)$label];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
