<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/professions.php';      // $professions
require_once __DIR__ . '/professions_tree.php'; // $PROFESSIONS_TREE

$out = $PROFESSIONS_TREE;
$out['__labels'] = $professions; // етикети за листата
echo json_encode($out, JSON_UNESCAPED_UNICODE);
