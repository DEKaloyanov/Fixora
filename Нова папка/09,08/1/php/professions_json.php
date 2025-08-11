<?php
header('Content-Type: application/json; charset=utf-8');
$prof = include __DIR__ . 'professions.php';
$out = [];
foreach ($prof as $key => $label) {
    $out[] = ['key' => $key, 'label' => $label];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
