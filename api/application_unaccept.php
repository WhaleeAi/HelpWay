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
    echo json_encode(['error' => 'Некорректные параметры (application_id, volunteer_id)']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('UPDATE application SET accepted_volunteer_id = NULL, status = :status WHERE id = :id AND accepted_volunteer_id = :volunteer_id');
    $stmt->execute([
        ':status' => 'open',
        ':id' => $applicationId,
        ':volunteer_id' => $volunteerId
    ]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'Заявка не привязана к этому волонтёру']);
        exit;
    }

    $stmt2 = $pdo->prepare('UPDATE application_volunteer SET status = :status WHERE application_id = :app_id AND volunteer_id = :vol_id');
    $stmt2->execute([
        ':status' => 'pending',
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
    echo json_encode(['error' => 'Не удалось отказаться от помощи', 'detail' => $e->getMessage()]);
}
