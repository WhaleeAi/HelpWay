<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    parse_str($raw, $data);
}

$applicationId = isset($data['application_id']) ? (int)$data['application_id'] : 0;
$volunteerId = isset($data['volunteer_id']) ? (int)$data['volunteer_id'] : 0;

if ($applicationId <= 0 || $volunteerId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Не хватает данных (application_id, volunteer_id)']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('UPDATE application SET accepted_volunteer_id = :volunteer_id, status = :status WHERE id = :id');
    $stmt->execute([
        ':volunteer_id' => $volunteerId,
        ':status' => 'confirmed',
        ':id' => $applicationId
    ]);

    $stmt2 = $pdo->prepare('UPDATE application_volunteer SET status = :status WHERE application_id = :app_id AND volunteer_id = :vol_id');
    $stmt2->execute([
        ':status' => 'accepted',
        ':app_id' => $applicationId,
        ':vol_id' => $volunteerId
    ]);

    $pdo->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Не удалось обновить заявку', 'detail' => $e->getMessage()]);
}
