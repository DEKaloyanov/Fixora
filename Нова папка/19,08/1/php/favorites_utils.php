<?php
require_once 'db.php';

function isFavorite($user_id, $job_id, $conn) {
    $stmt = $conn->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$user_id, $job_id]);
    return $stmt->fetchColumn() !== false;
}

function renderFavoriteButton($user_id, $job_id, $conn) {
    $isFav = isFavorite($user_id, $job_id, $conn);
    $icon = $isFav ? 'img/heart-filled.png' : 'img/heart-outline.png';
    $alt = $isFav ? 'Премахни от любими' : 'Добави в любими';
    
    return '<button class="favorite-btn" data-job-id="' . $job_id . '" onclick="toggleFavorite(event, this)">
                <img src="/Fixora/' . $icon . '" alt="' . $alt . '">
            </button>';
}


function isJobFavorite($conn, $user_id, $job_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$user_id, $job_id]);
    return $stmt->fetchColumn() > 0;
}


function removeFromFavorites($conn, $user_id, $job_id) {
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$user_id, $job_id]);
}

?>