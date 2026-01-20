<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $R * $c;
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$viewerId = isset($_GET['viewer_id']) ? (int)$_GET['viewer_id'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
if ($limit !== null && $limit <= 0) {
    $limit = null;
}
if ($userId <= 0 && $limit === null) {
    http_response_code(422);
    echo json_encode(['error' => 'Некорректный пользователь']);
    exit;
}

// Параметры сортировки
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'datetime';
$sortOrder = isset($_GET['sort_order']) ? strtoupper($_GET['sort_order']) : 'DESC';

// Валидация полей сортировки
$allowedSortFields = ['datetime', 'go_date'];
if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'datetime';
}
if (!in_array($sortOrder, ['ASC', 'DESC'])) {
    $sortOrder = 'DESC';
}

$typeFilter = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
$distanceKm = isset($_GET['distance_km']) ? (float)$_GET['distance_km'] : null;
if ($distanceKm !== null && $distanceKm <= 0) {
    $distanceKm = null;
}

try {
$sql = "
        SELECT  a.id, a.datetime, a.type, a.comment,
            a.status, EXISTS(SELECT 1 FROM application_volunteer av WHERE av.application_id = a.id) AS has_responses,
            a.start_address, a.end_id, a.end_type, a.go_date, u.lat AS client_lat, u.lon AS client_lon,
            CASE  WHEN a.end_type = 'mfc' THEN (
                    SELECT COALESCE(short_name, common_name, full_name) FROM mfc_centers m WHERE m.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'polyclinic' THEN (
                    SELECT COALESCE(short_name, full_name, org_full_name) FROM polyclinics p WHERE p.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'uprava' THEN (
                    SELECT name FROM upravas u WHERE u.global_id = a.end_id LIMIT 1
                ) ELSE NULL
            END AS end_name,
            CASE  WHEN a.end_type = 'mfc' THEN (
                    SELECT lat FROM mfc_centers m WHERE m.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'polyclinic' THEN (
                    SELECT lat FROM polyclinics p WHERE p.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'uprava' THEN (
                    SELECT lat FROM upravas u WHERE u.global_id = a.end_id LIMIT 1
                ) ELSE NULL
            END AS end_lat,
            CASE  WHEN a.end_type = 'mfc' THEN (
                    SELECT lon FROM mfc_centers m WHERE m.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'polyclinic' THEN (
                    SELECT lon FROM polyclinics p WHERE p.global_id = a.end_id LIMIT 1
                ) WHEN a.end_type = 'uprava' THEN (
                    SELECT lon FROM upravas u WHERE u.global_id = a.end_id LIMIT 1
                ) ELSE NULL
            END AS end_lon
        FROM application a
        LEFT JOIN users u ON u.id = a.user_id
        /**where_clause**/
        ORDER BY /**order_clause**/
        /**limit_clause**/
    ";
    $params = [];
    $whereParts = [];
    if ($userId > 0) {
        $whereParts[] = "a.user_id = :user_id";
        $params[':user_id'] = $userId;
    } else {
        $whereParts[] = "a.status <> 'closed'";
    }
    if ($typeFilter !== '') {
        $whereParts[] = "a.type LIKE :type";
        $params[':type'] = '%' . $typeFilter . '%';
    }
    $where = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";
    
    $orderBy = "{$sortBy} {$sortOrder}";
    $limitClause = $limit ? "LIMIT {$limit}" : "";
    
    $sql = str_replace(['/**where_clause**/', '/**order_clause**/', '/**limit_clause**/'], [$where, $orderBy, $limitClause], $sql);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    if ($distanceKm !== null) {
        $originLat = null;
        $originLon = null;
        $distanceMode = null;
        $originId = $userId > 0 ? $userId : ($viewerId > 0 ? $viewerId : 0);
        if ($originId > 0) {
            $stmtOrigin = $pdo->prepare('SELECT lat, lon FROM users WHERE id = :id LIMIT 1');
            $stmtOrigin->execute([':id' => $originId]);
            $origin = $stmtOrigin->fetch();
            if ($origin && $origin['lat'] !== null && $origin['lon'] !== null) {
                $originLat = (float)$origin['lat'];
                $originLon = (float)$origin['lon'];
                $distanceMode = $userId > 0 ? 'client_to_end' : 'viewer_to_client';
            }
        }

        if ($originLat === null || $originLon === null) {
            $rows = [];
        } else {
            foreach ($rows as &$row) {
                if ($distanceMode === 'client_to_end') {
                    $destLat = $row['end_lat'];
                    $destLon = $row['end_lon'];
                } else {
                    $destLat = $row['client_lat'];
                    $destLon = $row['client_lon'];
                }
                if ($destLat === null || $destLon === null) {
                    $row['distance_km'] = null;
                    continue;
                }
                $row['distance_km'] = round(haversine($originLat, $originLon, (float)$destLat, (float)$destLon), 2);
            }
            unset($row);
            $rows = array_values(array_filter($rows, function ($row) use ($distanceKm) {
                return $row['distance_km'] !== null && $row['distance_km'] <= $distanceKm;
            }));
        }
    }
    echo json_encode($rows);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
}