<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

try {
    $mfc = $pdo->query("SELECT global_id AS id, COALESCE(short_name, common_name, full_name) AS name, address, lat, lon FROM mfc_centers ORDER BY name")
        ->fetchAll();
    $polyclinics = $pdo->query("SELECT global_id AS id, COALESCE(short_name, full_name, org_full_name) AS name, legal_address AS address, lat, lon FROM polyclinics ORDER BY name")
        ->fetchAll();
    $upravas = $pdo->query("SELECT global_id AS id, name, address, lat, lon FROM upravas ORDER BY name")
        ->fetchAll();

    echo json_encode([
        'mfc' => $mfc,
        'polyclinics' => $polyclinics,
        'upravas' => $upravas,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
}
