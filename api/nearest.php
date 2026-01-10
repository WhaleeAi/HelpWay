<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Некорректный пользователь']);
    exit;
}

function haversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c;
}

try {
$stmt = $pdo->prepare("SELECT lat, lon FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$userRow = $stmt->fetch();
if (!$userRow || $userRow['lat'] === null || $userRow['lon'] === null) {
    http_response_code(422);
    echo json_encode(['error' => 'Нет координат пользователя (lat/lon)']);
    exit;
}
$userLat = (float)$userRow['lat'];
$userLon = (float)$userRow['lon'];

    $points = [];

    $mfc = $pdo->query("SELECT global_id AS id, COALESCE(short_name, common_name, full_name) AS name, address, lat, lon, near_stations FROM mfc_centers WHERE lat IS NOT NULL AND lon IS NOT NULL")->fetchAll();
    foreach ($mfc as $row) {
        $points[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'address' => $row['address'],
            'lat' => (float)$row['lat'],
            'lon' => (float)$row['lon'],
            'type' => 'mfc',
            'metro' => $row['near_stations'] ?? null,
        ];
    }

    $poly = $pdo->query("SELECT global_id AS id, COALESCE(short_name, full_name, org_full_name) AS name, legal_address AS address, lat, lon FROM polyclinics WHERE lat IS NOT NULL AND lon IS NOT NULL")->fetchAll();
    foreach ($poly as $row) {
        $points[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'address' => $row['address'],
            'lat' => (float)$row['lat'],
            'lon' => (float)$row['lon'],
            'type' => 'polyclinic',
            'metro' => null,
        ];
    }

    $uprava = $pdo->query("SELECT global_id AS id, name, address, lat, lon FROM upravas WHERE lat IS NOT NULL AND lon IS NOT NULL")->fetchAll();
    foreach ($uprava as $row) {
        $points[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'address' => $row['address'],
            'lat' => (float)$row['lat'],
            'lon' => (float)$row['lon'],
            'type' => 'uprava',
            'metro' => null,
        ];
    }

    foreach ($points as &$p) {
        $p['distance_km'] = haversine($userLat, $userLon, $p['lat'], $p['lon']);
    }
    unset($p);

    usort($points, function($a, $b){
        return $a['distance_km'] <=> $b['distance_km'];
    });

    $nearest = array_slice($points, 0, 10);

    echo json_encode([
        'user' => ['lat' => $userLat, 'lon' => $userLon],
        'points' => $nearest
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
}
