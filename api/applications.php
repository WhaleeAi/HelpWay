<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
if ($limit !== null && $limit <= 0) {
    $limit = null;
}
if ($userId <= 0 && $limit === null) {
    http_response_code(422);
    echo json_encode(['error' => 'Некорректный пользователь']);
    exit;
}

try {
$sql = "
        SELECT 
            a.id,
            a.datetime,
            a.type,
            a.comment,
            a.status,
            a.start_address,
            a.end_id,
            a.end_type,
            a.go_date,
            CASE 
                WHEN a.end_type = 'mfc' THEN (
                    SELECT COALESCE(short_name, common_name, full_name) FROM mfc_centers m WHERE m.global_id = a.end_id LIMIT 1
                )
                WHEN a.end_type = 'polyclinic' THEN (
                    SELECT COALESCE(short_name, full_name, org_full_name) FROM polyclinics p WHERE p.global_id = a.end_id LIMIT 1
                )
                WHEN a.end_type = 'uprava' THEN (
                    SELECT name FROM upravas u WHERE u.global_id = a.end_id LIMIT 1
                )
                ELSE NULL
            END AS end_name
        FROM application a
        /**where_clause**/
        ORDER BY a.datetime DESC
        /**limit_clause**/
    ";
    $where = "WHERE a.user_id = :user_id";
    $params = [];
    if ($userId > 0) {
        $params[':user_id'] = $userId;
    } else {
        $where = "WHERE a.status <> 'closed'";
    }
    $limitClause = $limit ? "LIMIT {$limit}" : "";
    $sql = str_replace(['/**where_clause**/', '/**limit_clause**/'], [$where, $limitClause], $sql);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
}
