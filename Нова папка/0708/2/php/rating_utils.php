<?php
require_once 'db.php';

// Връща средна оценка за потребител
function getUserAverageRating($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT AVG(rating) FROM ratings WHERE to_user_id = ?");
    $stmt->execute([$user_id]);
    $avg = $stmt->fetchColumn();
    if ($avg === false || $avg == 0)
        return 'Няма оценки';
    return number_format($avg, 2) . ' / 5';
}

function getJobAverageRating($job_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT AVG(rating) FROM ratings WHERE job_id = ?");
    $stmt->execute([$job_id]);
    $avg = $stmt->fetchColumn();
    if ($avg === false || $avg == 0)
        return 'Няма оценки';
    return number_format($avg, 2) . ' / 5';
}

// Връща списък с оценки за обява (с коментари)
function getRatingsForJob($job_id)
{
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.*, u.username, u.ime, u.familiq 
        FROM ratings r 
        JOIN users u ON u.id = r.from_user_id 
        WHERE r.job_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$job_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Връща списък с оценки получени от потребителя
function getReceivedRatings($user_id)
{
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.*, u.ime, u.familiq 
        FROM ratings r 
        JOIN users u ON u.id = r.from_user_id 
        WHERE r.to_user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Връща списък с оценки, които потребителят е дал
function getGivenRatings($user_id)
{
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.*, u.ime, u.familiq 
        FROM ratings r 
        JOIN users u ON u.id = r.to_user_id 
        WHERE r.from_user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Връща HTML за звездички



function displayUserAverageRating(PDO $conn, int $user_id): string
{
    $stmt = $conn->prepare("SELECT AVG(rating) FROM ratings WHERE to_user_id = ?");
    $stmt->execute([$user_id]);
    $avg = $stmt->fetchColumn();

    if ($avg === false || $avg == 0) {
        return '<div class="user-rating"><h3>Средна оценка:</h3><span>Няма оценки</span></div>';
    }

    return '<div class="user-rating">
                <h3>Средна оценка:</h3>
                <span>' . number_format($avg, 2) . ' / 5</span>
            </div>';
}



?>