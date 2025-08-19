<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

$sql = "SELECT DISTINCT place FROM (
    SELECT TRIM(location) AS place FROM jobs WHERE location IS NOT NULL AND location <> ''
    UNION
    SELECT TRIM(city)     AS place FROM jobs WHERE city     IS NOT NULL AND city     <> ''
) t";
$params = [];

if ($q !== '') {
    $sql .= " WHERE place LIKE :q";
    $params[':q'] = '%'.$q.'%';
}

$sql .= " ORDER BY place ASC LIMIT 200";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
