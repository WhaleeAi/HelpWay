<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($userId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Некорректный user_id']);
    exit;
}

try {
    $where = "a.status = 'closed' AND a.accepted_volunteer_id = :uid";
    $params = [':uid' => $userId];

    $sql = "
        SELECT a.id, a.datetime, a.type, a.comment,
            a.status, a.start_address, a.end_id, 
            a.end_type, a.go_date,
            CASE WHEN a.end_type = 'mfc' THEN (
                    SELECT COALESCE(short_name, common_name, full_name) FROM mfc_centers m WHERE m.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'polyclinic' THEN (
                    SELECT COALESCE(short_name, full_name, org_full_name) FROM polyclinics p WHERE p.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'uprava' THEN (
                    SELECT name FROM upravas u WHERE u.global_id = a.end_id LIMIT 1
                ) ELSE NULL
            END AS end_name
        FROM application a
        WHERE {$where}
        ORDER BY a.datetime DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Не удалось загрузить архив', 'detail' => $e->getMessage()]);
}
