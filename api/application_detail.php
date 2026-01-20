<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Не передан id']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT a.id, a.user_id, a.datetime, a.type,
            a.comment, a.start_address, a.end_id,
            a.end_type, a.go_date, a.status,
            a.accepted_volunteer_id,
            CASE WHEN a.end_type = 'mfc' THEN (
                    SELECT COALESCE(short_name, common_name, full_name) FROM mfc_centers m WHERE m.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'polyclinic' THEN (
                    SELECT COALESCE(short_name, full_name, org_full_name) FROM polyclinics p WHERE p.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'uprava' THEN (
                    SELECT name FROM upravas u WHERE u.global_id = a.end_id LIMIT 1
                ) ELSE NULL
            END AS end_name
        FROM application a
        WHERE a.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $app = $stmt->fetch();
    if (!$app) {
        http_response_code(404);
        echo json_encode(['error' => 'Заявка не найдена']);
        exit;
    }

    $stmtVols = $pdo->prepare("
        SELECT av.application_id, av.volunteer_id, av.answer, av.status, av.created_at,
               u.f_name, u.l_name, u.email
        FROM application_volunteer av
        LEFT JOIN users u ON u.id = av.volunteer_id
        WHERE av.application_id = :id
        ORDER BY av.created_at DESC
    ");
    $stmtVols->execute([':id' => $id]);
    $volunteers = $stmtVols->fetchAll();

    $volunteerIds = array_values(array_unique(array_filter(array_column($volunteers, 'volunteer_id'))));
    $closedCounts = [];
    if ($volunteerIds) {
        $placeholders = [];
        $params = [];
        foreach ($volunteerIds as $index => $volunteerId) {
            $key = ':v' . $index;
            $placeholders[] = $key;
            $params[$key] = $volunteerId;
        }
        $sql = "SELECT accepted_volunteer_id AS volunteer_id, COUNT(*) AS closed_count
                FROM application
                WHERE status = 'closed' AND accepted_volunteer_id IN (" . implode(',', $placeholders) . ")
                GROUP BY accepted_volunteer_id";
        $stmtCounts = $pdo->prepare($sql);
        $stmtCounts->execute($params);
        foreach ($stmtCounts->fetchAll() as $row) {
            $closedCounts[(int)$row['volunteer_id']] = (int)$row['closed_count'];
        }
    }
    foreach ($volunteers as &$volunteer) {
        $volunteerId = (int)($volunteer['volunteer_id'] ?? 0);
        $volunteer['closed_count'] = $volunteerId ? ($closedCounts[$volunteerId] ?? 0) : 0;
    }
    unset($volunteer);

    echo json_encode(['application' => $app, 'volunteers' => $volunteers]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Не удалось загрузить данные', 'detail' => $e->getMessage()]);
}
